<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250507152659 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE objectif ADD user_id INT NOT NULL, ADD target_amount DOUBLE PRECISION NOT NULL, ADD current_amount DOUBLE PRECISION NOT NULL, ADD start_date DATETIME NOT NULL, ADD end_date DATETIME NOT NULL, DROP targetAmount, DROP currentAmount, DROP startDate, DROP endDate
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE objectif ADD CONSTRAINT FK_E2F86851A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E2F86851A76ED395 ON objectif (user_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE objectif DROP FOREIGN KEY FK_E2F86851A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_E2F86851A76ED395 ON objectif
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE objectif ADD targetAmount NUMERIC(10, 0) NOT NULL, ADD currentAmount NUMERIC(10, 0) NOT NULL, ADD startDate DATETIME NOT NULL, ADD endDate DATETIME NOT NULL, DROP user_id, DROP target_amount, DROP current_amount, DROP start_date, DROP end_date
        SQL);
    }
}
