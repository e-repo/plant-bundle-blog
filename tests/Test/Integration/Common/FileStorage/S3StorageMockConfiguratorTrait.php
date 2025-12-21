<?php

declare(strict_types=1);

namespace Test\Integration\Common\FileStorage;

use CoreKit\Infra\FileStorage\S3StorageClient;

trait S3StorageMockConfiguratorTrait
{
    protected function setMockS3StorageClient(): void
    {
        $s3ClientMock = $this->createMock(S3StorageClient::class);
        $this->container->set(S3StorageClient::class, $s3ClientMock);
    }
}
