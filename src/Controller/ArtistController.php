<?php

namespace App\Controller;

use App\Repository\ArtistRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\AuthSpotifyService;
use App\Service\SpotifyRequestService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/artist')]
final class ArtistController extends AbstractController
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
    #[Route('/add-to-fav/{id}', name: 'app_artist_fav_add')]
    public function addToFav(string $id, EntityManagerInterface $em, ArtistRepository $artistRepository, Request $request): Response
    {
        $user = $this->getUser();
        $artist = $this->spotifyRequestService->getArtist($id, $this->token);
        $existingArtist = $artistRepository->findOneBy(['idSpotify' => $artist->getIdSpotify()]);
        if (!$existingArtist) {
            $em->persist($artist);
            $em->flush();
            $existingArtist = $artist;
        }
        if (!$user->getFavArtists()->contains($existingArtist)) {
            $user->addFavArtist($existingArtist);
            $em->flush();
            $this->addFlash('success', 'Artiste ajouté aux favoris !');
        } else {
            $this->addFlash('info', 'Cette artiste est déjà dans vos favoris.');
        }

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_artist_index'));
    }

    #[IsGranted("ROLE_USER")]
    #[Route('/delete-from-fav/{idSpotify}', name: 'app_artist_fav_remove')]
    public function deleteFromFav(string $idSpotify, ArtistRepository $artistRepository, EntityManagerInterface $em, Request $request): Response
    {
        $user = $this->getUser();
        $artist = $artistRepository->findOneBy(['idSpotify' => $idSpotify]);
        if ($artist && $user->getFavArtists()->contains($artist)) {
            $user->removeFavArtist($artist);
            $em->flush();
            $this->addFlash('success', 'Artiste supprimé des favoris.');
        } else {
            $this->addFlash('error', 'Artiste introuvable dans vos favoris.');
        }

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_artist_fav'));
    }

    #[IsGranted("ROLE_USER")]
    #[Route('/fav', name: 'app_artist_fav')]
    public function fav(): Response
    {
        $user = $this->getUser();
        return $this->render('artist/fav.html.twig', [
            'fav' => $user->getFavArtists(),
        ]);
    }

    #[Route('/show/{id}', name: 'app_artist_show')]
    public function show(string $id): Response
    {
        $user = $this->getUser();
        $tabFav = [];
        if ($user) {
            foreach ($user->getFavArtists() as $artist) {
                $tabFav[] = $artist->getIdSpotify();
            }
        }
        return $this->render('artist/show.html.twig', [
            'artist' => $this->spotifyRequestService->getArtist($id, $this->token),
            'fav' => $tabFav,
        ]);
    }

    #[Route('/{search?}', name: 'app_artist_index')]
    public function index(?string $search, ArtistRepository $artistRepository): Response
    {
        $user = $this->getUser();
        $tabFav = [];
        if ($user) {
            foreach ($user->getFavArtists() as $artist) {
                $tabFav[] = $artist->getIdSpotify();
            }
        }
        return $this->render('artist/index.html.twig', [
            'artists' => $this->spotifyRequestService->searchArtist($search ?: "hjvqiuv", $this->token),
            'search' => $search,
            'fav' => $tabFav
        ]);
    }
}
