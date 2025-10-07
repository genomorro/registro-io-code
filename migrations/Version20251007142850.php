<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251007142850 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE visitor (
              id INT AUTO_INCREMENT NOT NULL,
              name VARCHAR(255) NOT NULL,
              phone INT DEFAULT NULL,
              dni VARCHAR(255) NOT NULL,
              tag INT NOT NULL,
              destination VARCHAR(255) NOT NULL,
              check_in_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
              check_out_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
              relationship VARCHAR(255) DEFAULT NULL,
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE visitor_patient (
              visitor_id INT NOT NULL,
              patient_id INT NOT NULL,
              INDEX IDX_D8C9472370BEE6D (visitor_id),
              INDEX IDX_D8C947236B899279 (patient_id),
              PRIMARY KEY(visitor_id, patient_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              visitor_patient
            ADD
              CONSTRAINT FK_D8C9472370BEE6D FOREIGN KEY (visitor_id) REFERENCES visitor (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              visitor_patient
            ADD
              CONSTRAINT FK_D8C947236B899279 FOREIGN KEY (patient_id) REFERENCES patient (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE visitor_patient DROP FOREIGN KEY FK_D8C9472370BEE6D');
        $this->addSql('ALTER TABLE visitor_patient DROP FOREIGN KEY FK_D8C947236B899279');
        $this->addSql('DROP TABLE visitor');
        $this->addSql('DROP TABLE visitor_patient');
    }
}
