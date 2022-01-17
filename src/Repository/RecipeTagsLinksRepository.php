<?php

namespace App\Repository;

use App\Entity\RecipeTagsLinks;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RecipeTagsLinks|null find($id, $lockMode = null, $lockVersion = null)
 * @method RecipeTagsLinks|null findOneBy(array $criteria, array $orderBy = null)
 * @method RecipeTagsLinks[]    findAll()
 * @method RecipeTagsLinks[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipeTagsLinksRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RecipeTagsLinks::class);
    }

    // /**
    //  * @return RecipeTagsLinks[] Returns an array of RecipeTagsLinks objects
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
    public function findOneBySomeField($value): ?RecipeTagsLinks
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
