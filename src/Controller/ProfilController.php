<?php

namespace App\Controller;

use App\Form\ProfilForm;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_PARTICIPANT")]
final class ProfilController extends AbstractController
{
    #[Route('/profil/{id}', name: 'profil_consulter', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function consulter(int $id, ParticipantRepository $participantRepository): Response
    {
		$participant = $participantRepository->find($id);

		if (!$participant){
			throw new NotFoundHttpException("Le participant n'existe pas");
		}

        return $this->render('profil/consulter.html.twig', [
            "participant" => $participant,
        ]);
    }

	#[Route('/profil', name: 'profil_modifier', methods: ['GET', 'POST'])]
	public function modifier(
		Request $request,
		UserPasswordHasherInterface $hasher,
		EntityManagerInterface $em,
		Security $security,
	): Response
	{
		$participant = $this->getUser();

		$form = $this->createForm(ProfilForm::class, $participant);

		$form->handleRequest($request);


		if ($form->isSubmitted() && $form->isValid()){

//			dd($form->getData());

			/** @var string $plainPassword */
			$plainPassword = $form->get('plainPassword')->getData();

			// encodage du mot de passe
			$participant->setPassword($hasher->hashPassword($participant, $plainPassword));
			$em->persist($participant);
			$em->flush();

			$this->addFlash("success", "Profil modifié avec succès !");
//			return $security->login($participant, 'form_login', 'main');
			return $this->redirectToRoute('main_home');
		}


		return $this->render('profil/modifier.html.twig', [
			"form" => $form,
		]);
	}
}
