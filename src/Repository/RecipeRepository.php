<?php

namespace App\Repository;

use App\Entity\Recipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
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

    public function getRandomRecipes($type, $max = 1, $notIn = [])
    {
        if (count($notIn) > 0) {
            $qb = $this->createQueryBuilder('r')
                ->where('r.type = :type')
                ->andWhere('r.id NOT IN (:notIn)')
                ->setParameter('type', $type)
                ->setParameter('notIn', $notIn)
                ->orderBy('RAND()')
                ->setMaxResults($max)
                ->getQuery();
        } else {
            $qb = $this->createQueryBuilder('r')
                ->where('r.type = :type')
                ->setParameter('type', $type)
                ->orderBy('RAND()')
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

    public function findByName($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.name like :query')
            ->setParameter('query', "%" . $value . "%")
            // ->orderBy('c.id', 'ASC')
            // ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function findBestRatedRecipesMadeByAUser($userId)
    {
        return $this->createQueryBuilder('r')
            ->innerJoin('r.ratings', 'r2', 'WITH', 'r2.recette = r.id')
            ->where('r.user_id = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('r2.rate', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    }

    public function findWorstRatedRecipesMadeByAUser($userId)
    {
        return $this->createQueryBuilder('r')
            ->innerJoin('r.ratings', 'r2', 'WITH', 'r2.recette = r.id')
            ->where('r.user_id = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('r2.rate', 'ASC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    }

    public function getBySearchQueryBuilder($filters)
    {
        $qb = $this->createQueryBuilder('r');
        if (isset($filters['type']) && !empty($filters['type'])) {
            $qb->andWhere('r.type IN (:type)')->setParameter('type', $filters['type']);
        }
        if (isset($filters['name'])) {
            $qb->andWhere('r.name like :name')->setParameter('name', "%" . $filters['name'] . "%");
        }
        if (isset($filters['difficulty']) && !empty($filters['difficulty'])) {
            $qb->andWhere('r.difficulty IN (:difficulty)')->setParameter('difficulty', $filters['difficulty']);
        }
        if (isset($filters['ingredients']) && !$filters['ingredients']->isEmpty()) {
            $qb->innerJoin('r.recipeTags', 'i', 'WITH', 'i.id IN (:ingredients)')
                ->groupBy('r.id')
                ->having('COUNT(i.id) = :count')
                ->setParameter('ingredients', $filters['ingredients'])
                ->setParameter('count', count($filters['ingredients']));
        }

        if (isset($filters['tags']) && !$filters['tags']->isEmpty()) {
            $qb->innerJoin('r.recipeTags', 't', 'WITH', 't.id IN (:tags)')
                ->groupBy('r.id')
                ->having('COUNT(t.id) = :count')
                ->setParameter('tags', $filters['tags'])
                ->setParameter('count', count($filters['tags']));
        }
        if (isset($filters['user'])) {
            $qb->andWhere('r.user_id = :user')->setParameter('user', $filters['user']);
        }
        if (isset($filters['minRate'])) {
            $qb->innerJoin('r.ratings', 'r2', 'WITH', 'r2.recette = r.id')
                ->andWhere('r2.rate >= :minRate')
                ->setParameter('minRate', $filters['minRate']);
        }
        if (isset($filters['maxRate'])) {
            $qb->innerJoin('r.ratings', 'r2', 'WITH', 'r2.recette = r.id')
                ->andWhere('r2.rate <= :maxRate')
                ->setParameter('maxRate', $filters['maxRate']);
        }
        if (isset($filters['maxDuration'])){
            switch ($filters['maxDuration']){
                case 1:
                    $qb->andWhere('r.preparation_time <= :maxDuration')->setParameter('maxDuration', new \DateTimeImmutable("01:00:00"));
                    break;
                case 2:
                    $qb->andWhere('r.preparation_time BETWEEN :maxDuration1 AND :maxDuration2')
                        ->setParameter('maxDuration1', new \DateTimeImmutable("01:00:00"))
                        ->setParameter('maxDuration2', new \DateTimeImmutable("02:00:00"));
                    break;
                case 3:
                    $qb->andWhere('r.preparation_time <= :maxDuration')->setParameter('maxDuration', new \DateTimeImmutable("00:30:00"));
                    break;
                default:
                    break;
            }
        }
        if (isset($filters['budget']) && !empty($filters['budget'])) {
            $qb->andWhere('r.budget IN (:budget)')->setParameter('budget', $filters['budget']);
        }
        if (isset($filters['number_of_persons']) && !empty($filters['number_of_persons'])) {
            $qb->andWhere('r.number_of_persons = :number_of_persons')->setParameter('number_of_persons', $filters['number_of_persons']);
        }
        if (isset($filters['orderBy'])) {
            $qb->orderBy('r.' . $filters['orderBy'], 'ASC');
        }else{
            $qb->orderBy('r.created_at', 'DESC')
                ->addOrderBy('r.name', 'ASC');
        }
        if (isset($filters['limit'])) {
            $qb->setMaxResults($filters['limit']);
        }
        //dd($qb->getQuery()->getDQL(), $qb->getParameters());

        return $qb;
    }

    public function findBySearch($filters)
    {
        $qb = $this->getBySearchQueryBuilder($filters);;
        return $qb->getQuery()->getResult();
    }

    public function findByIngredients($ingredients)
    {
        $qb = $this->createQueryBuilder('r');
        $qb->innerJoin('r.ingredients', 'i', 'WITH', 'i.id IN (:ingredients)')
            ->groupBy('r.id')
            ->having('COUNT(i.id) = :count')
            ->setParameters([
                'ingredients' => $ingredients,
                'count' => count($ingredients)
            ]);
        return $qb->getQuery()->getResult();
    }

    // Find/search articles by title/content
    public function findArticlesByName(string $query)
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->orX(
                        $qb->expr()->like('p.name', ':query'),
                    ),
                )
            )
            ->setParameter('query', '%' . $query . '%');
        return $qb
            ->getQuery()
            ->getResult();
    }
}
