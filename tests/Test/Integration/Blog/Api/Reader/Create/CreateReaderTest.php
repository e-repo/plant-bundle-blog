<?php

declare(strict_types=1);

namespace Test\Integration\Blog\Api\Reader\Create;

use Blog\Application\Reader\Command\CreateOrUpdate\Command;
use Blog\Domain\Reader\Entity\Reader;
use CoreKit\Domain\Entity\Id;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Test\Common\DataFromJsonResponseTrait;
use Test\Common\FunctionalTestCase;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

final class CreateReaderTest extends FunctionalTestCase
{
    use DataFromJsonResponseTrait;
    use InteractsWithMessenger;

    private ObjectRepository $readerRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->databaseTool
            ->withPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE)
            ->loadFixtures([
                ReaderFixture::class,
            ]);

        $this->readerRepository = $this->entityManager->getRepository(Reader::class);
    }

    public function testSuccessCreate(): void
    {
        // arrange
        $command = new Command(
            id: Id::next()->value,
            firstname: 'Антон',
            lastname: 'Чехов',
            email: 'test@test.ru',
        );

        $commandBus = $this->bus('command.bus');

        // action
        $commandBus->dispatch($command);

        // assert
        /** @var Reader $reader */
        $reader = $this->readerRepository->findOneBy([
            'id' => $command->id,
        ]);

        self::assertNotNull($reader);
        self::assertEquals($command->id, $reader->getId());
        self::assertEquals($command->firstname, $reader->getName()->first);
        self::assertEquals($command->lastname, $reader->getName()->last);
        self::assertEquals($command->email, $reader->getEmail()->value);
    }

    public function testSuccessUpdate(): void
    {
        // arrange
        $command = new Command(
            id: 'f472d1a5-ba78-4039-94e3-ae0161256eaf',
            firstname: 'Антон',
            lastname: 'Чехов',
            email: 'test@test.ru',
        );

        $commandBus = $this->bus('command.bus');

        // action
        $commandBus->dispatch($command);

        // assert
        /** @var Reader $reader */
        $reader = $this->readerRepository->findOneBy([
            'id' => $command->id,
        ]);

        self::assertNotNull($reader);
        self::assertEquals($command->id, $reader->getId());
        self::assertEquals($command->firstname, $reader->getName()->first);
        self::assertEquals($command->lastname, $reader->getName()->last);
        self::assertEquals($command->email, $reader->getEmail()->value);
    }

    public function testFailedCreate(): void
    {
        // arrange
        $command = new Command(
            id: Id::next()->value,
            firstname: 'Антон',
            lastname: 'Чехов',
            email: 'test_1@test.ru',
        );

        $commandBus = $this->bus('command.bus');

        // action
        $previous = null;

        try {
            $commandBus->dispatch($command);
        } catch (HandlerFailedException $exception) {
            $previous = $exception->getPrevious();
        }

        // assert
        self::assertNull(
            $this->readerRepository->findOneBy([
                'id' => $command->id,
            ])
        );
        self::assertEquals('Пользователь блога с данным email уже существует.', $previous?->getMessage());
    }
}
