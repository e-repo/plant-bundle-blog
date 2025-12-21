<?php

declare(strict_types=1);

namespace Auth\Application\User\Command\RequestResetPassword;

use Auth\Domain\User\Repository\UserRepositoryInterface;
use CoreKit\Application\Bus\CommandHandlerInterface;
use DomainException;

final readonly class Handler implements CommandHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(Command $command): void
    {
        $user = $this->userRepository->findByEmail($command->email);

        if (null === $user) {
            throw new DomainException('Пользователь по указанному email не найден');
        }

        $user->requestResetPassword($command->registrationSource);
    }
}
