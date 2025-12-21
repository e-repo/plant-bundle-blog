<?php

declare(strict_types=1);

namespace Blog\Application\Reader\Listener;

use Blog\Application\Reader\Command\CreateOrUpdate\Command;
use CoreKit\Application\Bus\CommandBusInterface;
use CoreKit\Application\Bus\EventListenerInterface;
use CoreKit\Application\Event\UserCreatedOrUpdatedEventInterface;

final readonly class UserCreatedOrUpdatedListener implements EventListenerInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {}

    public function __invoke(UserCreatedOrUpdatedEventInterface $event): void
    {
        $this->commandBus->dispatch(
            new Command(
                id: $event->getId(),
                firstname: $event->getFirstname(),
                lastname: $event->geLastname(),
                email: $event->getEmail(),
            )
        );
    }
}
