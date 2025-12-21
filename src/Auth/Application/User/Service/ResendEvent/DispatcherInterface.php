<?php

declare(strict_types=1);

namespace Auth\Application\User\Service\ResendEvent;

interface DispatcherInterface
{
    public function dispatch(): void;
}
