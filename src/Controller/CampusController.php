<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\CampusForm;
use App\Form\FiltreNomForm;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/administration')]
#[IsGranted("ROLE_ADMIN")]
final class CampusController extends AbstractController
{
    #[Route('/campus/{filtre}', name: 'campus_list', methods: ['GET', 'POST' ])]
    public function list(
		CampusRepository $campusRepository,
		EntityManagerInterface $em,
		Request $request,
		?string $filtre = null,
	): Response
    {
		$campus = new Campus();

		$campusForm = $this->createForm(CampusForm::class, $campus);
		$campusForm->handleRequest($request);

		if ($campusForm->isSubmitted() && $campusForm->isValid()){
			$em->persist($campus);
			$em->flush();
			$this->addFlash("success", "Campus ajouté avec succès !");
			return $this->redirectToRoute('campus_list');
		}

		$filtreForm = $this->createForm(FiltreNomForm::class);
		$filtreForm->handleRequest($request);

		if ($filtreForm->isSubmitted()){
			$filtre = $filtreForm->getData()['nom'];
			if ($filtre == ''){
				return $this->redirectToRoute('campus_list');
			}
			return $this->redirectToRoute('campus_list', ['filtre' => $filtre]);
		}

		if ($filtre !== null) {
			$campuss = $campusRepository->search($filtre);
		} else {
			$campuss = $campusRepository->findAll();
		}

        return $this->render('campus/list.html.twig', [
            'campuss' => $campuss,
			'campusForm' => $campusForm,
			'filtreForm' => $filtreForm,
        ]);
    }

	#[Route('/campus/{id}/modifier', name: 'campus_edit')]
	public function edit(
		Campus $campus,
		EntityManagerInterface $em,
		Request $request,
	): Response
    {
		$campusForm = $this->createForm(CampusForm::class, $campus);
		$campusForm->handleRequest($request);

		if($campusForm->isSubmitted() && $campusForm->isValid()){
			$em->persist($campus);
			$em->flush();
			$this->addFlash("success", "Campus modifié avec succès !");
			return $this->redirectToRoute('campus_list');
		}

		return $this->render('campus/edit.html.twig', [
			'campusForm' => $campusForm,
		]);
    }

	#[Route('campus/{id}/supprimer/{token}', name: 'campus_delete')]
	public function delete(
		Campus $campus,
		string $token,
		EntityManagerInterface $em,
	): Response
	{
		if( $this->isCsrfTokenValid('supprimer-campus-'.$campus->getId(), $token))
		{
			$em->remove($campus);
			$em->flush();
			$this->addFlash("success", "Campus supprimé avec succès !");
			return $this->redirectToRoute('campus_list');
		}

		$this->addFlash("danger", "Une erreur est survenue lors de la suppression du campus !");
		return $this->redirectToRoute('campus_edit', ['id' => $campus->getId()]);
	}
}
