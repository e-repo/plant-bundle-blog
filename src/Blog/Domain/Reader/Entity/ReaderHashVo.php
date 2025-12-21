<?php

declare(strict_types=1);

namespace Blog\Domain\Reader\Entity;

final readonly class ReaderHashVo
{
    public function __construct(
        public string $firstname,
        public ?string $lastname,
        public string $email,
    ) {}

    public function isEqual(Reader $reader): bool
    {
        return $this->hash() === $reader->makeReaderHash()->hash();
    }

    public function isEmailNotEqual(Reader $reader): bool
    {
        return $reader->getEmail()->value !== $this->email;
    }

    public function hash(): string
    {
        $hashData = [
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->lastname,
        ];

        return md5(serialize($hashData));
    }
}
