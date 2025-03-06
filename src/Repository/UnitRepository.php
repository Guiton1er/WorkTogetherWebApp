<?php

namespace App\Repository;

use App\Entity\Unit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Unit>
 */
class UnitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Unit::class);
    }

    /**
    * @return Unit[] Returns an array of Unit objects
    */
    public function findAvailableUnits(): array
    {
        $entityManager = $this->getEntityManager();

        // Create the ResultSetMapping object
        $rsm = new ResultSetMappingBuilder($entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Entity\Unit', 'unit');

        // Create the native query with the mapping
        $query = $entityManager->createNativeQuery(
            'SELECT DISTINCT u.*
                FROM unit u
                WHERE u.id NOT IN (
                    SELECT uo.unit_id 
                    FROM unit_order uo
                    INNER JOIN `order` o
                    ON o.id = uo.order_id
                    WHERE o.start_date <= NOW()
                    AND o.end_date >= NOW()
                    OR o.end_date IS NULL
                );',
            $rsm
        );

        // Execute and get fully hydrated User entities
        $users = $query->getResult();
        return $users;
    }

    /**
    * @return Unit[] Returns an array of Unit objects
    */
    public function findByType($type, $customer): array
    {
        $entityManager = $this->getEntityManager();

        $query = 
        'SELECT COUNT(u.type_id)
        FROM unit u
        WHERE u.id IN
        (
            SELECT uo.unit_id 
            FROM unit_order uo
            INNER JOIN `order` o
            ON o.id = uo.order_id
            WHERE o.start_date <= NOW()
            AND o.end_date >= NOW() 
            OR o.end_date IS NULL
            AND o.customer_id = :customer
        )
        GROUP BY u.type_id;
        ';

        $stmt = $entityManager->getConnection()->prepare($query);
        $stmt->bindValue('type', $type->getId());
        $stmt->bindValue('customer', $customer->getId());

        return $stmt->executeQuery()->fetchAll();
    }

    /**
    * @return Unit[] Returns an array of Unit objects
    */
    public function findByState($state, $customer): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT u
            FROM App\Entity\Unit u
            WHERE u.state = :state
            AND u.orders IN
            (
                SELECT o 
                FROM App\Entity\Order o
                WHERE o.startDate <= :now
                AND o.endDate >= :now
                AND o.customer = :customer
            )'
        )
        ->setParameter('state', $state)
        ->setParameter('now', new \DateTime())
        ->setParameter('customer', $customer);
        
        // returns an array of Product objects
        return $query->getResult();
    }

//    public function findOneBySomeField($value): ?Unit
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
