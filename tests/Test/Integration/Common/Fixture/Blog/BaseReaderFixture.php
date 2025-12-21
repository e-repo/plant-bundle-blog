<?php

declare(strict_types=1);

namespace Test\Integration\Common\Fixture\Blog;

use Blog\Domain\Reader\Entity\Reader;
use Blog\Domain\Reader\Entity\ReaderDto;
use Blog\Domain\Reader\Entity\Specification\SpecificationAggregator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Test\Integration\Common\Fixture\BaseFixtureTrait;
use Test\Integration\Common\Fixture\ReferencableInterface;

class BaseReaderFixture extends Fixture implements ReferencableInterface
{
    use BaseFixtureTrait;

    public function __construct(
        private readonly SpecificationAggregator $specificationAggregator,
    ) {}

    public function load(ObjectManager $manager): void
    {
        foreach (static::allItems() as $key => $item) {
            ++$key;

            $reader = new Reader(
                readerDto: new ReaderDto(
                    firstname: $item['firstName'],
                    lastname: $item['lastName'],
                    email: $item['email'],
                    id: $item['id'],
                ),
                specificationAggregator: $this->specificationAggregator
            );

            $manager->persist($reader);

            $this->addReference(self::getReferenceName($key), $reader);
        }

        $manager->flush();
    }

    public static function getPrefix(): string
    {
        return 'reader';
    }
}
