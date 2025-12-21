<?php

declare(strict_types=1);

namespace Auth\Application\User\Command\ConfirmEmail;

use Auth\Domain\User\Repository\UserRepositoryInterface;
use CoreKit\Application\Bus\CommandHandlerInterface;
use CoreKit\Domain\Entity\Id;
use DomainException;

final readonly class Handler implements CommandHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(Command $command): void
    {
        $user = $this->userRepository->find(new Id($command->userId));

        if (null === $user) {
            throw new DomainException(
                sprintf('Пользователь с идентификатором %s не найден.', $command->userId)
            );
        }

        $user->confirmUserEmail($command->token);
    }
}
