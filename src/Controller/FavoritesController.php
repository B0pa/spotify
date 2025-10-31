<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_USER')]
#[Route('/favorites')]
final class FavoritesController extends AbstractController
{
    #[Route('/', name: 'app_favorites')]
    public function index(): Response
    {
        $user = $this->getUser();
        return $this->render('favorites/index.html.twig', [
            'favTracks' => $user->getFavTracks(),
            'favArtists' => $user->getFavArtists(),
        ]);
    }
}
