<?php

declare(strict_types=1);

namespace Test\Integration\Common\Listener;

use CoreKit\Application\Bus\EventListenerInterface;

class MockUserCreatedOrUpdatedListener implements EventListenerInterface
{
    public function __construct() {}

    public function __invoke() {}
}
