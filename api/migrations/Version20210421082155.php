<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210421082155 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE archive ADD COLUMN expires_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_D5FC5D9C772E836A');
        $this->addSql('CREATE TEMPORARY TABLE __temp__archive AS SELECT id, client, created_at, updated_at, identifier, status FROM archive');
        $this->addSql('DROP TABLE archive');
        $this->addSql('CREATE TABLE archive (id CHAR(36) NOT NULL --(DC2Type:uuid)
        , client VARCHAR(128) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, identifier VARCHAR(128) NOT NULL, status SMALLINT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO archive (id, client, created_at, updated_at, identifier, status) SELECT id, client, created_at, updated_at, identifier, status FROM __temp__archive');
        $this->addSql('DROP TABLE __temp__archive');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D5FC5D9C772E836A ON archive (identifier)');
        $this->addSql('DROP INDEX IDX_8C9F36102956195F');
        $this->addSql('CREATE TEMPORARY TABLE __temp__file AS SELECT id, archive_id, uri, path FROM file');
        $this->addSql('DROP TABLE file');
        $this->addSql('CREATE TABLE file (id CHAR(36) NOT NULL --(DC2Type:uuid)
        , archive_id CHAR(36) DEFAULT NULL --(DC2Type:uuid)
        , uri VARCHAR(1024) NOT NULL, path VARCHAR(1024) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO file (id, archive_id, uri, path) SELECT id, archive_id, uri, path FROM __temp__file');
        $this->addSql('DROP TABLE __temp__file');
        $this->addSql('CREATE INDEX IDX_8C9F36102956195F ON file (archive_id)');
    }
}
