<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieForm;
use App\Repository\SortieRepository;
use App\Utils\Etat;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SortieController extends AbstractController
{
	#[Route('/sortie/creer', name: 'sortie_creer')]
	public function creerSortie(Request $request, EntityManagerInterface $em): Response
	{
		$sortie = new Sortie();
		$sortie->setOrganisateur($this->getUser());
		$sortie->setEtat(Etat::EN_CREATION);


		$form = $this->createForm(SortieForm::class, $sortie);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()){
			if ($form->get('publier')->isClicked()){
				$sortie->setEtat(Etat::OUVERTE);
			}
			$em->persist($sortie);
			$em->flush();

			return $this->redirectToRoute('main_home');
		}


		return $this->render('sortie/creer.html.twig', [
			'form' => $form,
		]);
	}
}
