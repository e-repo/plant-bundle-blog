<?php

declare(strict_types=1);

namespace Blog\Infra\Reader\Repository;

use Blog\Domain\Reader\Entity\Reader;
use Blog\Domain\Reader\Repository\ReaderRepositoryInterface;
use CoreKit\Domain\Entity\Email;
use CoreKit\Domain\Entity\Id;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reader>
 *
 * @method Reader|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reader|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reader[]    findAll()
 * @method Reader[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class ReaderRepository extends ServiceEntityRepository implements ReaderRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reader::class);
    }

    public function add(Reader $reader): void
    {
        $this->_em->persist($reader);
    }

    public function findByEmail(string $email): ?Reader
    {
        return $this->findOneBy([
            'email' => new Email($email),
        ]);
    }

    public function findById(string $id): ?Reader
    {
        return $this->findOneBy([
            'id' => new Id($id),
        ]);
    }
}
