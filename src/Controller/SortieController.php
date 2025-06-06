<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\AnnulationSortieForm;
use App\Form\SortieForm;
use App\Repository\LieuRepository;
use App\Repository\VilleRepository;
use App\Utils\Etat;
use App\Utils\ImageSaver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_PARTICIPANT")]
final class SortieController extends AbstractController
{
    #[Route('/sortie/creer', name: 'sortie_creer')]
    public function creerSortie(
        Request                $request,
        EntityManagerInterface $em,
        LieuRepository         $lieuRepository,
        VilleRepository        $villeRepository,
    ): Response
    {
        $sortie = new Sortie();
        $sortie->setOrganisateur($this->getUser());
        $sortie->setSiteOrganisateur($this->getUser()->getCampus());
        $sortie->setEtat(Etat::EN_CREATION);

        $lieux = $lieuRepository->findAll();
        $tabLieux = [];
        foreach ($lieux as $lieu) {
            $tabLieux[$lieu->getId()] = $lieu;
        }

        $villes = $villeRepository->findAll();

        $form = $this->createForm(SortieForm::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

			try {
				ImageSaver::save($form->get('image')->getData(), $this->getParameter('app.project_images_directory'), $sortie);
			} catch (FileException $e) {
				$this->addFlash('error', "Le fichier n'a pas pu être enregistré");
			}

            if ($form->get('publier')->isClicked()) {
                $sortie->setEtat(Etat::OUVERTE);
            }
            $em->persist($sortie);
            $em->flush();

            $this->addFlash("success", "sortie créée avec succès !");
            return $this->redirectToRoute('main_home');
        }


        return $this->render('sortie/creer.html.twig', [
            'lieux' => $tabLieux,
            'form' => $form,
            'villes' => $villes,
        ]);
    }

    #[Route('/sortie/{id}/modifier', name: 'sortie_modifier', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function modifierSortie(
        Request                $request,
        Sortie                 $sortie,
        EntityManagerInterface $em,
        LieuRepository         $lieuRepository,
        VilleRepository        $villeRepository,
    ): Response
    {

        if ($sortie->getOrganisateur() !== $this->getUser() and !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Vous n'avez pas le droit de modifier cette sortie");
        }
        if ($sortie->getEtat() === Etat::OUVERTE && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Une sortie ne peut pas être modifiée une fois publiée");
        }


        $lieux = $lieuRepository->findAll();
        $tabLieux = [];
        foreach ($lieux as $lieu) {
            $tabLieux[$lieu->getId()] = $lieu;
        }

        $villes = $villeRepository->findAll();

        $form = $this->createForm(SortieForm::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

			try {
				ImageSaver::save($form->get('image')->getData(), $this->getParameter('app.project_images_directory'), $sortie);
			} catch (FileException $e) {
				$this->addFlash('error', "Le fichier n'a pas pu être enregistré");
			}

            if ($form->get('publier')->isClicked()) {
                $sortie->setEtat(Etat::OUVERTE);
            }
            $em->persist($sortie);
            $em->flush();

            $this->addFlash("success", "Sortie modifiée avec succès !");
            return $this->redirectToRoute('main_home');
        }

        return $this->render('sortie/modifier.html.twig', [
            'form' => $form,
            'sortie' => $sortie,
            'lieux' => $tabLieux,
            'villes' => $villes,
        ]);
    }

    #[Route('/sortie/{id}/supprimer/{token}', name: 'sortie_supprimer', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function supprimerSortie(Sortie $sortie, string $token, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('supprimer-sortie-' . $sortie->getId(), $token)) {
            $em->remove($sortie);
            $em->flush();
            $this->addFlash("success", "La sortie à été supprimée avec succès !");
            return $this->redirectToRoute('main_home');
        }
        $this->addFlash("danger", "Une erreur est survenue lors de la suppression de la sortie !");
        return $this->redirectToRoute('sortie_modifier', ['id' => $sortie->getId()]);

    }

    #[Route('/sortie/{id}', name: 'sortie_details', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function detail(Sortie $sortie): Response
    {
        return $this->render('sortie/details.html.twig', [
            'sortie' => $sortie,
        ]);
    }


    #[Route('/sortie/{id}/inscription', name: 'sortie_inscrire', methods: ['POST'])]
    public function inscrire(Sortie $sortie, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user instanceof Participant) {
            $this->addFlash('danger', 'Vous devez être connecté pour vous inscrire.');
            return $this->redirectToRoute('app_login');
        }

        // Déjà inscrit
        if ($sortie->getParticipants()->contains($user)) {
            $this->addFlash('info', 'Vous êtes déjà inscrit(e) à cette sortie.');
            return $this->redirectToRoute('main_home');
        }

        //  Sortie non ouverte
        if ($sortie->getEtat() !== Etat::OUVERTE) {
            $this->addFlash('danger', 'Cette sortie n’est pas ouverte à l’inscription.');
            return $this->redirectToRoute('main_home');
        }

        //  Sortie complète
        if (
            $sortie->getNbInscriptionMax() !== null &&
            $sortie->getParticipants()->count() >= $sortie->getNbInscriptionMax()
        ) {
            $this->addFlash('danger', 'Cette sortie est déjà complète.');
            return $this->redirectToRoute('main_home');
        }

        //  Date limite d’inscription dépassée
        if ($sortie->getDateLimiteInscription() < new \DateTimeImmutable()) {
            $this->addFlash('danger', 'La date limite d’inscription est dépassée.');
            return $this->redirectToRoute('main_home');
        }

        //  Inscription OK
        $sortie->addParticipant($user);
        if ($sortie->getNbInscriptionMax() == $sortie->getParticipants()->count()) {
            $sortie->setEtat(Etat::CLOTUREE);
        }
        $em->flush();
        $this->addFlash('success', 'Inscription réussie à la sortie !');

        return $this->redirectToRoute('main_home');
    }

    #[Route('/sortie/{id}/desister', name: 'sortie_desister', methods: ['POST'])]
    public function desister(Sortie $sortie, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user instanceof Participant) {
            $this->addFlash('danger', 'Vous devez être connecté pour vous désister.');
            return $this->redirectToRoute('app_login');
        }

        // Si la sortie a déjà commencé
        if ($sortie->getDateHeureDebut() < new \DateTimeImmutable()) {
            $this->addFlash('danger', 'Vous ne pouvez plus vous désister d’une sortie déjà commencée.');
            return $this->redirectToRoute('main_home');
        }


        // Désistement
        if ($sortie->getParticipants()->contains($user)) {
            if ($sortie->getNbInscriptionMax() == $sortie->getParticipants()->count()) {
                $sortie->setEtat(Etat::OUVERTE);
            }
            $sortie->removeParticipant($user);
            $em->flush();
            $this->addFlash('success', 'Vous vous êtes désisté(e) de la sortie.');
        } else {
            $this->addFlash('warning', 'Vous n\'êtes pas inscrit(e) à cette sortie.');
        }

        return $this->redirectToRoute('main_home');
    }

    // Affiche le formulaire pour saisir un motif d'annulation
    #[Route('/sortie/{id}/annuler', name: 'sortie_annuler_form', methods: ['GET'])]
    public function annulerForm(Sortie $sortie)
    {
        if ($this->getUser() !== $sortie->getOrganisateur() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Vous n'avez pas le droit d'annuler cette sortie");
        }

        return $this->render('sortie/annuler_form.html.twig', [
            'sortie' => $sortie
        ]);
    }

// Traite la soumission du formulaire d'annulation avec motif
    #[Route('/sortie/{id}/annuler', name: 'sortie_annuler_form', methods: ['GET', 'POST'])]
    public function annuler(Sortie $sortie, Request $request, EntityManagerInterface $em)
    {
        if ($this->getUser() !== $sortie->getOrganisateur() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Vous n'avez pas le droit d'annuler cette sortie");
        }

        $form = $this->createForm(AnnulationSortieForm::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sortie->setEtat(Etat::ANNULEE);
            $em->flush();

            $this->addFlash('success', 'La sortie a bien été annulée.');
            return $this->redirectToRoute('main_home');
        }

        return $this->render('sortie/annuler_form.html.twig', [
            'form' => $form->createView(),
            'sortie' => $sortie
        ]);
    }
}
