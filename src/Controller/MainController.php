<?php

namespace App\Controller;

use App\Form\FiltreSortieForm;
use App\Repository\CampusRepository;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route; // ou Attribute\Route si tu utilises PHP 8+
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_PARTICIPANT")]
final class MainController extends AbstractController
{
    #[Route('/', name: 'main_home', methods: ['GET', 'POST'])]
    public function home(Request $request, SortieRepository $sortieRepository, CampusRepository $campusRepository): Response
    {
        // Création du formulaire avec choix des campus
        $form = $this->createForm(FiltreSortieForm::class, [
            'campus' => $this->getUser()->getCampus(), // valeur par défaut
        ], [
            'campus_choices' => $campusRepository->findAll()
        ]);


        $form->handleRequest($request);

        $filters = $form->getData();

        if ($form->isSubmitted() && $form->isValid()) {

            $criteria = [];

            if ($filters['campus'] ?? null) {
                $criteria['campus'] = $filters['campus'];
            }

           //todo : filtres pour formulaire

            $sorties = $sortieRepository->findBy($criteria);
        } else {

            $sorties = $sortieRepository->findAll();

        }

        return $this->render('main/home.html.twig', [
            'form' => $form->createView(),
            'sorties' => $sorties,
            'participant' => $this->getUser()
        ]);
    }
}
