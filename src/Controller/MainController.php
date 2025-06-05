<?php

namespace App\Controller;

use App\Form\FiltreSortieForm;
use App\Repository\CampusRepository;
use App\Repository\SortieRepository;
use App\Utils\Etat;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_PARTICIPANT")]
final class MainController extends AbstractController
{
    #[Route('/', name: 'main_home', methods: ['GET', 'POST'])]
    public function home(
        Request $request,
        SortieRepository $sortieRepository,
        CampusRepository $campusRepository
    ): Response {
        $participant = $this->getUser();


        // Création du formulaire avec campus par défaut
        $form = $this->createForm(FiltreSortieForm::class, [
            'campus' => $this->getUser()->getCampus(),
        ], [
            'campus_choices' => $campusRepository->findAll()
        ]);

        $form->handleRequest($request);

        $filters = $form->getData();

        // Détecter si l'utilisateur est admin
        $isAdmin = $this->isGranted('ROLE_ADMIN');

        if ($isAdmin) {
            if (empty($filters['etat']) || count($filters['etat']) === 0) {
                // Remplir le filtre d’état avec tous les états disponibles
                $filters['etat'] = array_map(
                    fn($case) => $case->value,
                    Etat::cases()
                );
            }
        } else {
            // Pour un participant normal : si aucun filtre organisteur, inscrit ou terminée, par défaut on affiche OUVERTE + ANNULEE
            if (
                empty($filters['organisateur']) &&
                empty($filters['inscrit']) &&
                empty($filters['terminees'])
            ) {
                $filters['etat'] = [Etat::OUVERTE->value, Etat::ANNULEE->value];
            }
        }

            // Appel commun à la méthode findFiltered, avec filtre d’états ajusté
            $sorties = $sortieRepository->findFiltered($participant, $filters, $isAdmin);

        // Créer la date du jour
        $dateDuJour = new \DateTimeImmutable();

        return $this->render('main/home.html.twig', [
            'form' => $form->createView(),
            'sorties' => $sorties,
            'participant' => $this->getUser(),
            'dateDuJour' => $dateDuJour
        ]);
    }


}