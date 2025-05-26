<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Participant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ParticipantsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
       $faker = \Faker\Factory::create();

	   for ($i = 0; $i < 50; $i++) {
		   $praticipant = new Participant();
		   $praticipant->setNom($faker->lastName);
		   $praticipant->setPrenom($faker->firstName);
		   $praticipant->setEmail("participant$i@eni.fr");
		   $praticipant->setTelephone($faker->phoneNumber);
		   $praticipant->setPassword("123456");
		   $praticipant->setCampus($this->getReference('campus'.$faker->numberBetween(0,3), Campus::class));;
		   $praticipant->setActif($faker->boolean(75));
		   $manager->persist($praticipant);
		   $this->addReference('participant'.$i, $praticipant);
	   }

	   $manager->flush();
    }

	public function getDependencies(): array
	{
		return [
			CampusFixtures::class,
		];
	}
}
