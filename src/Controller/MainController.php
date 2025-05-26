<?php

namespace App\Controller;

use App\Repository\CampusRepository;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    #[Route('/', name: 'main_home', methods: ['GET'])]
    public function home(Request $request, SortieRepository $sortieRepository, CampusRepository $campusRepository): Response
    {
        $user = $this->getUser();
        $campusList = $campusRepository->findAll();

        //todo : faire un tri par état
        $sorties = $sortieRepository->findAll();


        return $this->render('main/home.html.twig', [
            'sorties' => $sorties,
            'campusList' => $campusList,
            'user' => $user,
        ]);
    }
}
