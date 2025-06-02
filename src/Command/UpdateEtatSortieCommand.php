<?php

namespace App\Command;

use App\Repository\SortieRepository;
use App\Utils\Etat;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;


class UpdateEtatSortieCommand extends Command
{
	protected static $defaultName = 'app:update-sortie-status';

	public function __construct(
		private EntityManagerInterface $entityManager,
		private SortieRepository $sortieRepository
	) {
		parent::__construct();
	}

	protected function configure(): void
	{
		$this->setDescription('Met à jour le statut des sorties en fonction des dates');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$sorties = $this->sortieRepository->findAll();
		$now = new \DateTime();

		foreach ($sorties as $sortie) {
			// Mise à jour du statut en fonction des dates
			if ($sortie->getDateLimiteInscription() < $now && $sortie->getDateHeureDebut() > $now && $sortie->getEtat() === Etat::OUVERTE) {
				$sortie->setEtat(Etat::CLOTUREE);
			} elseif ($sortie->getDateHeureDebut() <= $now && $sortie->getDateHeureFin() > $now && $sortie->getEtat() === Etat::CLOTUREE) {
				$sortie->setEtat(Etat::EN_COURS);
			} elseif ($sortie->getDateHeureFin() <= $now && $sortie->getDateHeureFin()->modify('+1 month') > $now && $sortie->getEtat() === Etat::EN_COURS) {
				$sortie->setEtat(Etat::TERMINEE);
			} elseif ($sortie->getDateHeureFin()->modify('+1 month') <= $now && $sortie->getEtat() === Etat::TERMINEE) {
				$sortie->setEtat(Etat::HISTORISEE);
			}
		}

		$this->entityManager->flush();

		$output->writeln('Les statuts des sorties ont été mis à jour avec succès');

		return Command::SUCCESS;
	}
}