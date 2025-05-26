<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'sortir_profil')]
    public function consulter(): Response
    {
		$participant = $this->getUser();


        return $this->render('profil/consulter.html.twig', [
            "participant" => $participant,
        ]);
    }
}
