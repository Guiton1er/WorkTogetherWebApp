<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use function Symfony\Component\Clock\now;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
    * @return Order[] Returns an array of Order objects
    */
    public function findActives($customer): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.customer = :val')
            ->setParameter('val', $customer)
            ->andWhere('o.startDate <= :now')
            ->setParameter('now', new \DateTime())
            ->andWhere('(o.endDate >= :now OR o.endDate IS NULL)')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult()
        ;
    }

    public function findActiveFromCollection($orders): ?Order
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.customer IN (:val)')
            ->setParameter('val', $orders)
            ->andWhere('o.startDate <= :now')
            ->setParameter('now', new \DateTime())
            ->andWhere('(o.endDate >= :now OR o.endDate IS NULL)')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
//    /**
//     * @return Order[] Returns an array of Order objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Order
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
