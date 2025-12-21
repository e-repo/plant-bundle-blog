<?php

declare(strict_types=1);

namespace Auth\Infra\User\Service\Security;

use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (! $user instanceof UserIdentity) {
            return;
        }

        if (! $user->isActive()) {
            throw new DisabledException('Аккаунт пользователя не активен.');
        }
    }

    public function checkPostAuth(UserInterface $user): void {}
}
