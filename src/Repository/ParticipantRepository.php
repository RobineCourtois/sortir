<?php

namespace App\Repository;

use App\Entity\Campus;
use App\Entity\Participant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Participant>
 */
class ParticipantRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participant::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Participant) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findBySearch(string $search = '', Campus $campus = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.campus', 'c')
            ->addSelect('c');

        if (!empty($search)) {
            $qb->andWhere('
            LOWER(p.nom) LIKE :search 
            OR LOWER(p.prenom) LIKE :search 
            OR LOWER(p.pseudo) LIKE :search 
            OR LOWER(p.email) LIKE :search 
            OR LOWER(p.telephone) LIKE :search
            OR LOWER(c.nom) LIKE :search
        ')
                ->setParameter('search', '%' . strtolower($search) . '%');
        }

        if ($campus) {
            $qb->andWhere('p.campus = :campus')
                ->setParameter('campus', $campus);
        }

        return $qb->orderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }


}