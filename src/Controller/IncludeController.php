<?php

namespace App\Controller;

use App\Form\SearchType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/include')]
class IncludeController extends AbstractController
{
    #[Route('/search-form-track', name: 'app_include_search_form_track', methods: ['GET', 'POST'])]
    public function searchFormTrack(Request $request): Response
    {
        $form = $this->createForm(SearchType::class, null, [
            'action' => $this->generateUrl('app_include_search_form_track'),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            return $this->redirectToRoute('app_track_index', ['search' => $data['query']]);
        }

        return $this->render('include/search_track.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/search-form-artist', name: 'app_include_search_form_artist', methods: ['GET', 'POST'])]
    public function searchFormArtist(Request $request): Response
    {
        $form = $this->createForm(SearchType::class, null, [
            'action' => $this->generateUrl('app_include_search_form_artist'),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            return $this->redirectToRoute('app_artist_index', ['search' => $data['query']]);
        }

        return $this->render('include/search_artist.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}