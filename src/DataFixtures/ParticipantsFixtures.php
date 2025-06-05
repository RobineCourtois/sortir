<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Participant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantsFixtures extends Fixture implements DependentFixtureInterface
{
	public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
	{
	}
    public function load(ObjectManager $manager): void
    {
       $faker = \Faker\Factory::create('fr_FR');


	   // Création de l'admin
	   $admin = new Participant();
	   $admin->setNom("admin");
	   $admin->setPrenom("admin");
	   $admin->setEmail("admin@eni.fr");
	   $admin->setTelephone("0612345678");
	   $password = $this->passwordHasher->hashPassword($admin, 'Pa$$w0rd');
	   $admin->setPassword($password);
	   $admin->setRoles(['ROLE_ADMIN']);
	   $admin->setCampus($this->getReference('campus0', Campus::class));
	   $admin->setActif(true);
	   $admin->setPseudo("admin");
	   $admin->setAdministrateur(true);
	   $manager->persist($admin);

	   // Création des participants
	   for ($i = 0; $i < 50; $i++) {
		   $praticipant = new Participant();
		   $praticipant->setNom($faker->lastName);
		   $praticipant->setPrenom($faker->firstName);
		   $praticipant->setPseudo("paricipant$i");
		   $praticipant->setEmail("participant$i@eni.fr");
		   $praticipant->setTelephone($faker->phoneNumber);
		   $password = $this->passwordHasher->hashPassword($praticipant, 'Pa$$w0rd');
		   $praticipant->setPassword($password);
		   $praticipant->setRoles(['ROLE_PARTICIPANT']);
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
