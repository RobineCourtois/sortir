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
    public function findFiltered(Participant $participant, array $filters, bool $isAdmin = false): array
    {
        $qb = $this->createQueryBuilder('sortie')
            ->leftJoin('sortie.organisateur', 'organisateur')
            ->leftJoin('sortie.participants', 'participants')
            ->addSelect('organisateur', 'participants');

        if ($isAdmin) {
            // Admin voit tout, sans aucune restriction ni filtre
            return $qb->orderBy('sortie.dateHeureDebut', 'ASC')
                ->getQuery()
                ->getResult();
        }

        // -- Partie filtres normale pour utilisateur non-admin --

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

        // Organisateur / inscrit / non inscrit
        $orConditions = [];

        if (!empty($filters['organisateur'])) {
            $orConditions[] = 'sortie.organisateur = :participant';
        }
        if (!empty($filters['inscrit'])) {
            $orConditions[] = ':participant MEMBER OF sortie.participants';
        }
        if (!empty($filters['non_inscrit'])) {
            $orConditions[] = ':participant NOT MEMBER OF sortie.participants';
        }
        if (!empty($orConditions)) {
            $qb->andWhere(implode(' OR ', $orConditions))
                ->setParameter('participant', $participant);
        }

        // États (Terminé, etc.)
        $etatConditions = [];
        if (!empty($filters['terminees'])) {
            $etatConditions[] = 'sortie.etat = :etat_terminee';
            $qb->setParameter('etat_terminee', Etat::TERMINEE);
        }
        if (!empty($filters['etat'])) {
            if (is_array($filters['etat'])) {
                $etatConditions[] = 'sortie.etat IN (:etats)';
                $qb->setParameter('etats', $filters['etat']);
            } else {
                $etatConditions[] = 'sortie.etat = :etat';
                $qb->setParameter('etat', $filters['etat']);
            }
        }
        if (!empty($etatConditions)) {
            $qb->andWhere(implode(' OR ', $etatConditions));
        }

        // Restriction sur la création : visible uniquement à l’organisateur
        $qb->andWhere('sortie.etat != :etat_creation OR sortie.organisateur = :participant')
            ->setParameter('etat_creation', Etat::EN_CREATION)
            ->setParameter('participant', $participant);

        return $qb->orderBy('sortie.dateHeureDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }
}