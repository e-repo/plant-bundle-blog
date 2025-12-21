<?php

declare(strict_types=1);

namespace Test\Integration\Service\Menu\GetMenu;

use JsonException;
use Symfony\Component\HttpFoundation\Response;
use Test\Common\DataFromJsonResponseTrait;
use Test\Common\FunctionalTestCase;
use Test\Integration\Common\User\UserBuilder;

final class GetMenuTest extends FunctionalTestCase
{
    use DataFromJsonResponseTrait;

    private const URI = '/api/service/v1/menu';

    /**
     * @throws JsonException
     */
    public function testSuccessGetMenu(): void
    {
        // arrange
        $client = $this->createClient();

        $expectedItemKeys = ['id', 'title', 'icon'];

        $client->loginUser(
            UserBuilder::createAdmin()->build()
        );

        // action
        $client->request(
            method: 'GET',
            uri: self::URI,
            parameters: [
                'name' => 'service',
            ]
        );

        $response = $this->getDataFromJsonResponse($client->getResponse());
        $responseData = $response['data'];

        // assert
        self::assertResponseIsSuccessful();
        self::assertGreaterThanOrEqual(1, count($responseData));

        $menuItem = reset($responseData);

        foreach ($expectedItemKeys as $key) {
            self::assertArrayHasKey($key, $menuItem);
        }
    }

    /**
     * @throws JsonException
     */
    public function testFailedMenuNotFound(): void
    {
        // arrange
        $client = $this->createClient();
        $expectedErrorRegex = "/Меню '.+' не найдено/";

        $client->loginUser(
            UserBuilder::createAdmin()->build()
        );

        // action
        $client->request(
            method: 'GET',
            uri: self::URI,
            parameters: [
                'name' => $this->faker->uuid(),
            ]
        );

        $response = $this->getDataFromJsonResponse($client->getResponse());
        $errors = $response['errors'];

        // assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        self::assertMatchesRegularExpression($expectedErrorRegex, $errors[0]['detail']);
    }

    /**
     * @throws JsonException
     */
    public function testFailedAccessDenied(): void
    {
        // arrange
        $client = $this->createClient();

        $expectedErrorMessage = 'Доступ запрещен.';

        $client->loginUser(
            UserBuilder::createUser()->build()
        );

        // action
        $client->request(
            method: 'GET',
            uri: self::URI,
            parameters: [
                'name' => $this->faker->uuid(),
            ]
        );

        // assert
        $response = $this->getDataFromJsonResponse($client->getResponse());

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $error = reset($response['errors']);
        self::assertEquals($expectedErrorMessage, $error['detail']);
    }
}
