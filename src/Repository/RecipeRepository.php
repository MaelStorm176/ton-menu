<?php

namespace App\Repository;

use App\Entity\Recipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Recipe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Recipe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Recipe[]    findAll()
 * @method Recipe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recipe::class);
    }

    // /**
    //  * @return Recipe[] Returns an array of Recipe objects
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
    public function findOneBySomeField($value): ?Recipe
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getRandomRecipes($type,$max=1,$notIn=[])
    {
        if (count($notIn)>0) {
            $qb = $this->createQueryBuilder('r')
                ->where('r.type = :type')
                ->andWhere('r.id NOT IN (:notIn)')
                ->setParameter('type', $type)
                ->setParameter('notIn', $notIn)
                ->orderBy('r.id', 'DESC')
                ->setMaxResults($max)
                ->getQuery();
        } else {
            $qb = $this->createQueryBuilder('r')
                ->where('r.type = :type')
                ->setParameter('type', $type)
                ->orderBy('r.id', 'DESC')
                ->setMaxResults($max)
                ->getQuery();
        }

        return $qb->getResult();
    }

    public function countRecipe()
    {
        return $this->createQueryBuilder('t')
            ->select('count(t.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countEntreeRecipe()
    {
        return $this->createQueryBuilder('t')
            ->select('t.id')
            ->andWhere('t.type = :val')
            ->setParameter('val', 'ENTREE')
            ->getQuery()
            ->getScalarResult();
    }

    public function countPlatRecipe()
    {
        return $this->createQueryBuilder('t')
            ->select('t.id')
            ->andWhere('t.type = :val')
            ->setParameter('val', 'PLAT')
            ->getQuery()
            ->getScalarResult();
    }

    public function countDessertRecipe()
    {
        return $this->createQueryBuilder('t')
            ->select('t.id')
            ->andWhere('t.type = :val')
            ->setParameter('val', 'DESSERT')
            ->getQuery()
            ->getScalarResult();
    }

    public function findRecipeByIdAndCountEachRecipe($value)
    {
        return $this->createQueryBuilder('t')
            ->select('t.name, count(t.id)')
            ->andWhere('r.id = :val')
            ->setParameter('val', $value)
            ->groupBy('t.id')
            ->getQuery()
            ->getScalarResult();
    }
}
