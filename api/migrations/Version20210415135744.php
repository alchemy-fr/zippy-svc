<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210415135744 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE archive (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:uuid)
        , client VARCHAR(128) NOT NULL COLLATE BINARY, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, identifier VARCHAR(128) NOT NULL COLLATE BINARY, status SMALLINT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D5FC5D9C772E836A ON archive (identifier)');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE file (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:uuid)
        , archive_id CHAR(36) DEFAULT NULL COLLATE BINARY --(DC2Type:uuid)
        , uri VARCHAR(1024) NOT NULL COLLATE BINARY, path VARCHAR(1024) NOT NULL COLLATE BINARY, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8C9F36102956195F ON file (archive_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE archive');
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE file');
    }
}
