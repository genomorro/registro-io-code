<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251006011942 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE appointment (
              id INT AUTO_INCREMENT NOT NULL,
              patient_id INT NOT NULL,
              place VARCHAR(255) NOT NULL,
              date_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
              type VARCHAR(255) NOT NULL,
              INDEX IDX_FE38F8446B899279 (patient_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              appointment
            ADD
              CONSTRAINT FK_FE38F8446B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)
        SQL);
        $this->addSql('ALTER TABLE date DROP FOREIGN KEY FK_AA9E377A6B899279');
        $this->addSql('DROP TABLE date');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE date (
              id INT AUTO_INCREMENT NOT NULL,
              patient_id INT NOT NULL,
              date_at DATETIME NOT NULL,
              place VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
              type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
              INDEX IDX_AA9E377A6B899279 (patient_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = ''
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              date
            ADD
              CONSTRAINT FK_AA9E377A6B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)
        SQL);
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F8446B899279');
        $this->addSql('DROP TABLE appointment');
    }
}
