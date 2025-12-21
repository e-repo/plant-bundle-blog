<?php

declare(strict_types=1);

namespace Auth\Infra\User\Service\ResendEvent;

use Auth\Application\User\Service\ResendEvent\DispatcherInterface;
use Auth\Application\User\Service\ResendEvent\ResendEventFactoryInterface;
use CoreKit\Application\Event\UserCreatedOrUpdatedEventInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

final class ResendEventFactory implements ResendEventFactoryInterface, ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $locator,
    ) {}

    public static function getSubscribedServices(): array
    {
        return [
            UserCreatedOrUpdatedEventInterface::class => UserCreatedOrUpdatedDispatcher::class,
        ];
    }

    public function create(string $eventName): DispatcherInterface
    {
        try {
            return $this->locator->get($eventName);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface) {
            throw new RuntimeException(
                sprintf(
                    'Сервис для повторной отправки события %s не найден.',
                    $eventName
                )
            );
        }
    }
}
