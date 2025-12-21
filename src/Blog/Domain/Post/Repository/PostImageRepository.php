<?php

declare(strict_types=1);

namespace Blog\Domain\Post\Repository;

use Blog\Domain\Post\Entity\PostImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PostImage>
 *
 * @method PostImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostImage[]    findAll()
 * @method PostImage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostImage::class);
    }
}
