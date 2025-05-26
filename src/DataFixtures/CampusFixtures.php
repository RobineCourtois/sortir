<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CampusFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
		$nantes = new Campus();
		$nantes->setNom("Nantes");
		$manager->persist($nantes);
		$this->addReference('campus0', $nantes);

		$rennes = new Campus();
		$rennes->setNom("Rennes");
		$manager->persist($rennes);
		$this->addReference('campus1', $rennes);

		$quimper = new Campus();
		$quimper->setNom("Quimper");
		$manager->persist($quimper);
		$this->addReference('campus2', $quimper);

		$niort = new Campus();
		$niort->setNom("Niort");
		$manager->persist($niort);
		$this->addReference('campus3', $niort);


        $manager->flush();
    }
}
