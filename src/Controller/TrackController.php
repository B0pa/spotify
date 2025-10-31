<?php

namespace App\Controller;

use App\Entity\Track;
use App\Repository\ArtistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\TrackRepository;
use App\Service\AuthSpotifyService;
use App\Service\SpotifyRequestService;
use App\Factory\TrackFactory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/track')]
class TrackController extends AbstractController
{
    private string $token;

    public function __construct(
        private readonly AuthSpotifyService    $authSpotifyService,
        private readonly SpotifyRequestService $spotifyRequestService
    )
    {
        $this->token = $this->authSpotifyService->auth();
    }

    #[IsGranted("ROLE_USER")]
    #[Route('/add-to-fav/{id}', name: 'app_track_fav_add')]
    public function addToFav(string $id, EntityManagerInterface $em, TrackRepository $trackRepository, ArtistRepository $artistRepository, Request $request): Response
    {
        $user = $this->getUser();
        $track = $this->spotifyRequestService->getTrack($id, $this->token);
        $existingTrack = $trackRepository->findOneBy(['spotifyId' => $track->getSpotifyId()]);
        if (!$existingTrack) {
            $em->persist($track);
            $em->flush();
            $existingTrack = $track;
        }
        if (!$user->getFavTracks()->contains($existingTrack)) {
            $user->addFavTrack($existingTrack);
            $em->flush();
            $this->addFlash('success', 'Musique ajoutée aux favoris !');
        } else {
            $this->addFlash('info', 'Cette musique est déjà dans vos favoris.');
        }
        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_track_index'));
    }

    #[IsGranted("ROLE_USER")]
    #[Route('/delete-from-fav/{spotifyId}', name: 'app_track_fav_remove')]
    public function deleteFromFav(string $spotifyId, TrackRepository $trackRepository, EntityManagerInterface $em, Request $request): Response
    {
        $user = $this->getUser();
        $track = $trackRepository->findOneBy(['spotifyId' => $spotifyId]);
        if ($track && $user->getFavTracks()->contains($track)) {
            $user->removeFavTrack($track);
            $em->flush();
            $this->addFlash('success', 'Musique supprimée des favoris.');
        } else {
            $this->addFlash('error', 'Musique introuvable dans vos favoris.');
        }

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_track_fav'));
    }

    #[IsGranted("ROLE_USER")]
    #[Route('/fav', name: 'app_track_fav')]
    public function fav(): Response
    {
        $user = $this->getUser();
        return $this->render('track/fav.html.twig', [
            'fav' => $user->getFavTracks(),
        ]);
    }

    #[Route('/show/{id}', name: 'app_track_show')]
    public function show(string $id): Response
    {
        $user = $this->getUser();
        $tabFav = [];
        if ($user) {
            foreach ($user->getFavTracks() as $track) {
                $tabFav[] = $track->getSpotifyId();
            }
        }
        $track = $this->spotifyRequestService->getTrack($id, $this->token);
        $track = TrackFactory::enrichArtists($track, $this->spotifyRequestService, $this->token);
        return $this->render('track/show.html.twig', [
            'track' => $track,
            'fav' => $tabFav,
        ]);
    }

    #[Route('/{search?}', name: 'app_track_index')]
    public function index(?string $search, TrackRepository $trackRepository): Response
    {
        $user = $this->getUser();
        $tabFav = [];
        if ($user) {
            foreach ($user->getFavTracks() as $track) {
                $tabFav[] = $track->getSpotifyId();
            }
        }
        return $this->render('track/index.html.twig', [
            'tracks' => $this->spotifyRequestService->searchTracks($search ?: "iufghzifg", $this->token),
            'search' => $search,
            'fav' => $tabFav
        ]);
    }



}
