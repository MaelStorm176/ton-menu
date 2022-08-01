<?php

namespace App\Repository;

use App\Entity\RecipeIngredients;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RecipeIngredients|null find($id, $lockMode = null, $lockVersion = null)
 * @method RecipeIngredients|null findOneBy(array $criteria, array $orderBy = null)
 * @method RecipeIngredients[]    findAll()
 * @method RecipeIngredients[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipeIngredientsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RecipeIngredients::class);
    }

    // /**
    //  * @return RecipeIngredients[] Returns an array of RecipeIngredients objects
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
    public function findOneBySomeField($value): ?RecipeIngredients
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findRecipeByIngredientArray($ingredientArray)
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.ingredient IN (:ingredientArray)')
            ->setParameter('ingredientArray', $ingredientArray)
            ->orderBy('RAND()')
            //having
            ->groupBy('r.recipe')
            ->having('COUNT(r.recipe) > 2')
            ->setMaxResults(3)
            ->getQuery();

        return $qb->getResult();
    }
}
