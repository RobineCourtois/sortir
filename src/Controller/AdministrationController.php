<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdministrationController extends AbstractController
{
    #[Route('/administration', name: 'app_administration')]
    public function menu(): Response
    {
        return $this->render('administration/menu.html.twig', [
            'controller_name' => 'AdministrationController',
        ]);
    }
}
