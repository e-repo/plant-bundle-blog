<?php

declare(strict_types=1);

namespace Blog\Domain\Reader\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final class NameVo
{
    public function __construct(
        #[ORM\Column(length: 255, options: [
            'comment' => 'Имя',
        ])]
        public string $first,
        #[ORM\Column(length: 255, nullable: true, options: [
            'comment' => 'Фамилия',
        ])]
        public ?string $last = null,
    ) {}

    public function fullName(): string
    {
        return sprintf('%s %s', $this->last, $this->first);
    }
}
