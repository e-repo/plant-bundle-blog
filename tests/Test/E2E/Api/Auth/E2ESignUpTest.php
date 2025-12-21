<?php

declare(strict_types=1);

namespace Test\E2E\Api\Auth;

use Auth\Domain\User\Entity\User;
use Blog\Domain\Reader\Entity\Reader;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ObjectRepository;
use JsonException;
use Test\Common\DataFromJsonResponseTrait;
use Test\Common\FunctionalTestCase;

final class E2ESignUpTest extends FunctionalTestCase
{
    use DataFromJsonResponseTrait;

    private const ENDPOINT_URL = '/api/auth/v1/user/sign-up';

    private ObjectRepository $userRepository;

    private ObjectRepository $readerRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->entityManager->getRepository(User::class);
        $this->readerRepository = $this->entityManager->getRepository(Reader::class);
    }

    /**
     * @throws Exception
     * @throws JsonException
     */
    public function testSuccessSignUp(): void
    {
        // arrange
        $userEmail = 'test@test.ru';
        $userFirstName = 'Test';
        $registrationSource = 'blog';

        $client = $this->createClient();

        // action
        $client->jsonRequest(
            method: 'POST',
            uri: self::ENDPOINT_URL,
            parameters: [
                'firstName' => $userFirstName,
                'email' => $userEmail,
                'password' => 'secret',
                'registrationSource' => $registrationSource,
            ]
        );

        // assert
        self::assertResponseIsSuccessful();

        /** @var User $user */
        $user = $this->userRepository->findOneBy([
            'email' => $userEmail,
        ]);
        /** @var Reader $reader */
        $reader = $this->readerRepository->findOneBy([
            'email' => $userEmail,
        ]);

        self::assertNotNull($user); // В модуле Auth
        self::assertNotNull($reader); // В модуле Blog
        self::assertEquals($user->getId()->value, $reader->getId()->value);
    }
}
