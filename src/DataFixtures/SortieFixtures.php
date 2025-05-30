<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Utils\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;


class SortieFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $em): void
    {
        $faker = Factory::create('fr_FR');


        // Génération aléatoire de sorties
        for ($i = 0; $i < 100; $i++) {
            $sortie = new Sortie();

            $dateDebut = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('+1 day', '+1 month'));
            $dateLimite = $dateDebut->modify('-' . $faker->numberBetween(1, 10) . ' days');

            $sortie->setNom($faker->sentence(3));
            $sortie->setDateHeureDebut($dateDebut);
            $sortie->setDateLimiteInscription($dateLimite);
            $sortie->setNbInscriptionMax($faker->numberBetween(5, 30));
            $sortie->setInfosSortie($faker->paragraph(2));
            $sortie->setOrganisateur($this->getReference('participant' . $faker->numberBetween(0, 49),Participant::class));
            $sortie->setEtat($faker->randomElement(Etat::cases()));;
            $sortie->setLieu($this->getReference('lieu' . $faker->numberBetween(0, 19), Lieu::class));
            $sortie->setDuree($faker->numberBetween(30, 180));
            $sortie->setSiteOrganisateur($this->getReference('campus' . $faker->numberBetween(0, 3), Campus::class));

            for ($j = 0; $j < $faker->numberBetween(0, 15); $j++) {
                $sortie->addParticipant($this->getReference('participant' . $faker->numberBetween(0, 49), Participant::class));
            }

            $em->persist($sortie);
        }

        $em->flush();
    }

    public function getDependencies(): array
    {
        return [
            VilleFixtures::class,
            LieuFixtures::class,
        ];
    }
}
