<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240331091611 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Добавление пользователя в таблицу user';
    }

    public function up(Schema $schema): void
    {
        $sql = <<<SQL
            INSERT INTO auth.user
            VALUES (
                '40af5c7b-7bfe-4273-afd9-2d7ca649011b',
                'admin@test.ru',
                null,
                'active',
                'ROLE_ADMIN',
                '$2y$04$2X3naTpRDzYlPRrB5xulQ.D6lrHjvk61d9yt6cbDUMyybRUr6v372',
                null,
                null,
                null,
                '2024-03-31 12:00:00+0000',
                'Админ',
                'Админов',
                'system'
            )
        SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM auth.user WHERE id = '40af5c7b-7bfe-4273-afd9-2d7ca649011b'");
    }
}
