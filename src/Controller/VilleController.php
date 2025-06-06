<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\FiltreNomForm;
use App\Form\VilleForm;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/administration')]
#[IsGranted("ROLE_ADMIN")]
final class VilleController extends AbstractController
{
	#[Route('/villes/{filtre}', name: 'villes_list', methods: ['GET', 'POST'])]
	public function list(
		VilleRepository $villeRepository,
		EntityManagerInterface $em,
		Request $request,
		?string $filtre = null,
	): Response
	{
		$ville = new Ville();

		$villeForm = $this->createForm(VilleForm::class, $ville);
		$villeForm->handleRequest($request);

		if ($villeForm->isSubmitted() && $villeForm->isValid()){
			$em->persist($ville);
			$em->flush();
			$this->addFlash("success", "Ville ajoutée avec succes !");
			return $this->redirectToRoute('villes_list');
		}

		$filtreForm = $this->createForm(FiltreNomForm::class);
		$filtreForm->handleRequest($request);

		if ($filtreForm->isSubmitted()){
			$filtre = $filtreForm->getData()['nom'];
			if ($filtre == ''){
				return $this->redirectToRoute('villes_list');
			}
			return $this->redirectToRoute('villes_list', ['filtre' => $filtre]);
		}

		if ($filtre !== null) {
			$villes = $villeRepository->search($filtre);
		} else {
			$villes = $villeRepository->findAll();
		}

		return $this->render('ville/list.html.twig', [
			'villes' => $villes,
			'villeForm' => $villeForm,
			'filtreForm' => $filtreForm,
		]);
	}

    #[Route('/villes/{id}/modifier', name: 'ville_edit')]
    public function edit(
		Ville $ville,
		EntityManagerInterface $em,
		Request $request,
	): Response
    {
		$villeForm = $this->createForm(VilleForm::class, $ville);

		$villeForm->handleRequest($request);

		if ($villeForm->isSubmitted() && $villeForm->isValid()){
			$em->persist($ville);
			$em->flush();
			$this->addFlash("success", "Ville Modifiée avec succes !");
			return $this->redirectToRoute('villes_list');
		}

        return $this->render('ville/edit.html.twig', [
            'villeForm' => $villeForm,
        ]);
    }

	#[Route('villes/{id}/supprimer/{token}', name: 'ville_delete')]
	public function delete(
		Ville $ville,
		string $token,
		EntityManagerInterface $em,
	): Response
	{
		if( $this->isCsrfTokenValid('supprimer-ville-'.$ville->getId(), $token))
		{
			$em->remove($ville);
			$em->flush();
			$this->addFlash("success", "Ville supprimée avec succes !");
			return $this->redirectToRoute('villes_list');
		}
		$this->addFlash("danger", "Une erreur est survenue lors de la suppression de la ville !");
		return $this->redirectToRoute('ville_edit', ['id' => $ville->getId()]);
	}

}
