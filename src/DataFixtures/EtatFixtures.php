<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EtatFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
		$enCreation = new Etat();
		$enCreation->setLibelle("En création");
		$manager->persist($enCreation);
		$this->addReference('etat0', $enCreation);

		$ouverte = new Etat();
		$ouverte->setLibelle("Ouverte");
		$manager->persist($ouverte);
		$this->addReference('etat1', $ouverte);

		$cloturee = new Etat();
		$cloturee->setLibelle("Clôturée");
		$manager->persist($cloturee);
		$this->addReference('etat2', $cloturee);

		$enCours = new Etat();
		$enCours->setLibelle("En cours");
		$manager->persist($enCours);
		$this->addReference('etat3', $enCours);

		$terminee = new Etat();
		$terminee->setLibelle("Terminée");
		$manager->persist($terminee);
		$this->addReference('etat4', $terminee);

		$annulee = new Etat();
		$annulee->setLibelle("Annulée");
		$manager->persist($annulee);
		$this->addReference('etat5', $annulee);

		$historisee = new Etat();
		$historisee->setLibelle("Historisée");
		$manager->persist($historisee);
		$this->addReference('etat6', $historisee);


        $manager->flush();
    }
}
