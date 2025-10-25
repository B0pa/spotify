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

    #[Route('/add-to-fav/{id}', name: 'app_track_fav_add')]
    public function addToFav(string $id, EntityManagerInterface $em, TrackRepository $trackRepository, ArtistRepository $artistRepository, Request $request): Response
    {
        $track = $this->spotifyRequestService->getTrack($id, $this->token);
        $existingTrack = $trackRepository->findOneBy(['spotifyId' => $track->getSpotifyId()]);
        if ($existingTrack) {
            $this->addFlash('info', 'Cette musique est déjà dans vos favoris.');
            return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_track_index'));
        }

        $artistsToPersist = new ArrayCollection();
        foreach ($track->getArtists() as $artist) {
            $existingArtist = $artistRepository->findOneBy(['idSpotify' => $artist->getIdSpotify()]);
            if ($existingArtist) {
                $artistsToPersist->add($existingArtist);
            }
        }
        $track->getArtists()->clear();
        foreach ($artistsToPersist as $artist) {
            $track->addArtist($artist);
        }

        try {
            $em->persist($track);
            $em->flush();
            $this->addFlash('success', 'Musique ajoutée aux favoris !');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_track_index'));
    }


    #[Route('/delete-from-fav/{spotifyId}', name: 'app_track_fav_remove')]
    public function delete(string $spotifyId, TrackRepository $trackRepository, EntityManagerInterface $em, Request $request): Response
    {
        $track = $trackRepository->findOneBy(['spotifyId' => $spotifyId]);

        if (!$track) {
            $this->addFlash('error', 'Musique introuvable dans vos favoris.');
            return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_track_fav'));
        }

        try {
            $em->remove($track);
            $em->flush();
            $this->addFlash('success', 'Musique supprimée des favoris.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_track_fav'));
    }


    #[Route('/fav', name: 'app_track_fav')]
    public function fav(TrackRepository $trackRepository): Response
    {
        $fav= $trackRepository->findAll();
        return $this->render('track/fav.html.twig', [
            'fav' => $fav,
        ]);
    }

    #[Route('/show/{id}', name: 'app_track_show')]
    public function show(string $id): Response
    {
        $track = $this->spotifyRequestService->getTrack($id, $this->token);
        $track = TrackFactory::enrichArtists($track, $this->spotifyRequestService, $this->token);
        return $this->render('track/show.html.twig', [
            'track' => $track,
        ]);
    }

    #[Route('/{search?}', name: 'app_track_index')]
    public function index(?string $search, trackRepository $trackRepository): Response
    {
        $fav= $trackRepository->findAll();
        $tabFav=[];
        foreach ($fav as $f) {
            $tabFav[]=$f->getSpotifyId();
        }
        return $this->render('track/index.html.twig', [
            'tracks' => $this->spotifyRequestService->searchTracks($search ?: "iufghzifg", $this->token),
            'search' => $search,
            'fav' => $tabFav
        ]);
    }


}
