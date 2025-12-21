<?php

declare(strict_types=1);

namespace Auth\Application\User\Listener;

use Auth\Infra\User\Service\Security\UserIdentity;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

final class JWTCreatedListener
{
    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        /** @var UserIdentity $user */
        $user = $event->getUser();
        $payload = $event->getData();

        $payload['user']['id'] = $user->id;
        $payload['user']['firstName'] = urlencode($user->firstName);
        $payload['user']['email'] = $user->email;
        $payload['user']['roles'] = $payload['roles'];

        unset($payload['roles']);

        $event->setData($payload);
    }
}
