<?php

namespace App\Repository;

use App\Entity\Wisdom;
use Symfony\Component\Uid\Uuid;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Wisdom>
 */
class WisdomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wisdom::class);
    }

    /**
     * @return Wisdom[]
     */
    public function findAllOrderedById(): array
    {
        return $this->createQueryBuilder('w')
            ->orderBy('w.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Uuid[]
     */
    public function getAllIds(): array
    {
        return array_map(
            fn(string $id) => Uuid::fromString($id),
            array_column(
                $this->createQueryBuilder('w')
                    ->select('w.id')
                    ->getQuery()
                    ->getArrayResult(),
                'id'
            )
        );
    }

    //    /**
    //     * @return Wisdom[] Returns an array of Wisdom objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('w.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Wisdom
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
