<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieForm;
use App\Repository\SortieRepository;
use App\Utils\Etat;
use Doctrine\ORM\EntityManagerInterface;
use http\Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
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

			$this->addFlash("success", "sortie créée avec succès !");
			return $this->redirectToRoute('main_home');
		}


		return $this->render('sortie/creer.html.twig', [
			'form' => $form,
		]);
	}

	#[Route('/sortie/{id}/modifier', name: 'sortie_modifier', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
	public function modifierSortie(Request $request, Sortie $sortie, EntityManagerInterface $em): Response
	{

		if ($sortie->getOrganisateur() !== $this->getUser() and !$this->isGranted('ROLE_ADMIN')){
			throw $this->createAccessDeniedException("Vous n'avez pas le droit de modifier cette sortie");
		}
		if ($sortie->getEtat() === Etat::OUVERTE){
			throw $this->createAccessDeniedException("Une sortie ne peux pas être modifiée une fois publiée");
		}

		$form = $this->createForm(SortieForm::class, $sortie);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()){
			if ($form->get('publier')->isClicked()){
				$sortie->setEtat(Etat::OUVERTE);
			}
			$em->persist($sortie);
			$em->flush();

			$this->addFlash("success", "Sortie modifiée avec succès !");
			return $this->redirectToRoute('main_home');
		}

		return $this->render('sortie/modifier.html.twig', [
			'form' => $form,
			'sortie' => $sortie,
		]);
	}

	#[Route('/sortie/{id}/supprimer/{token}', name: 'sortie_supprimer', requirements: ['id' => '\d+'], methods: ['GET'])]
	public function supprimerSortie(Sortie $sortie, string $token, EntityManagerInterface $em): Response
	{
		if (!$this->isGranted('ROLE_ADMIN') and $sortie->getOrganisateur() !== $this->getUser()){
			throw $this->createAccessDeniedException("Vous n'avez pas le droit de supprimer cette sortie");
		}
		if($this->isCsrfTokenValid('supprimer-sortie-'.$sortie->getId(), $token)){
			$em->remove($sortie);
			$em->flush();
			$this->addFlash("success", "La sortie à été supprimée avec succès !");
			return $this->redirectToRoute('main_home');
		}
		$this->addFlash("danger", "Une erreur est survenue lors de la suppression de la sortie !");
		return $this->redirectToRoute('sortie_modifier', ['id' => $sortie->getId()]);

	}

	#[Route('/sortie/{id}/annuler', name: 'sortie_annuler', requirements: ['id' => '\d+'], methods: ['GET'])]
	public function annulerSortie(Sortie $sortie, EntityManagerInterface $em): Response
	{
		if ($sortie->getEtat() !== Etat::OUVERTE){
			throw $this->createAccessDeniedException("Vous ne pouvez annuler une sortie que si elle est publiée et non commencée");
		}
		$sortie->setEtat(Etat::ANNULEE);
		$em->persist($sortie);
		$em->flush();
		$this->addFlash("success", "Sortie annulée avec succès !");
		return $this->redirectToRoute('main_home');
	}
}
