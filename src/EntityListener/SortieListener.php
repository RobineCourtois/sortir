<?php

namespace App\EntityListener;

use App\Entity\Sortie;
use App\Utils\Etat;

class SortieListener
{
	public function postLoad(Sortie $sortie): void
	{
		if( $sortie->getEtat() == Etat::OUVERTE && new \DateTimeImmutable('now') > $sortie->getDateLimiteInscription()){
			$sortie->setEtat(Etat::CLOTUREE);
		}
		if( $sortie->getEtat() == Etat::CLOTUREE && new \DateTimeImmutable('now') < $sortie->getDateHeureDebut()){
			$sortie->setEtat(Etat::EN_COURS);
		}
		if( $sortie->getEtat() == Etat::EN_COURS && new \DateTimeImmutable('now') > $sortie->getDateHeureFin()){
			$sortie->setEtat(Etat::TERMINEE);
		}
		if(
			($sortie->getEtat() == Etat::TERMINEE || $sortie->getEtat() == Etat::ANNULEE)
			&& new \DateTimeImmutable('now') > $sortie->getDateHeureFin()->modify('+ 1 month')
		){
			$sortie->setEtat(Etat::HISTORISEE);
		}
	}
}