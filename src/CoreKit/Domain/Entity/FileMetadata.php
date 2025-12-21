<?php

declare(strict_types=1);

namespace CoreKit\Domain\Entity;

use CoreKit\Infra\FileStorage\FileMetadataRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FileMetadataRepository::class)]
class FileMetadata
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', options: [
        'comment' => 'Код',
    ])]
    private string $key;

    #[ORM\Column(length: 255, options: [
        'comment' => 'Оригинальное наименование файла',
    ])]
    private string $name;

    #[ORM\Column(length: 50, options: [
        'comment' => 'Тип файла',
    ])]
    private string $type;

    #[ORM\Column(length: 20, options: [
        'comment' => 'Расширение файла',
    ])]
    private string $extension;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    public function __construct(
        string $key,
        string $name,
        string $type,
        string $extension,
        DateTimeImmutable $createdAt
    ) {
        $this->key = $key;
        $this->name = $name;
        $this->type = $type;
        $this->extension = $extension;
        $this->createdAt = $createdAt;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
