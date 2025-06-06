<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\FiltreSortieForm;
use App\Form\VilleForm;
use App\Repository\CampusRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use App\Utils\Etat;
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

	#[Route('/sorties', name: 'admin_sorties', methods: 'GET')]
	public function listSorties(
		Request $request,
		SortieRepository $sortieRepository,
		CampusRepository $campusRepository,
	): Response
	{
		$form = $this->createForm(FiltreSortieForm::class, [], [
			'campus_choices' => $campusRepository->findAll(),
		]);

		$form->handleRequest($request);
		$filters = $form->getData();

		if (empty($filters['état']) || count($filters['état']) === 0) {
			$filters['état'] = array_map(
				fn($case) => $case->value,
				Etat::cases()
			);
		}

		$sorties = $sortieRepository->findFiltered($this->getUser(), $filters, true);

		return $this->render('administration/sorties.html.twig', [
			'form' => $form,
			'sorties' => $sorties,
			'dateDuJour' => new \DateTimeImmutable()
		]);
	}


}
