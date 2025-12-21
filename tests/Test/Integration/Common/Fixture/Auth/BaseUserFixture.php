<?php

declare(strict_types=1);

namespace Test\Integration\Common\Fixture\Auth;

use Auth\Domain\User\Entity\NameVo;
use Auth\Domain\User\Entity\Role;
use Auth\Domain\User\Entity\Specification\UniqueEmailSpecification;
use Auth\Domain\User\Entity\Status;
use Auth\Domain\User\Entity\User;
use Auth\Domain\User\Service\PasswordHasher\Hasher;
use CoreKit\Domain\Entity\Email;
use CoreKit\Domain\Entity\Id;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Test\Integration\Common\Fixture\BaseFixtureTrait;
use Test\Integration\Common\Fixture\ReferencableInterface;

class BaseUserFixture extends Fixture implements ReferencableInterface
{
    use BaseFixtureTrait;

    public function __construct(
        private readonly Hasher $hasher,
        private readonly UniqueEmailSpecification $uniqueEmailSpecification,
    ) {}

    public function load(ObjectManager $manager): void
    {
        foreach (static::allItems() as $key => $item) {
            ++$key;

            $user = new User(
                id: new Id($item['id']),
                name: new NameVo(
                    first: $item['firstName'],
                    last: $item['lastName']
                ),
                email: new Email($item['email']),
                password: $item['password'],
                registrationSource: $item['registrationSource'],
                uniqueEmailSpecification: $this->uniqueEmailSpecification,
                hasher: $this->hasher,
            );

            if (isset($item['status'])) {
                $user->changeStatus(
                    status: Status::from($item['status']),
                    changedBy: $user->getEmail()->value
                );
            }

            if (isset($item['role'])) {
                $user->changeRole(Role::from($item['role']));
            }

            $user->clearRecordedEvents();

            $manager->persist($user);

            $this->addReference(self::getReferenceName($key), $user);
        }

        $manager->flush();
    }

    public static function getPrefix(): string
    {
        return 'user';
    }
}
