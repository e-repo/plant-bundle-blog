<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241208173527 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Создание таблицы file_metadata';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            '
            CREATE TABLE file_metadata (
                key UUID NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                type VARCHAR(50) NOT NULL, 
                extension VARCHAR(20) NOT NULL, 
                created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, 
                PRIMARY KEY(key)
            )'
        );

        $this->addSql('COMMENT ON COLUMN file_metadata.key IS \'Код(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN file_metadata.name IS \'Оригинальное наименование файла\'');
        $this->addSql('COMMENT ON COLUMN file_metadata.type IS \'Тип файла\'');
        $this->addSql('COMMENT ON COLUMN file_metadata.extension IS \'Расширение файла\'');
        $this->addSql('COMMENT ON COLUMN file_metadata.created_at IS \'(DC2Type:datetimetz_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE file_metadata');
    }
}
