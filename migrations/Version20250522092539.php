<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250522092539 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE categorie ADD user_id INT NOT NULL, ADD nom VARCHAR(255) NOT NULL, ADD description VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE categorie ADD CONSTRAINT FK_497DD634A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_497DD634A76ED395 ON categorie (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction ADD categorie_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction ADD CONSTRAINT FK_723705D1BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_723705D1BCF5E72D ON transaction (categorie_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE categorie DROP FOREIGN KEY FK_497DD634A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_497DD634A76ED395 ON categorie
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE categorie DROP user_id, DROP nom, DROP description
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1BCF5E72D
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_723705D1BCF5E72D ON transaction
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction DROP categorie_id
        SQL);
    }
}
