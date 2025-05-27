<?php

namespace App\Controller;

use App\Form\FiltreSortieForm;
use App\Repository\CampusRepository;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    #[Route('/', name: 'main_home', methods: ['GET'])]
    public function home(Request $request, SortieRepository $sortieRepository, CampusRepository $campusRepository): Response
    {
        $form = $this->createForm(FiltreSortieForm::class, null, [
            'campus_choices' => $campusRepository->findAll() // ou une liste transformée ['Nom campus' => id]
        ]);

        $form->handleRequest($request);

        $filters = $form->getData();

        $sorties = $sortieRepository->findBy(['etat' => 'Ouverte']);

        return $this->render('main/home.html.twig', [
            'form' => $form->createView(),
            'sorties' => $sorties,
        ]);

    }
}
