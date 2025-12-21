<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241027172254 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Создание таблицы blog.category';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE blog.category (
            id UUID NOT NULL, 
            name VARCHAR(50) NOT NULL, 
            description VARCHAR(255) NOT NULL, 
            created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, 
            PRIMARY KEY(id)
        )');

        $this->addSql('COMMENT ON COLUMN blog.category.id IS \'Код категории(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN blog.category.name IS \'Наименование категории\'');
        $this->addSql('COMMENT ON COLUMN blog.category.description IS \'Описание категории\'');
        $this->addSql('COMMENT ON COLUMN blog.category.created_at IS \'Дата создания категории(DC2Type:datetimetz_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE blog.category');
    }
}
