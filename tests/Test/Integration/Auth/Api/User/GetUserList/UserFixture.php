<?php

declare(strict_types=1);

namespace Test\Integration\Auth\Api\User\GetUserList;

use Test\Integration\Common\Fixture\Auth\BaseUserFixture;

final class UserFixture extends BaseUserFixture
{
    public static function allItems(): array
    {
        return [
            [
                'id' => 'f472d1a5-ba78-4039-94e3-ae0161256eaf',
                'firstName' => 'TestFirstName_1',
                'lastName' => 'TestLastName_1',
                'email' => 'test_1@test.ru',
                'password' => 'secret_1',
                'status' => 'wait',
                'registrationSource' => 'blog',
            ],
            [
                'id' => '125d7cd7-0b77-409a-bc5c-19b44416a5fa',
                'firstName' => 'TestFirstName_2',
                'lastName' => 'TestLastName_2',
                'email' => 'test_2@test.ru',
                'password' => 'secret_2',
                'status' => 'active',
                'registrationSource' => 'blog',
            ],
            [
                'id' => '76c3a2d9-49fd-4fbd-a0f4-0022d38dbaba',
                'firstName' => 'TestFirstName_3',
                'lastName' => 'TestLastName_3',
                'email' => 'test_3@test.ru',
                'password' => 'secret_3',
                'status' => 'blocked',
                'registrationSource' => 'blog',
            ],
            [
                'id' => 'be5a8db3-e44b-4358-861e-b74109447efd',
                'firstName' => 'TestFirstName_4',
                'lastName' => 'TestLastName_4',
                'email' => 'test_4@test.ru',
                'password' => 'secret_4',
                'status' => 'active',
                'registrationSource' => 'blog',
            ],
            [
                'id' => 'a7f2ddde-276b-4caa-84e9-2d42e6af8f1d',
                'firstName' => 'TestFirstName_15',
                'lastName' => 'TestLastName_15',
                'email' => 'test_15@test.ru',
                'password' => 'secret_15',
                'status' => 'active',
                'registrationSource' => 'blog',
            ],
        ];
    }
}
