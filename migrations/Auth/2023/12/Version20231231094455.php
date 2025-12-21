<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231231094455 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Добавление таблицы refresh_tokens';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            '
            CREATE TABLE "auth"."refresh_tokens" (
                id INT NOT NULL, 
                refresh_token VARCHAR(128) NOT NULL, 
                username VARCHAR(255) NOT NULL, 
                valid TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
                PRIMARY KEY(id)
            )'
        );

        $this->addSql('CREATE SEQUENCE IF NOT EXISTS refresh_tokens_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_39BB651DC74F2195 ON "auth"."refresh_tokens" (refresh_token)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE refresh_tokens_id_seq CASCADE');
        $this->addSql('DROP TABLE "auth"."refresh_tokens"');
    }
}
