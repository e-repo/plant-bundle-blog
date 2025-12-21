<?php

declare(strict_types=1);

namespace Auth\Application\User\Command\SignUp;

use Auth\Domain\User\Entity\NameVo;
use Auth\Domain\User\Entity\Specification\UniqueEmailSpecification;
use Auth\Domain\User\Entity\User;
use Auth\Domain\User\Repository\UserRepositoryInterface;
use Auth\Domain\User\Service\PasswordHasher\Hasher;
use CoreKit\Application\Bus\CommandHandlerInterface;
use CoreKit\Domain\Entity\Email;
use CoreKit\Domain\Entity\Id;

final readonly class Handler implements CommandHandlerInterface
{
    public function __construct(
        private Hasher $hasher,
        private UserRepositoryInterface $userRepository,
        private UniqueEmailSpecification $uniqueEmailSpecification,
    ) {}

    public function __invoke(Command $command): void
    {
        $emailVo = new Email($command->email);

        $user = new User(
            id: Id::next(),
            name: new NameVo($command->firstName),
            email: $emailVo,
            password: $command->password,
            registrationSource: $command->registrationSource,
            uniqueEmailSpecification: $this->uniqueEmailSpecification,
            hasher: $this->hasher,
        );

        $this->userRepository->add($user);
    }
}
