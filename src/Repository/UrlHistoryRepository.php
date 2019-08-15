<?php

namespace App\Repository;

use App\Entity\UrlHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UrlHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method UrlHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method UrlHistory[]    findAll()
 * @method UrlHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UrlHistoryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UrlHistory::class);
    }

    // /**
    //  * @return UrlHistory[] Returns an array of UrlHistory objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UrlHistory
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
