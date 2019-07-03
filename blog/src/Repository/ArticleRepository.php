<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * @return mixed
     */
    public function findAllWithCategoriesAndTags()
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.category', 'c' )
            ->leftjoin('a.tags', 't')
            ->addSelect('c, t')
            ->orderBy('a.id', 'DESC')
            ->getQuery();

        return $qb->execute();
    }

    /**
     * @return mixed
     */
    public function findAllWithCategoriesTagsAndAuthor()
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.category', 'c' )
            ->leftJoin('a.tags', 't')
            ->leftJoin('a.author', 'u')
            ->addSelect('c, t, u')
            ->orderBy('a.id', 'DESC')
            ->getQuery();

        return $qb->execute();
    }
}
