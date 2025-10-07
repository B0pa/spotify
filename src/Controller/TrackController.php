<?php

namespace App\Controller;

use App\Service\AuthSpotifyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;



#[Route('/track')]
final class TrackController extends AbstractController
{
    private string $token;

    public function __construct(private readonly AuthSpotifyService $authSpotifyService){
        $this->token=$this->authSpotifyService->auth();
        dd($this->token);

    }

    #[Route('/', name: 'app_track')]
    public function index(): Response
    {
        return $this->render('track/index.html.twig', [
            'controller_name' => 'TrackController',
        ]);
    }
}
