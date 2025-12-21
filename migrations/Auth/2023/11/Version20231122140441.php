<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231122140441 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Создание таблицы user';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA IF NOT EXISTS auth');

        $this->addSql(
            'CREATE TABLE "auth"."user" (
                id UUID NOT NULL, 
                email VARCHAR(100) NOT NULL, 
                email_confirm_token VARCHAR(50) DEFAULT NULL, 
                status VARCHAR(50) NOT NULL, 
                role VARCHAR(100) NOT NULL, 
                password_hash VARCHAR(255) NOT NULL, 
                reset_password_token VARCHAR(50) DEFAULT NULL, 
                password_token_expires TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, 
                new_email VARCHAR(100) DEFAULT NULL, 
                created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, 
                name_first VARCHAR(255) NOT NULL, 
                name_last VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )'
        );

        $this->addSql('CREATE UNIQUE INDEX UNIQ_6707FF82E7927C74 ON "auth"."user" (email)');

        $this->addSql('COMMENT ON COLUMN "auth"."user".id IS \'Код пользователя(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN "auth"."user".email IS \'(DC2Type:email)\'');
        $this->addSql('COMMENT ON COLUMN "auth"."user".email_confirm_token IS \'Токен для подтверждения email\'');
        $this->addSql('COMMENT ON COLUMN "auth"."user".status IS \'Статус пользователя\'');
        $this->addSql('COMMENT ON COLUMN "auth"."user".role IS \'Роль пользователя\'');
        $this->addSql('COMMENT ON COLUMN "auth"."user".password_hash IS \'Хэш пароля\'');
        $this->addSql('COMMENT ON COLUMN "auth"."user".reset_password_token IS \'Токен сброса пароля\'');
        $this->addSql('COMMENT ON COLUMN "auth"."user".password_token_expires IS \'Дата действия токена сброса пароля(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "auth"."user".new_email IS \'Новый email (при смене)(DC2Type:email)\'');
        $this->addSql('COMMENT ON COLUMN "auth"."user".created_at IS \'Дата создания пользователя(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "auth"."user".name_first IS \'Имя\'');
        $this->addSql('COMMENT ON COLUMN "auth"."user".name_last IS \'Фамилия\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE "auth"."user"');
    }
}
