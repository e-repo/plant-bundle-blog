<?php

declare(strict_types=1);

namespace Auth\UI\Cli\User;

use Auth\Application\User\Command\ResendCreatedOrUpdatedEvent\Command as ResendEventCommand;
use CoreKit\Application\Bus\CommandBusInterface;
use CoreKit\Application\Event\UserCreatedOrUpdatedEventInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:resend:user-created-event',
    description: 'Повторная отправка события создания/обновления пользователя.'
)]
class ResendCreatedOrUpdatedEventCommand extends Command
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->info('Старт отправки события создания/обновления по всем пользователям.');

        $this->commandBus->dispatch(
            new ResendEventCommand(
                eventName: UserCreatedOrUpdatedEventInterface::class
            )
        );

        $io->info('Отправка событий завершена успешно.');

        return Command::SUCCESS;
    }
}
