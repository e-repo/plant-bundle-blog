<?php

declare(strict_types=1);

namespace CoreKit\Domain\Service\FileStorage;

use Exception;
use Throwable;

final class FileDownloadException extends Exception
{
    private const DEFAULT_MESSAGE = 'Ошибка скачивания файла из хранилища. Попробуйте повторить позже.';

    public function __construct(
        public readonly string $fileKey,
        Throwable $previous,
        ?string $message = null
    ) {
        parent::__construct(
            message: $message ?? self::DEFAULT_MESSAGE,
            previous: $previous,
        );
    }
}
