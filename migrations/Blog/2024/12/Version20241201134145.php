<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241201134145 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Создание таблицы post';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE blog.post (
                id UUID NOT NULL, 
                category_id UUID NOT NULL, 
                slug VARCHAR(255) NOT NULL, 
                title VARCHAR(255) NOT NULL, 
                short_title VARCHAR(100) NOT NULL, 
                content TEXT NOT NULL, 
                status VARCHAR(100) NOT NULL, 
                comment_available BOOLEAN DEFAULT false NOT NULL, 
                created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, 
                meta_keyword VARCHAR(255) DEFAULT NULL, 
                meta_description VARCHAR(255) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ');

        $this->addSql('CREATE INDEX IDX_7EE8296212469DE2 ON blog.post (category_id)');
        $this->addSql('COMMENT ON COLUMN blog.post.id IS \'Код поста(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN blog.post.category_id IS \'Категория поста(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN blog.post.slug IS \'slug поста\'');
        $this->addSql('COMMENT ON COLUMN blog.post.title IS \'Заголовок\'');
        $this->addSql('COMMENT ON COLUMN blog.post.short_title IS \'Сокращенный заголовок, для карточки поста\'');
        $this->addSql('COMMENT ON COLUMN blog.post.content IS \'Содержание статьи\'');
        $this->addSql('COMMENT ON COLUMN blog.post.status IS \'статус поста\'');
        $this->addSql('COMMENT ON COLUMN blog.post.comment_available IS \'Доступность комментариев\'');
        $this->addSql('COMMENT ON COLUMN blog.post.created_at IS \'(DC2Type:datetimetz_immutable)\'');

        $this->addSql('ALTER TABLE blog.post ADD CONSTRAINT FK_7EE8296212469DE2 FOREIGN KEY (category_id) REFERENCES blog.category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE blog.post DROP CONSTRAINT FK_7EE8296212469DE2');
        $this->addSql('DROP TABLE blog.post');
    }
}
