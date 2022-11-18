<?php

namespace App\Repository;

use App\Entity\RecipeSteps;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RecipeSteps|null find($id, $lockMode = null, $lockVersion = null)
 * @method RecipeSteps|null findOneBy(array $criteria, array $orderBy = null)
 * @method RecipeSteps[]    findAll()
 * @method RecipeSteps[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipeStepsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RecipeSteps::class);
    }

    // /**
    //  * @return RecipeSteps[] Returns an array of RecipeSteps objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RecipeSteps
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findAllById($value): ?RecipeSteps
    {
        return $this->createQueryBuilder('t')
            ->select('t.step')
            ->andWhere('t.id = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult();
    }
}
