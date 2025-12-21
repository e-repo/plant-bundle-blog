<?php

declare(strict_types=1);

namespace Auth\Infra\User\Service\ResendEvent;

use Auth\Application\User\Event\UserCreatedOrUpdatedEvent;
use Auth\Application\User\Service\ResendEvent\DispatcherInterface;
use Auth\Domain\User\Repository\UserRepositoryInterface;
use CoreKit\Application\Bus\EventBusInterface;

final readonly class UserCreatedOrUpdatedDispatcher implements DispatcherInterface
{
    public function __construct(
        private EventBusInterface $eventBus,
        private UserRepositoryInterface $userRepository,
    ) {}

    public function dispatch(): void
    {
        foreach ($this->userRepository->getIterator() as $user) {
            $this->eventBus->publish(
                new UserCreatedOrUpdatedEvent(
                    id: $user->getId()->value,
                    firstname: $user->getName()->first,
                    lastname: $user->getName()->last,
                    email: $user->getEmail()->value,
                    status: $user->getStatus()->value,
                    role: $user->getRole()->value,
                )
            );
        }
    }
}
