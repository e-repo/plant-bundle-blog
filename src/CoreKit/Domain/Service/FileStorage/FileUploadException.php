<?php

declare(strict_types=1);

namespace CoreKit\Domain\Service\FileStorage;

use Exception;
use Throwable;

final class FileUploadException extends Exception
{
    private const DEFAULT_MESSAGE = 'Ошибка загрузки файла в хранилище. Попробуйте повторить позже.';

    public function __construct(
        private readonly string $fileName,
        Throwable $previous,
        ?string $message = null
    ) {
        parent::__construct(
            message: $message ?? self::DEFAULT_MESSAGE,
            previous: $previous,
        );
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }
}
