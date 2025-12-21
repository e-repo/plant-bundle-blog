<?php

declare(strict_types=1);

namespace Auth\Application\User\Listener;

use Auth\Application\User\Event\UserCreatedOrUpdatedEvent;
use Auth\Domain\User\Entity\Event\UserStatusChangedEvent;
use CoreKit\Application\Bus\EventBusInterface;
use CoreKit\Application\Bus\EventListenerInterface;

final readonly class UserStatusChangedListener implements EventListenerInterface
{
    public function __construct(
        private EventBusInterface $eventBus,
    ) {}

    public function __invoke(UserStatusChangedEvent $event): void
    {
        $this->eventBus->publish(
            new UserCreatedOrUpdatedEvent(
                id: $event->getId(),
                firstname: $event->getFirstname(),
                lastname: $event->getLastname(),
                email: $event->getEmail(),
                status: $event->getStatus(),
                role: $event->getRole(),
            )
        );
    }
}
