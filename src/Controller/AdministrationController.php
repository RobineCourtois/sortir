<?php

namespace App\Controller;

use App\Entity\Ville;
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
final class AdministrationController extends AbstractController
{
    #[Route('/', name: 'app_administration')]
    public function menu(): Response
    {
        return $this->render('administration/menu.html.twig', [
            'controller_name' => 'AdministrationController',
        ]);
    }



}
