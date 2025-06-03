<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\UtilisateurForm;
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
        EntityManagerInterface $em,
        Request                $request,
    ): Response
    {
        $participants = $participantRepository->findAll();

        return $this->render('administration/utilisateurs.html.twig', [
            'participants' => $participants,
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
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($participant, $plainPassword);
                $participant->setPassword($hashedPassword);
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
            $plainPassword = $form->get('plainPassword')->getData();

            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($participant, $plainPassword);
                $participant->setPassword($hashedPassword);
            }

            if ($participant->isAdministrateur()) {
                $participant->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
            } else {
                $participant->setRoles(['ROLE_USER']);
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
