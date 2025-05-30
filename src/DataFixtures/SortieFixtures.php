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

        // Choix d'un nombre de jours à soustraire pour la limite (max 10)
        //la date limite doit toujours >= aujourd'hui
            $maxDaysToSubtract = min(10, $dateDebut->diff(new \DateTimeImmutable('now'))->days);

            if ($maxDaysToSubtract > 0) {
                $daysToSubtract = $faker->numberBetween(1, $maxDaysToSubtract);
                $dateLimite = $dateDebut->modify('-' . $daysToSubtract . ' days');
            } else {
                // Si dateDebut est dans 1 jour seulement, pas possible de soustraire plus, alors dateLimite = aujourd'hui
                $dateLimite = new \DateTimeImmutable('now');
            }
            $sortie->setNom($faker->sentence(3));
            $sortie->setDateHeureDebut($dateDebut);
            $sortie->setDateLimiteInscription($dateLimite);
            // Vérifier si date limite est passée par rapport à aujourd'hui
            if ($dateLimite < new \DateTimeImmutable('now')) {
                // Sortie clôturée
                $sortie->setEtat(Etat::CLOTUREE);
            } else {
                // Sortie dans un état aléatoire sauf clôturé
                $etatsPossibles = array_filter(Etat::cases(), fn($e) => $e !== Etat::CLOTUREE);
                $sortie->setEtat($faker->randomElement($etatsPossibles));
            }
            $sortie->setNbInscriptionMax($faker->numberBetween(5, 30));
            $sortie->setInfosSortie($faker->paragraph(2));
            $sortie->setOrganisateur($this->getReference('participant' . $faker->numberBetween(0, 49),Participant::class));
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
