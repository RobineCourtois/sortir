<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\UtilisateurForm;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        Participant            $participant,
        Request                $request,
        EntityManagerInterface $em
    ): Response
    {
        $form = $this->createForm(UtilisateurForm::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
        EntityManagerInterface $em
    ): Response
    {
        $participant = new Participant();
        $form = $this->createForm(UtilisateurForm::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Pour la démo, mot de passe par défaut à hasher manuellement
            $participant->setPassword('password'); // À remplacer par un hash sécurisé
            $participant->setActif(true);

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

        // Vérifiele token CSRF
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('toggle-actif-' . $participant->getId(), $token)) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('gestion-utilisateurs');
        }

        // Inverser l'état actif
        $participant->setActif(!$participant->isActif());
        $em->flush();

        // Ajouter un flash en fonction de l'état actif
        if ($participant->isActif()) {
            $this->addFlash('success', sprintf("L'utilisateur %s %s a été activé.", $participant->getPrenom(), $participant->getNom()));
        } else {
            $this->addFlash('warning', sprintf("L'utilisateur %s %s a été désactivé.", $participant->getPrenom(), $participant->getNom()));
        }

        return $this->redirectToRoute('gestion-utilisateurs');
    }

}
