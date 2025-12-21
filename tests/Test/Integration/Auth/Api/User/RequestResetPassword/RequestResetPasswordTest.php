<?php

declare(strict_types=1);

namespace Test\Integration\Auth\Api\User\RequestResetPassword;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\DBAL\Exception;
use JsonException;
use Symfony\Component\HttpFoundation\Response;
use Test\Common\DataFromJsonResponseTrait;
use Test\Common\FunctionalTestCase;

final class RequestResetPasswordTest extends FunctionalTestCase
{
    use DataFromJsonResponseTrait;

    private const ENDPOINT_URL = '/api/auth/v1/user/request-reset-password';

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
     * @throws Exception
     */
    public function testSuccessPasswordReset(): void
    {
        // arrange
        $loadedUser = UserFixture::allItems()[1];
        $client = $this->createClient();

        $expectedResponse = [
            'data' => [
                'status' => 'Запрос на сброс пароля успешно зарегистрирован, для дальнейших действий перейдите в указанную почту',
            ],
            'meta' => null,
        ];

        // action
        $client->jsonRequest(
            method: 'POST',
            uri: self::ENDPOINT_URL,
            parameters: [
                'email' => $loadedUser['email'],
                'registrationSource' => $loadedUser['registrationSource'],
            ]
        );

        $response = $this->getDataFromJsonResponse($client->getResponse());
        $savedUser = $this->getUserByEmail($loadedUser['email']);

        // assert
        self::assertResponseIsSuccessful();
        self::assertEquals($expectedResponse, $response);

        self::assertEquals($loadedUser['email'], $savedUser['email']);
        self::assertEquals($loadedUser['status'], $savedUser['status']);
        self::assertEquals('ROLE_USER', $savedUser['role']);
        self::assertEquals($loadedUser['firstName'], $savedUser['name_first']);
        self::assertEquals($loadedUser['registrationSource'], $savedUser['registration_source']);
        self::assertNotEmpty($savedUser['password_hash']);
        self::assertNotEmpty($savedUser['reset_password_token']);
        self::assertNotEmpty($savedUser['password_token_expires']);
        self::assertNull($savedUser['email_confirm_token']);

        self::assertEmailCount(1);

        $mail = self::getMailerMessage();
        $resetLink = sprintf('/https?:[\/]{2}[a-z.]+\/confirm-reset-password\?token=%s/', $savedUser['reset_password_token']);

        self::assertMatchesRegularExpression($resetLink, $mail->getHtmlBody());
    }

    /**
     * @throws Exception
     * @throws JsonException
     */
    public function testFailedResetByInvalidEmail(): void
    {
        // arrange
        $fakeEmail = 'test@test.ru';
        $loadedUser = UserFixture::allItems()[1];
        $client = $this->createClient();

        $expectedResponse = [
            'message' => 'Ошибка бизнес-логики.',
            'errors' => [
                [
                    'detail' => 'Пользователь по указанному email не найден',
                    'source' => '',
                    'data' => [],
                ],
            ],
        ];

        // action
        $client->jsonRequest(
            method: 'POST',
            uri: self::ENDPOINT_URL,
            parameters: [
                'email' => $fakeEmail,
                'registrationSource' => $loadedUser['registrationSource'],
            ]
        );

        $savedUser = $this->getUserByEmail($loadedUser['email']);
        $response = $this->getDataFromJsonResponse($client->getResponse());

        // assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertEquals($expectedResponse, $response);
        self::assertEmpty($savedUser['reset_password_token']);
        self::assertEmpty($savedUser['password_token_expires']);
    }

    /**
     * @throws Exception
     * @throws JsonException
     */
    public function testFailedResetByNotActiveUser(): void
    {
        // arrange
        $loadedUser = UserFixture::allItems()[0];
        $client = $this->createClient();

        $expectedResponse = [
            'message' => 'Ошибка бизнес-логики.',
            'errors' => [
                [
                    'detail' => 'Невозможно сбросить пароль т.к пользователь не является активным',
                    'source' => '',
                    'data' => [],
                ],
            ],
        ];

        // action
        $client->jsonRequest(
            method: 'POST',
            uri: self::ENDPOINT_URL,
            parameters: [
                'email' => $loadedUser['email'],
                'registrationSource' => $loadedUser['registrationSource'],
            ]
        );

        $savedUser = $this->getUserByEmail($loadedUser['email']);
        $response = $this->getDataFromJsonResponse($client->getResponse());

        // assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertEquals($expectedResponse, $response);
        self::assertEmpty($savedUser['reset_password_token']);
        self::assertEmpty($savedUser['password_token_expires']);
    }

    /**
     * @throws Exception
     * @throws JsonException
     */
    public function testFailedResetByRequestAlreadySent(): void
    {
        // arrange
        $loadedUser = UserFixture::allItems()[1];
        $client = $this->createClient();

        $expectedResponse = [
            'message' => 'Ошибка бизнес-логики.',
            'errors' => [
                [
                    'detail' => 'Запрос на сброс пароля уже был отправлен. Действует в течении суток',
                    'source' => '',
                    'data' => [],
                ],
            ],
        ];

        // action
        $client->jsonRequest(
            method: 'POST',
            uri: self::ENDPOINT_URL,
            parameters: [
                'email' => $loadedUser['email'],
                'registrationSource' => $loadedUser['registrationSource'],
            ]
        );

        //        $this->entityManager->clear();

        $client->jsonRequest(
            method: 'POST',
            uri: self::ENDPOINT_URL,
            parameters: [
                'email' => $loadedUser['email'],
                'registrationSource' => $loadedUser['registrationSource'],
            ]
        );

        $savedUser = $this->getUserByEmail($loadedUser['email']);
        $response = $this->getDataFromJsonResponse($client->getResponse());

        // assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertEquals($expectedResponse, $response);
        self::assertNotEmpty($savedUser['reset_password_token']);
        self::assertNotEmpty($savedUser['password_token_expires']);
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
