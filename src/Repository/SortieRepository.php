<?php

namespace App\Repository;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Utils\Etat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }


    public function findFiltered(Participant $participant, array $filters): array
    {
        $qb = $this->createQueryBuilder('sortie')
            ->leftJoin('sortie.organisateur', 'organisateur')
            ->leftJoin('sortie.participants', 'participants')
            ->addSelect('organisateur', 'participants');

        // Campus
        if (!empty($filters['campus'])) {
            $qb->andWhere('sortie.siteOrganisateur = :campus')
                ->setParameter('campus', $filters['campus']);
        }

        // Nom de la sortie
        if (!empty($filters['search'])) {
            $qb->andWhere('LOWER(sortie.nom) LIKE :search')
                ->setParameter('search', '%' . strtolower($filters['search']) . '%');
        }

        // Dates
        if (!empty($filters['dateDebut']) && !empty($filters['dateFin'])) {
            $qb->andWhere('sortie.dateHeureDebut BETWEEN :debut AND :fin')
                ->setParameter('debut', $filters['dateDebut'])
                ->setParameter('fin', $filters['dateFin']);
        }

        // Organisateur et/ou inscrit/non inscrit
        $orConditions = [];

        // Organisateur
        if (!empty($filters['organisateur'])) {
            $orConditions[] = 'sortie.organisateur = :participant';
        }

        // Inscrit
        if (!empty($filters['inscrit'])) {
            $orConditions[] = ':participant MEMBER OF sortie.participants';
        }

        // Non inscrit
        if (!empty($filters['non_inscrit'])) {
            $orConditions[] = ':participant NOT MEMBER OF sortie.participants';
        }

        if (!empty($orConditions)) {
            $qb->andWhere(implode(' OR ', $orConditions))
                ->setParameter('participant', $participant);
        }

        // État : Terminé OU autres
        $etatConditions = [];
        if (!empty($filters['terminees'])) {
            $etatConditions[] = 'sortie.etat = :etat_terminee';
            $qb->setParameter('etat_terminee', Etat::TERMINEE);
        }
        if (!empty($filters['etat'])) {
            if (is_array($filters['etat'])) {
                $qb->andWhere('sortie.etat IN (:etat)')
                    ->setParameter('etat', $filters['etat']);
            } else {
                $qb->andWhere('sortie.etat = :etat')
                    ->setParameter('etat', $filters['etat']);
            }
        }

        if (!empty($etatConditions)) {
            $qb->andWhere(implode(' OR ', $etatConditions));
        }

        // En création : visible uniquement à l’organisateur
        $qb->andWhere('sortie.etat != :etat_creation OR sortie.organisateur = :participant')
            ->setParameter('etat_creation', Etat::EN_CREATION)
            ->setParameter('participant', $participant);

        // Tri
        return $qb->orderBy('sortie.dateHeureDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }
}