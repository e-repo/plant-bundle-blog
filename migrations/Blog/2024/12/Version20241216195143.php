<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241216195143 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Создание таблицы post_image';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE blog.post_image (
            id UUID NOT NULL, 
            post_id UUID NOT NULL, 
            parent_id UUID DEFAULT NULL, 
            file_key UUID NOT NULL, 
            type VARCHAR(50) NOT NULL, 
            is_active BOOLEAN NOT NULL, 
            created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, 
            PRIMARY KEY(id)
        )');

        $this->addSql('CREATE INDEX IDX_8B4C276A4B89032C ON blog.post_image (post_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8B4C276A727ACA70 ON blog.post_image (parent_id)');

        $this->addSql('COMMENT ON COLUMN blog.post_image.id IS \'Идентификатор изображения(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN blog.post_image.post_id IS \'Код поста(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN blog.post_image.parent_id IS \'Миниатюра главного изображения(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN blog.post_image.file_key IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN blog.post_image.type IS \'Тип файла\'');
        $this->addSql('COMMENT ON COLUMN blog.post_image.is_active IS \'Признак активного изображения\'');
        $this->addSql('COMMENT ON COLUMN blog.post_image.created_at IS \'Дата создания(DC2Type:datetimetz_immutable)\'');

        $this->addSql('ALTER TABLE blog.post_image ADD CONSTRAINT FK_8B4C276A4B89032C FOREIGN KEY (post_id) REFERENCES blog.post (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE blog.post_image ADD CONSTRAINT FK_8B4C276A727ACA70 FOREIGN KEY (parent_id) REFERENCES blog.post_image (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE blog.post_image DROP CONSTRAINT FK_8B4C276A4B89032C');
        $this->addSql('ALTER TABLE blog.post_image DROP CONSTRAINT FK_8B4C276A727ACA70');
        $this->addSql('DROP TABLE blog.post_image');
    }
}
