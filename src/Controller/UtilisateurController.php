<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\UtilisateurForm;
use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

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
    ): Response {
        // Ajout du paramètre 'is_creation' => false pour ne pas afficher le champ password
        $form = $this->createForm(UtilisateurForm::class, $participant, [
            'is_creation' => false
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gère le rôle en fonction du booléen administrateur
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
        UserPasswordHasherInterface $hasher,
        ResetPasswordHelperInterface $resetPasswordHelper,
        UrlGeneratorInterface $urlGenerator,
        MailerInterface $mailer
    ): Response {
        $participant = new Participant();
        $participant->setMustChangePassword(true);

        $form = $this->createForm(UtilisateurForm::class, $participant, [
            'is_creation' => true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $participant->setPassword($hasher->hashPassword($participant, 'Pa$$word'));

            if ($participant->isAdministrateur()) {
                $participant->setRoles(['ROLE_PARTICIPANT', 'ROLE_ADMIN']);
            } else {
                $participant->setRoles(['ROLE_PARTICIPANT']);
            }

            $em->persist($participant);
            $em->flush();

            // Générer le token de réinitialisation
            $resetToken = $resetPasswordHelper->generateResetToken($participant);

            // Générer l'URL complète de reset
            $resetUrl = $urlGenerator->generate(
                'app_reset_password',
                ['token' => $resetToken->getToken()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            // Création et envoi de l'email
            $email = (new TemplatedEmail())
                ->from('no-reply@sortir.com')
                ->to($participant->getEmail())
                ->subject('Réinitialisez votre mot de passe')
                ->htmlTemplate('reset_password/email.html.twig')
                ->context([
                    'resetUrl' => $resetUrl,
                    'user' => $participant
                ]);

            $mailer->send($email);

            $this->addFlash('success', 'Nouvel utilisateur créé avec succès. Un e-mail de réinitialisation a été envoyé.');
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
