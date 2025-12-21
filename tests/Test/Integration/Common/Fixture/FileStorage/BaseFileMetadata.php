<?php

declare(strict_types=1);

namespace Test\Integration\Common\Fixture\FileStorage;

use Carbon\CarbonImmutable;
use CoreKit\Domain\Entity\FileMetadata;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Test\Integration\Common\Fixture\BaseFixtureTrait;
use Test\Integration\Common\Fixture\ReferencableInterface;

class BaseFileMetadata extends Fixture implements ReferencableInterface
{
    use BaseFixtureTrait;

    public function load(ObjectManager $manager): void
    {
        foreach (static::allItems() as $key => $item) {
            ++$key;

            $fileMetadata = new FileMetadata(
                key: $item['fileKey'],
                name: $item['name'],
                type: $item['type'],
                extension: $item['extension'],
                createdAt: isset($item['created_at'])
                    ? CarbonImmutable::parse($item['created_at'])->toDateTimeImmutable()
                    : new DateTimeImmutable(),
            );

            $manager->persist($fileMetadata);

            $this->addReference(self::getReferenceName($key), $fileMetadata);
        }

        $manager->flush();
    }

    public static function getPrefix(): string
    {
        return 'file-metadata';
    }
}
