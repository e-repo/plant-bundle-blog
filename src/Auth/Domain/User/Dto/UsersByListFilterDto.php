<?php

declare(strict_types=1);

namespace Auth\Domain\User\Dto;

final readonly class UsersByListFilterDto
{
    /**
     * @param UserProfileDto[] $userList
     */
    public function __construct(
        public array $userList,
        public int $total
    ) {}
}
