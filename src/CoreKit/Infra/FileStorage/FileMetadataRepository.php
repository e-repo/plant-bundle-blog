<?php

declare(strict_types=1);

namespace CoreKit\Infra\FileStorage;

use CoreKit\Domain\Entity\FileMetadata;
use CoreKit\Domain\Repository\FileMetadataRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FileMetadata>
 *
 * @method FileMetadata|null find($id, $lockMode = null, $lockVersion = null)
 * @method FileMetadata|null findOneBy(array $criteria, array $orderBy = null)
 * @method FileMetadata[]    findAll()
 * @method FileMetadata[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FileMetadataRepository extends ServiceEntityRepository implements FileMetadataRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FileMetadata::class);
    }

    public function add(FileMetadata $entity): void
    {
        $this->getEntityManager()->persist($entity);
    }

    public function remove(FileMetadata $entity): void
    {
        $this->getEntityManager()->remove($entity);
    }

    public function findByFileKey($key): ?FileMetadata
    {
        return $this->find($key);
    }
}
