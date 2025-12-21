<?php

declare(strict_types=1);

namespace Test\Integration\Auth\Cli\User\ResendCreatedOrUpdatedEvent;

use Blog\Application\Reader\Listener\UserCreatedOrUpdatedListener;
use CoreKit\Application\Event\UserCreatedOrUpdatedEventInterface;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Component\Console\Tester\CommandTester;
use Test\Common\FunctionalTestCase;
use Test\Integration\Common\Listener\MockUserCreatedOrUpdatedListener;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

final class ResendTest extends FunctionalTestCase
{
    use InteractsWithMessenger;

    private const COMMAND_NAME = 'app:resend:user-created-event';

    public function setUp(): void
    {
        parent::setUp();

        // arrange
        $this->databaseTool
            ->withPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE)
            ->loadFixtures([
                UserFixture::class,
            ]);

        // Убираем создание Reader - тестируется отдельно
        $userCreatedOrUpdatedListener = $this->createMock(
            MockUserCreatedOrUpdatedListener::class
        );
        $this->container->set(UserCreatedOrUpdatedListener::class, $userCreatedOrUpdatedListener);
    }

    public function testSuccessResend(): void
    {
        // arrange
        $eventBus = $this->bus('event.bus');
        $command = $this->application->find(self::COMMAND_NAME);
        $commandTester = new CommandTester($command);

        $expectedUsers = [
            'f472d1a5-ba78-4039-94e3-ae0161256eaf',
            '125d7cd7-0b77-409a-bc5c-19b44416a5fa',
            '76c3a2d9-49fd-4fbd-a0f4-0022d38dbaba',
        ];

        // action
        $commandTester->execute([]);

        // assert
        $commandTester->assertCommandIsSuccessful();

        /** @var UserCreatedOrUpdatedEventInterface $message */
        foreach ($eventBus->dispatched()->messages() as $message) {
            self::assertContains($message->getId(), $expectedUsers);
        }
    }
}
