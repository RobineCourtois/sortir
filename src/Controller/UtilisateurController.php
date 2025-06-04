<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\UtilisateurForm;
use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/administration')]
#[IsGranted("ROLE_ADMIN")]
final class UtilisateurController extends AbstractController
{
    #[Route('/utilisateurs', name: 'gestion-utilisateurs', methods: ['GET', 'POST'])]
    public function utilisateurs(
        ParticipantRepository  $participantRepository,
        CampusRepository       $campusRepository,
        Request                $request
    ): Response {
        $search = $request->query->get('search', '');
        $campusId = $request->query->get('campus');
        $campus = $campusId ? $campusRepository->find($campusId) : null;

        $participants = $participantRepository->findBySearch($search, $campus);

        return $this->render('administration/utilisateurs.html.twig', [
            'participants' => $participants,
            'campuses' => $campusRepository->findAll(), // pour le select dans le twig
            'selectedCampusId' => $campusId, // pour conserver la sélection
            'search' => $search // pour remplir le champ
        ]);
    }


    #[Route('/utilisateur/{id}/modifier', name: 'gestion-utilisateur-modifier', methods: ['GET', 'POST'])]
    public function modifier(
        Participant $participant,
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $form = $this->createForm(UtilisateurForm::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($participant->isAdministrateur()) {
                $participant->setRoles(['ROLE_PARTICIPANT', 'ROLE_ADMIN']);
            } else {
                $participant->setRoles(['ROLE_PARTICIPANT']);
            }

            $em->flush();
            $this->addFlash('success', 'Utilisateur modifié avec succès.');
            return $this->redirectToRoute('gestion-utilisateurs');
        }

        return $this->render('administration/modif-utilisateur.html.twig', [
            'form' => $form->createView(),
            'participant' => $participant,
        ]);
    }

    #[Route('/utilisateur/nouveau', name: 'gestion-utilisateur-nouveau', methods: ['GET', 'POST'])]
    public function nouveau(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $participant = new Participant();

        // ajout de l'option pour la création :
        $form = $this->createForm(UtilisateurForm::class, $participant, [
            'is_creation' => true
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($participant->isAdministrateur()) {
                $participant->setRoles(['ROLE_PARTICIPANT', 'ROLE_ADMIN']);
            } else {
                $participant->setRoles(['ROLE_PARTICIPANT']);
            }

            $em->persist($participant);
            $em->flush();

            $this->addFlash('success', 'Nouvel utilisateur créé avec succès.');
            return $this->redirectToRoute('gestion-utilisateurs');
        }

        return $this->render('administration/utilisateur_nouveau.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    #[Route('/utilisateur/{id}/toggle-actif', name: 'gestion-utilisateur-toggle-actif', methods: ['POST'])]
    public function toggleActif(
        Participant $participant,
        Request $request,
        EntityManagerInterface $em
    ): Response {

        // Vérifie le token CSRF
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('toggle-actif-' . $participant->getId(), $token)) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('gestion-utilisateurs');
        }

        // Inverse l'état actif
        $participant->setActif(!$participant->isActif());
        $em->flush();

        // Ajout d'un flash en fonction de l'état actif
        if ($participant->isActif()) {
            $this->addFlash('success', sprintf("L'utilisateur %s %s a été activé.", $participant->getPrenom(), $participant->getNom()));
        } else {
            $this->addFlash('warning', sprintf("L'utilisateur %s %s a été désactivé.", $participant->getPrenom(), $participant->getNom()));
        }

        return $this->redirectToRoute('gestion-utilisateurs');
    }

}
