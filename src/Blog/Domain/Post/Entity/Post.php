<?php

declare(strict_types=1);

namespace Blog\Domain\Post\Entity;

use Blog\Domain\Post\Entity\Dto\ImageDto;
use Blog\Domain\Post\Entity\Dto\PostDto;
use Blog\Domain\Post\Entity\Event\MainImageAddedEvent;
use Blog\Domain\Post\Entity\Event\PostCreatedEvent;
use Blog\Domain\Post\Entity\Specification\Post\SpecificationAggregator;
use Blog\Infra\Post\Repository\PostRepository;
use CoreKit\Domain\Entity\EventRecordTrait;
use CoreKit\Domain\Entity\HasEventsInterface;
use CoreKit\Domain\Entity\Id;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\Table(schema: 'blog')]
class Post implements HasEventsInterface
{
    use EventRecordTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', options: [
        'comment' => 'Код поста',
    ])]
    private Id $id;

    #[ORM\Column(
        length: 255,
        unique: true,
        options: [
            'comment' => 'slug поста',
        ]
    )]
    private string $slug;

    #[ORM\Column(
        length: 255,
        unique: true,
        options: [
            'comment' => 'Заголовок',
        ]
    )]
    private string $title;

    #[ORM\Column(
        length: 100,
        unique: true,
        options: [
            'comment' => 'Сокращенный заголовок, для карточки поста',
        ]
    )]
    private string $shortTitle;

    #[ORM\Column(type: Types::TEXT, options: [
        'comment' => 'Содержание статьи',
    ])]
    private string $content;

    #[ORM\Column(length: 100, enumType: Status::class, options: [
        'comment' => 'статус поста',
    ])]
    private Status $status;

    #[ORM\Column(options: [
        'comment' => 'Доступность комментариев',
        'default' => false,
    ])]
    private bool $commentAvailable = false;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, options: [
        'comment' => 'Категория поста',
    ])]
    private Category $category;

    #[ORM\Embedded(Metadata::class)]
    private ?Metadata $meta;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\OneToMany(
        mappedBy: 'post',
        targetEntity: PostImage::class,
        cascade: ['persist', 'remove'],
        fetch: 'EAGER',
        orphanRemoval: true,
    )]
    private Collection $images;

    public function __construct(
        PostDto $postDto,
        Category $category,
        SpecificationAggregator $specificationAggregator,
    ) {
        $this->id = null === $postDto->id
            ? Id::next()
            : new Id($postDto->id);

        $this->slug = $postDto->slug;
        $this->title = $postDto->title;
        $this->shortTitle = $postDto->shortTitle;
        $this->content = $postDto->content->text;
        $this->status = $postDto->status;
        $this->category = $category;
        $this->commentAvailable = $postDto->commentAvailable;
        $this->createdAt = new $postDto->createdAt();

        if (null !== $postDto->meta) {
            $this->meta = new Metadata(
                keyword: $postDto->meta->keyword,
                description: $postDto->meta->description,
            );
        }

        $this->images = new ArrayCollection();
        $this->addImage($postDto->image);

        $this->checkSpecifications($specificationAggregator);
        $this->record($this->makePostCreatedEvent());
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getShortTitle(): string
    {
        return $this->shortTitle;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function getMeta(): ?Metadata
    {
        return $this->meta;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, PostImage>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(ImageDto $imageDto): self
    {
        $image = $this->makePostImage($imageDto);

        $imageByFileKey = $this->images->findFirst(
            static function ($key, PostImage $currentImage) use ($image) {
                return $image->getFileKey()->value === $currentImage->getFileKey()->value;
            }
        );

        if (null !== $imageByFileKey) {
            throw new DomainException(
                sprintf(
                    'Данное изображение уже было добавлено, fileKey - \'%s\'.',
                    $image->getFileKey()->value,
                )
            );
        }

        $this->images->add($image);

        if ($image->getType() === ImageType::MAIN) {
            $this->record(
                $this->makeMainImageAddedEvent($imageDto)
            );
        }

        return $this;
    }

    public function removeImage(PostImage $image): self
    {
        $mainImages = $this->images->filter(
            static fn (PostImage $image) => $image->getType() === ImageType::MAIN
        );

        if (count($mainImages) === 1) {
            $mainImage = $mainImages->first();

            if ($image->getFileKey() === $mainImage->getFileKey()) {
                throw new DomainException('Невозможно удалить последнее главное изображение поста.');
            }
        }

        // При удалении активного изображения меняем признак активности
        if (
            $image->isActive() &&
            $image->getType() === ImageType::MAIN
        ) {
            $mainImages->removeElement($image);

            $mainImages->first()->activate();
        }

        $this->images->removeElement($image);

        return $this;
    }

    private function makePostImage(ImageDto $image): PostImage
    {
        return new PostImage(
            post: $this,
            type: $image->type,
            fileKey: $image->fileKey,
            isActive: $this->isImageActivable($image),
            createdAt: $image->createdAt ?? new DateTimeImmutable(),
        );
    }

    private function isImageActivable(ImageDto $image): bool
    {
        if ($image->type !== ImageType::MAIN) {
            return true;
        }

        return $this->images
            ->filter(
                static fn (PostImage $image) => $image->getType() === ImageType::MAIN
            )
            ->isEmpty();
    }

    private function makePostCreatedEvent(): PostCreatedEvent
    {
        return new PostCreatedEvent(
            id: $this->id->value,
            slug: $this->slug,
            title: $this->title,
            status: $this->status,
            categoryId: $this->category->getId()->value,
            categoryName: $this->getCategory()->getName(),
            createdAt: $this->getCreatedAt(),
        );
    }

    private function makeMainImageAddedEvent(ImageDto $imageDto): MainImageAddedEvent
    {
        return new MainImageAddedEvent(
            postId: $this->getId(),
            type: $imageDto->type,
            fileKey: $imageDto->fileKey->value,
            extension: $imageDto->file->getExtension(),
            originalFileName: $imageDto->originalFileName,
        );
    }

    private function checkSpecifications(SpecificationAggregator $aggregator): void
    {
        if (! $aggregator->uniqueShortTitle->isSatisfiedBy($this)) {
            throw new DomainException('Данный сокращенный заголовок статьи уже существует.');
        }

        if (! $aggregator->uniqueTitle->isSatisfiedBy($this)) {
            throw new DomainException('Данный заголовок статьи уже существует.');
        }
    }
}
