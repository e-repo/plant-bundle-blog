<?php

declare(strict_types=1);

namespace Blog\Infra\Post\Repository;

use Blog\Domain\Post\Entity\Post;
use Blog\Domain\Post\Repository\PostRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 *
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository implements PostRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function add(Post $post): void
    {
        $this->_em->persist($post);
    }

    public function findByUuid(string $uuid): ?Post
    {
        return $this->find($uuid);
    }

    public function findBySlug(string $slug): ?Post
    {
        return $this->findOneBy([
            'slug' => $slug,
        ]);
    }

    public function findByTitle(string $title): ?Post
    {
        return $this->findOneBy([
            'title' => $title,
        ]);
    }

    public function findByShortTitle(string $shortTitle): ?Post
    {
        return $this->findOneBy([
            'shortTitle' => $shortTitle,
        ]);
    }
}
