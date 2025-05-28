<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\SortieForm;
use App\Repository\SortieRepository;
use App\Utils\Etat;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
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
    #[Route('/sortie/{id}/inscrire', name: 'sortie_inscrire')]
    public function inscrire(Sortie $sortie, EntityManagerInterface $em, Security $security): Response
    {
        $participant = $security->getUser();

        if (!$participant instanceof Participant) {
            throw new \LogicException('Utilisateur non connecté ou invalide.');
        }

        if (!$sortie->getParticipants()->contains($participant)) {
            $sortie->addParticipant($participant);
            $em->flush();
            $this->addFlash('success', '✅ Vous êtes bien inscrit(e) à la sortie.');
        }

        return $this->redirectToRoute('main_home');
    }


    #[Route('/sortie/{id}/desister', name: 'sortie_desister')]
    public function desister(Sortie $sortie, EntityManagerInterface $em, Security $security): Response
    {
        $participant = $security->getUser();

        if (!$participant instanceof Participant) {
            throw new \LogicException('Utilisateur non connecté ou invalide.');
        }

        if ($sortie->getParticipants()->contains($participant)) {
            $sortie->removeParticipant($participant);
            $em->flush();
            $this->addFlash('success', '⚠️ Vous vous êtes désisté(e) de la sortie.');
        }

        return $this->redirectToRoute('main_home');
    }

    #[Route('/sortie/{id}', name: 'sortie_details')]
    public function details(Sortie $sortie): Response
    {
        return $this->render('sortie/details.html.twig', [
            'sortie' => $sortie,
        ]);
    }



}
