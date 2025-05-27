<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LieuFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
       $faker = \Faker\Factory::create('fr_FR');

	   for ($i = 0; $i < 20; $i++) {
		   $lieu = new Lieu();
		   $lieu->setNom($faker->company);
		   $lieu->setRue($faker->streetAddress);
		   $lieu->setLatitude($faker->latitude);
		   $lieu->setLongitude($faker->longitude);
		   $lieu->setVille($this->getReference('ville'.$faker->numberBetween(0,9), Ville::class));
		   $manager->persist($lieu);
		   $this->addReference('lieu'.$i, $lieu);
	   }

	   $manager->flush();
    }

	public function getDependencies(): array
	{
		return [
			VilleFixtures::class,
		];
	}
}
