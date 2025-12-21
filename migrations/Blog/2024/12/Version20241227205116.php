<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241227205116 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Добавление индексов уникальности для полей сущностей post и post_image. Удаление поля parent.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7EE82962989D9B62 ON blog.post (slug)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7EE829622B36786B ON blog.post (title)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7EE82962AB33D86E ON blog.post (short_title)');

        $this->addSql('ALTER TABLE blog.post_image DROP CONSTRAINT fk_8b4c276a727aca70');
        $this->addSql('DROP INDEX blog.uniq_8b4c276a727aca70');
        $this->addSql('ALTER TABLE blog.post_image DROP parent_id');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8B4C276AA5D32530 ON blog.post_image (file_key)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX blog.UNIQ_8B4C276AA5D32530');

        $this->addSql('ALTER TABLE blog.post_image ADD parent_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN blog.post_image.parent_id IS \'Миниатюра главного изображения(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE blog.post_image ADD CONSTRAINT fk_8b4c276a727aca70 
                                FOREIGN KEY (parent_id) REFERENCES blog.post_image (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_8b4c276a727aca70 ON blog.post_image (parent_id)');

        $this->addSql('DROP INDEX blog.UNIQ_7EE82962989D9B62');
        $this->addSql('DROP INDEX blog.UNIQ_7EE829622B36786B');
        $this->addSql('DROP INDEX blog.UNIQ_7EE82962AB33D86E');
    }
}
