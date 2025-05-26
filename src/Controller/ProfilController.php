<?php

namespace App\Controller;

use App\Form\ProfilForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'profil_consulter')]
    public function consulter(): Response
    {
		$participant = $this->getUser();


        return $this->render('profil/consulter.html.twig', [
            "participant" => $participant,
        ]);
    }

	#[Route('/profil/modifier', name: 'profil_modifier')]
	public function modifier(): Response
	{
		$form = $this->createForm(ProfilForm::class, $this->getUser());

		return $this->render('profil/modifier.html.twig', [
			"form" => $form->createView(),
		]);
	}
}
