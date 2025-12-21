<?php

declare(strict_types=1);

namespace Blog\Infra\Post\Repository;

use Blog\Domain\Post\Entity\Category;
use Blog\Domain\Post\Repository\CategoryRepositoryInterface;
use CoreKit\Domain\Entity\Id;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository implements CategoryRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function add(Category $category): void
    {
        $this->_em->persist($category);
    }

    public function findByName(string $name): ?Category
    {
        return $this->findOneBy([
            'name' => $name,
        ]);
    }

    public function findById(string $id): ?Category
    {
        return $this->findOneBy([
            'id' => new Id($id),
        ]);
    }
}
