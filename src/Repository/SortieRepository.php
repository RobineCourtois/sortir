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

//    /**
//     * @return Sortie[] Returns an array of Sortie objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Sortie
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function findFiltered( Participant $participant, array $filters): array
    {
        $queryBuilder = $this->createQueryBuilder('sortie')
            ->leftJoin('sortie.organisateur', 'organisateur')
            ->leftJoin('sortie.participants', 'participants')
            ->addSelect('organisateur', 'participants');

        // Campus (siteOrganisateur)
        if (!empty($filters['campus'])) {
            $queryBuilder->andWhere('sortie.siteOrganisateur = :campus')
                ->setParameter('campus', $filters['campus']);
        }

        // Nom de la sortie (search)
        if (!empty($filters['search'])) {
            $queryBuilder->andWhere('LOWER(sortie.nom) LIKE :search')
                ->setParameter('search', '%' . strtolower($filters['search']) . '%');
        }

        // Dates
        if (!empty($filters['dateDebut']) && !empty($filters['dateFin'])) {
            $queryBuilder->andWhere('sortie.dateHeureDebut BETWEEN :debut AND :fin')
                ->setParameter('debut', $filters['dateDebut'])
                ->setParameter('fin', $filters['dateFin']);
        }

        // Je suis l'organisateur
        if (!empty($filters['organisateur']) && $participant) {
            $queryBuilder->andWhere('sortie.organisateur = :participant')
                ->setParameter('participant', $participant);
        }

        // Je suis inscrit
        if (!empty($filters['inscrit']) && $participant) {
            $queryBuilder->andWhere(':participant MEMBER OF sortie.participants')
                ->setParameter('participant', $participant);
        }

        // Je ne suis PAS inscrit
        if (!empty($filters['non_inscrit']) && $participant) {
            $queryBuilder->andWhere(':participant NOT MEMBER OF sortie.participants')
                ->setParameter('participant', $participant);
        }

        // État
        if (!empty($filters['etat'])) {
            $queryBuilder->andWhere('sortie.etat = :etat')
                ->setParameter('etat', $filters['etat']);
        }

        /// Sorties terminées ou à venir
        if (!empty($filters['terminees'])) {
            $queryBuilder->andWhere('sortie.etat = :etat')
                ->setParameter('etat', Etat::TERMINEE);
        } elseif (!empty($filters['etat'])) {
            $queryBuilder->andWhere('sortie.etat = :etat')
                ->setParameter('etat', $filters['etat']);
        } else {
            $queryBuilder->andWhere('sortie.etat != :etat')
                ->setParameter('etat', Etat::TERMINEE);
        }


        // Tri par date de début
        return $queryBuilder->orderBy('sortie.dateHeureDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
