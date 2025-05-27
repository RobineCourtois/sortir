<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;


class SortieFixtures extends Fixture
{
    public function load(ObjectManager $em): void
    {
        $faker = Factory::create('fr_FR');

        $etats = $em->getRepository(Etat::class)->findAll();
        $organisateurs = $em->getRepository(Participant::class)->findAll();
        $lieux = $em->getRepository(Lieu::class)->findAll();

        // Sortie "test" fixe
        $sortieTest = new Sortie();
        $dateDebutTest = new \DateTimeImmutable('+7 days');
        $dateLimiteTest = $dateDebutTest->modify('-3 days');
        $sortieTest->setNom('Sortie Test Symfony');
        $sortieTest->setDateHeureDebut($dateDebutTest);
        $sortieTest->setDateLimiteInscription($dateLimiteTest);
        $sortieTest->setNbInscriptionMax(20);
        $sortieTest->setInfosSortie('Sortie créée pour les tests.');
        $sortieTest->setOrganisateur($faker->randomElement($organisateurs));
        $sortieTest->setEtat($faker->randomElement($etats));
        $sortieTest->setLieu($faker->randomElement($lieux));
        $sortieTest->setDuree($faker->numberBetween(30, 180));

        if (method_exists($sortieTest, 'addParticipant')) {
            $participantsInscrits = $faker->randomElements($organisateurs, $faker->numberBetween(1, 5));
            foreach ($participantsInscrits as $participant) {
                $sortieTest->addParticipant($participant);
            }
        }

        $em->persist($sortieTest);

        // Génération aléatoire de sorties
        for ($i = 0; $i < 20; $i++) {
            $sortie = new Sortie();

            $dateDebut = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('+1 day', '+1 month'));
            $dateLimite = $dateDebut->modify('-' . $faker->numberBetween(1, 10) . ' days');

            $sortie->setNom($faker->sentence(3));
            $sortie->setDateHeureDebut($dateDebut);
            $sortie->setDateLimiteInscription($dateLimite);
            $sortie->setNbInscriptionMax($faker->numberBetween(5, 30));
            $sortie->setInfosSortie($faker->paragraph(2));
            $sortie->setOrganisateur($faker->randomElement($organisateurs));
            $sortie->setEtat($faker->randomElement($etats));
            $sortie->setLieu($faker->randomElement($lieux));
            $sortie->setDuree($faker->numberBetween(30, 180));

            if (method_exists($sortie, 'addParticipant')) {
                $participantsInscrits = $faker->randomElements($organisateurs, $faker->numberBetween(1, 5));
                foreach ($participantsInscrits as $participant) {
                    $sortie->addParticipant($participant);
                }
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
