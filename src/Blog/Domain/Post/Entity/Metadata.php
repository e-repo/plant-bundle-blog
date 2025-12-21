<?php

declare(strict_types=1);

namespace Blog\Domain\Post\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final readonly class Metadata
{
    public function __construct(
        #[ORM\Column(
            length: 255,
            nullable: true
        )]
        public ?string $keyword = null,
        #[ORM\Column(
            length: 255,
            nullable: true
        )]
        public ?string $description = null,
    ) {}
}
