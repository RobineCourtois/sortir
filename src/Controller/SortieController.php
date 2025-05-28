<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieForm;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SortieController extends AbstractController
{
	#[Route('/sortie/creer', name: 'sortie_creer')]
	public function creerSortie(Request $request): Response
	{
		$sortie = new Sortie();
		$sortie->setOrganisateur($this->getUser());

		$form = $this->createForm(SortieForm::class, $sortie);
		$form->handleRequest($request);


		return $this->render('sortie/creer.html.twig', [
			'form' => $form,
		]);
	}
}
