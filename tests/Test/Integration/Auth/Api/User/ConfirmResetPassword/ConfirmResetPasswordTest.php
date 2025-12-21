<?php

declare(strict_types=1);

namespace Test\Integration\Auth\Api\User\ConfirmResetPassword;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\DBAL\Exception;
use JsonException;
use Test\Common\DataFromJsonResponseTrait;
use Test\Common\FunctionalTestCase;

final class ConfirmResetPasswordTest extends FunctionalTestCase
{
    use DataFromJsonResponseTrait;

    private const REQUEST_RESET_URL = '/api/auth/v1/user/request-reset-password';

    private const CONFIRM_RESET_URL = '/api/auth/v1/user/confirm-reset-password';

    public function setUp(): void
    {
        parent::setUp();

        // arrange
        $this->databaseTool
            ->withPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE)
            ->loadFixtures([
                UserFixture::class,
            ]);

        $this->mailerListener->reset();
    }

    /**
     * @throws JsonException
     */
    public function testSuccessConfirmResetPassword(): void
    {
        // arrange
        $newPassword = 'qwerty';
        $loadedUser = UserFixture::allItems()[1];
        $client = $this->createClient();

        $expectedResponse = [
            'data' => [
                'status' => 'Пароль успешно обновлен.',
            ],
            'meta' => null,
        ];

        // action
        $client->jsonRequest(
            method: 'POST',
            uri: self::REQUEST_RESET_URL,
            parameters: [
                'email' => $loadedUser['email'],
                'registrationSource' => $loadedUser['registrationSource'],
            ]
        );

        $savedUser = $this->getUserByEmail($loadedUser['email']);
        $oldPasswordHash = $savedUser['password_hash'];

        $client->jsonRequest(
            method: 'POST',
            uri: self::CONFIRM_RESET_URL,
            parameters: [
                'token' => $savedUser['reset_password_token'],
                'password' => $newPassword,
            ]
        );

        $response = $this->getDataFromJsonResponse($client->getResponse());

        // assert
        self::assertResponseIsSuccessful();
        self::assertEquals($expectedResponse, $response);

        $savedUser = $this->getUserByEmail($loadedUser['email']);

        self::assertNull($savedUser['reset_password_token']);
        self::assertNull($savedUser['password_token_expires']);
        self::assertNotEquals($oldPasswordHash, $savedUser['password_hash']);
    }

    /**
     * @throws Exception
     */
    private function getUserByEmail(string $email): array
    {
        return $this->queryBuilder
            ->select('*')
            ->from('auth.user', 'u')
            ->where(
                $this->queryBuilder->expr()->eq('u.email', ':email')
            )
            ->setParameter('email', $email)
            ->fetchAssociative();
    }
}
