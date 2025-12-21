<?php

declare(strict_types=1);

namespace Auth\Application\User\Listener;

use Auth\Domain\User\Entity\Event\UserPasswordResetEvent;
use CoreKit\Application\Bus\EventListenerInterface;
use DomainException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

final readonly class UserPasswordResetListener implements EventListenerInterface
{
    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig,
        private string $appEmail,
        private array $registrationSources,
    ) {}

    /**
     * @param UserPasswordResetEvent $event
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws TransportExceptionInterface
     */
    public function __invoke(UserPasswordResetEvent $event): void
    {
        $mail = $this->makePasswordResetMessage($event);
        $this->mailer->send($mail);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    private function makePasswordResetMessage(UserPasswordResetEvent $event): Email
    {
        $domain = $this->registrationSources[$event->registrationSource] ?? null;

        if (null === $domain) {
            throw new DomainException('Источник регистрации не определен.');
        }

        return (new TemplatedEmail())
            ->from($this->appEmail)
            ->to($event->email)
            ->html($this->twig->render('mail/auth/reset-password.html.twig', [
                'token' => $event->resetPasswordToken,
                'domain' => $domain,
            ]));
    }
}
