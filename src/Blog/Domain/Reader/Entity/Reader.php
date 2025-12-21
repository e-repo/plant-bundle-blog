<?php

declare(strict_types=1);

namespace Blog\Domain\Reader\Entity;

use Blog\Domain\Reader\Entity\Specification\SpecificationAggregator;
use CoreKit\Domain\Entity\Email;
use CoreKit\Domain\Entity\Id;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity]
#[ORM\Table(schema: 'blog')]
class Reader
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', options: [
        'comment' => 'Код читателя',
    ])]
    private Id $id;

    #[ORM\Embedded(NameVo::class)]
    private NameVo $name;

    #[ORM\Column(type: 'email', length: 100, unique: true)]
    private Email $email;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, options: [
        'comment' => 'Дата создания читателя',
    ])]
    private DateTimeImmutable $createdAt;

    public function __construct(
        ReaderDto $readerDto,
        SpecificationAggregator $specificationAggregator,
    ) {
        $this->id = null === $readerDto->id
            ? Id::next()
            : new Id($readerDto->id);

        $this->name = new NameVo(
            first: $readerDto->firstname,
            last: $readerDto->lastname
        );

        $this->email = new Email($readerDto->email);
        $this->createdAt = new DateTimeImmutable();

        $this->checkSpecifications($specificationAggregator);
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function changeEmail(Email $email): void
    {
        $this->email = $email;
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function changeName(NameVo $name): void
    {
        $this->name = $name;
    }

    public function getName(): NameVo
    {
        return $this->name;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function update(ReaderHashVo $hashVo): void
    {
        if ($hashVo->isEqual($this)) {
            return;
        }

        $this->changeName(
            new NameVo(
                first: $hashVo->firstname,
                last: $hashVo->lastname,
            )
        );

        if ($hashVo->isEmailNotEqual($this)) {
            $this->changeEmail(new Email($hashVo->email));
        }
    }

    public function makeReaderHash(): ReaderHashVo
    {
        return new ReaderHashVo(
            firstname: $this->getName()->first,
            lastname: $this->getName()->last,
            email: $this->getEmail()->value
        );
    }

    private function checkSpecifications(SpecificationAggregator $aggregator): void
    {
        if (! $aggregator->uniqueEmailSpecification->isSatisfiedBy($this)) {
            throw new DomainException('Пользователь блога с данным email уже существует.');
        }
    }
}
