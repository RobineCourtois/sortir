<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfilForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class ProfilController extends AbstractController
{
    #[Route('/profil/{id}', name: 'profil_consulter')]
    public function consulter(Participant $participant): Response
    {
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
			/** @var string $plainPassword */
			$plainPassword = $form->get('plainPassword')->getData();

			// encodage du mot de passe
			$participant->setPassword($hasher->hashPassword($participant, $plainPassword));
			$em->persist($participant);
			$em->flush();

			return $security->login($participant, 'form_login', 'main');
		}


		return $this->render('profil/modifier.html.twig', [
			"form" => $form->createView(),
		]);
	}
}
