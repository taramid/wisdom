<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260728192714 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE wisdom (id UUID NOT NULL, title VARCHAR(255) NOT NULL, body TEXT NOT NULL, tags VARCHAR(255) DEFAULT NULL, subject_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_D4F58FE523EDC87 ON wisdom (subject_id)');
        $this->addSql('ALTER TABLE wisdom ADD CONSTRAINT FK_D4F58FE523EDC87 FOREIGN KEY (subject_id) REFERENCES subject (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wisdom DROP CONSTRAINT FK_D4F58FE523EDC87');
        $this->addSql('DROP TABLE wisdom');
    }
}
