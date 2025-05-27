<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieForm;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SortieController extends AbstractController
{
    #[Route('/sortie', name: 'app_sortie')]
    public function listeSoirties(SortieRepository $sortieRepository): Response
    {
        $sorties = $sortieRepository->findAll();
        return $this->render('home.html.twig', [
            'sorties' => $sorties
        ]);
    }

	#[Route('/sortie/creer', name: 'sortie_creer')]
	public function creerSortie(): Response
	{
		$sortie = new Sortie();
		$form = $this->createForm(SortieForm::class, $sortie);


		return $this->render('sortie/creer.html.twig', [
			'form' => $form,
		]);
	}
}
