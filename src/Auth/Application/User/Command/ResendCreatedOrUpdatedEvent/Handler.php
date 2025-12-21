<?php

declare(strict_types=1);

namespace Auth\Application\User\Command\ResendCreatedOrUpdatedEvent;

use Auth\Application\User\Service\ResendEvent\ResendEventFactoryInterface;
use CoreKit\Application\Bus\CommandHandlerInterface;

final readonly class Handler implements CommandHandlerInterface
{
    public function __construct(
        private ResendEventFactoryInterface $resendEventFactory
    ) {}

    public function __invoke(Command $command): void
    {
        $this->resendEventFactory
            ->create($command->eventName)
            ->dispatch();
    }
}
