<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250526123911 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE participant ADD email VARCHAR(180) NOT NULL, ADD roles JSON NOT NULL, ADD password VARCHAR(255) NOT NULL, DROP mail, DROP mot_passe, CHANGE telephone telephone VARCHAR(255) NOT NULL, CHANGE administrateur administrateur TINYINT(1) NOT NULL, CHANGE actif actif TINYINT(1) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON participant (email)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_IDENTIFIER_EMAIL ON participant
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participant ADD mot_passe VARCHAR(255) NOT NULL, DROP email, DROP roles, CHANGE telephone telephone VARCHAR(50) DEFAULT NULL, CHANGE administrateur administrateur TINYINT(1) DEFAULT NULL, CHANGE actif actif TINYINT(1) DEFAULT NULL, CHANGE password mail VARCHAR(255) NOT NULL
        SQL);
    }
}
