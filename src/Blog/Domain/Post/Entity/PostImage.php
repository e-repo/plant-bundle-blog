<?php

declare(strict_types=1);

namespace Blog\Domain\Post\Entity;

use Blog\Domain\Post\Repository\PostImageRepository;
use CoreKit\Domain\Entity\Id;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostImageRepository::class)]
#[ORM\Table(schema: 'blog')]
class PostImage
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', options: [
        'comment' => 'Идентификатор изображения',
    ])]
    private Id $id;

    #[ORM\ManyToOne(
        inversedBy: 'images',
    )]
    #[ORM\JoinColumn(name: 'post_id', nullable: false)]
    private Post $post;

    #[ORM\Column(type: 'uuid', unique: true)]
    private Id $fileKey;

    #[ORM\Column(
        length: 50,
        enumType: ImageType::class,
        options: [
            'comment' => 'Тип файла',
        ]
    )]
    private ImageType $type;

    #[ORM\Column(type: Types::BOOLEAN, options: [
        'comment' => 'Признак активного изображения',
    ])]
    private bool $isActive;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, options: [
        'comment' => 'Дата создания',
    ])]
    private DateTimeImmutable $createdAt;

    public function __construct(
        Post $post,
        ImageType $type,
        Id $fileKey,
        Id $id = null,
        bool $isActive = false,
        ?DateTimeImmutable $createdAt = null,
    ) {
        $this->id = $id ?? Id::next();

        $this->post = $post;
        $this->type = $type;
        $this->fileKey = $fileKey;
        $this->isActive = $isActive;
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getPost(): Post
    {
        return $this->post;
    }

    public function getFileKey(): Id
    {
        return $this->fileKey;
    }

    public function getType(): ImageType
    {
        return $this->type;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function activate(): void
    {
        $this->isActive = true;
    }
}
