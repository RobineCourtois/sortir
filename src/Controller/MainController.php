<?php

namespace App\Controller;

use App\Form\FiltreSortieForm;
use App\Repository\CampusRepository;
use App\Repository\SortieRepository;
use App\Utils\Etat;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_PARTICIPANT")]
final class MainController extends AbstractController
{
    #[Route('/', name: 'main_home', methods: ['GET', 'POST'])]
    public function home(Request $request, SortieRepository $sortieRepository, CampusRepository $campusRepository): Response
    {
        // Création du formulaire avec campus par défaut
        $form = $this->createForm(FiltreSortieForm::class, [
            'campus' => $this->getUser()->getCampus(),
        ], [
            'campus_choices' => $campusRepository->findAll()
        ]);

        $form->handleRequest($request);

        $filters = $form->getData();

        // Par défaut, état "OUVERTE" si aucune case n’est cochée
        if (
            empty($filters['organisateur']) &&
            empty($filters['inscrit']) &&
            empty($filters['non_inscrit']) &&
            empty($filters['terminees'])
        ) {
            $filters['etat'] = Etat::OUVERTE;
        }

        // Méthode personnalisée de filtrage
        $sorties = $sortieRepository->findFiltered($this->getUser(), $filters);

        return $this->render('main/home.html.twig', [
            'form' => $form->createView(),
            'sorties' => $sorties,
            'participant' => $this->getUser(),
        ]);
    }
}