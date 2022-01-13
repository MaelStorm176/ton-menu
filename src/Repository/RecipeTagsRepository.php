<?php

namespace App\Repository;

use App\Entity\RecipeTags;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RecipeTags|null find($id, $lockMode = null, $lockVersion = null)
 * @method RecipeTags|null findOneBy(array $criteria, array $orderBy = null)
 * @method RecipeTags[]    findAll()
 * @method RecipeTags[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipeTagsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RecipeTags::class);
    }

    // /**
    //  * @return RecipeTags[] Returns an array of RecipeTags objects
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
    public function findOneBySomeField($value): ?RecipeTags
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
