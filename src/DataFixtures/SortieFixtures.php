<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Utils\Etat;
use DateInterval;
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

            $dateDebut = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-5 month', '+1 month'));

			$dateCloture = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween(
				($dateDebut->sub(new \DateInterval('P' . $faker->numberBetween(1, 3) . 'D')))->format('Y-m-d H:i:s')
				, $dateDebut->format('Y-m-d H:i:s')));

            $sortie->setNom($faker->sentence(3));
            $sortie->setDateHeureDebut($dateDebut);
            $sortie->setDateLimiteInscription($dateCloture);


            $sortie->setNbInscriptionMax($faker->numberBetween(5, 30));
            $sortie->setInfosSortie($faker->paragraph(2));
            $sortie->setOrganisateur($this->getReference('participant' . $faker->numberBetween(0, 49),Participant::class));
            $sortie->setLieu($this->getReference('lieu' . $faker->numberBetween(0, 19), Lieu::class));
            $sortie->setDuree($faker->numberBetween(30, 180));
            $sortie->setSiteOrganisateur($this->getReference('campus' . $faker->numberBetween(0, 3), Campus::class));

            for ($j = 0; $j < $faker->numberBetween(0, 15); $j++) {
                $sortie->addParticipant($this->getReference('participant' . $faker->numberBetween(0, 49), Participant::class));
            }

			if ($faker->boolean(10)){
				$sortie->setEtat(Etat::ANNULEE);
			} else if ($faker->boolean(10)){
				$sortie->setEtat(Etat::EN_CREATION);
			} else {
				$sortie->setEtat(Etat::OUVERTE);
			}

			if ($dateCloture < new \DateTimeImmutable('now') || $sortie->getNbInscriptionMax() == $sortie->getParticipants()->count()) {
				// Sortie clôturée
				$sortie->setEtat(Etat::CLOTUREE);
			}
			$dateFin = $dateDebut->add(new DateInterval('PT' . $sortie->getDuree() . 'M'));
			if ($dateFin <= new \DateTimeImmutable('now')){
				$sortie->setEtat(Etat::TERMINEE);
			}
			if ($dateDebut <= (new \DateTimeImmutable('now'))->sub(new DateInterval('P30D'))){
				$sortie->setEtat(Etat::HISTORISEE);
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
