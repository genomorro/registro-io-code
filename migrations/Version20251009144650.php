<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251009144650 extends AbstractMigration
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
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              patient_id INTEGER NOT NULL,
              place VARCHAR(255) NOT NULL,
              date_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
              ,
              type VARCHAR(255) NOT NULL,
              CONSTRAINT FK_FE38F8446B899279 FOREIGN KEY (patient_id) REFERENCES patient (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        SQL);
        $this->addSql('CREATE INDEX IDX_FE38F8446B899279 ON appointment (patient_id)');
        $this->addSql(<<<'SQL'
            CREATE TABLE attendance (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              patient_id INTEGER NOT NULL,
              check_in_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
              ,
              check_out_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
              ,
              tag INTEGER NOT NULL,
              CONSTRAINT FK_6DE30D916B899279 FOREIGN KEY (patient_id) REFERENCES patient (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        SQL);
        $this->addSql('CREATE INDEX IDX_6DE30D916B899279 ON attendance (patient_id)');
        $this->addSql(<<<'SQL'
            CREATE TABLE patient (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              file VARCHAR(12) NOT NULL,
              name VARCHAR(255) NOT NULL,
              disability BOOLEAN NOT NULL
            )
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE visitor (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              name VARCHAR(255) NOT NULL,
              phone VARCHAR(255) DEFAULT NULL,
              dni VARCHAR(255) NOT NULL,
              tag INTEGER NOT NULL,
              destination VARCHAR(255) NOT NULL,
              check_in_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
              ,
              check_out_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
              ,
              relationship VARCHAR(255) DEFAULT NULL
            )
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE visitor_patient (
              visitor_id INTEGER NOT NULL,
              patient_id INTEGER NOT NULL,
              PRIMARY KEY(visitor_id, patient_id),
              CONSTRAINT FK_D8C9472370BEE6D FOREIGN KEY (visitor_id) REFERENCES visitor (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE,
              CONSTRAINT FK_D8C947236B899279 FOREIGN KEY (patient_id) REFERENCES patient (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        SQL);
        $this->addSql('CREATE INDEX IDX_D8C9472370BEE6D ON visitor_patient (visitor_id)');
        $this->addSql('CREATE INDEX IDX_D8C947236B899279 ON visitor_patient (patient_id)');
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              body CLOB NOT NULL,
              headers CLOB NOT NULL,
              queue_name VARCHAR(190) NOT NULL,
              created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
              ,
              available_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
              ,
              delivered_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
            )
        SQL);
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE appointment');
        $this->addSql('DROP TABLE attendance');
        $this->addSql('DROP TABLE patient');
        $this->addSql('DROP TABLE visitor');
        $this->addSql('DROP TABLE visitor_patient');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
