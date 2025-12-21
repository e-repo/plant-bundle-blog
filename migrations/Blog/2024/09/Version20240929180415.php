<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240929180415 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Создание таблицы reader';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA IF NOT EXISTS blog');

        $this->addSql(
            '
            CREATE TABLE "blog"."reader" (
                id UUID NOT NULL, 
                email VARCHAR(100) NOT NULL, 
                created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, 
                name_first VARCHAR(255) NOT NULL, 
                name_last VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id))'
        );

        $this->addSql('CREATE UNIQUE INDEX UNIQ_136FF762E7927C74 ON "blog"."reader" (email)');
        $this->addSql('COMMENT ON COLUMN "blog"."reader".id IS \'Код читателя(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN "blog"."reader".email IS \'(DC2Type:email)\'');
        $this->addSql('COMMENT ON COLUMN "blog"."reader".created_at IS \'Дата создания читателя(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "blog"."reader".name_first IS \'Имя\'');
        $this->addSql('COMMENT ON COLUMN "blog"."reader".name_last IS \'Фамилия\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE "blog"."reader"');
    }
}
