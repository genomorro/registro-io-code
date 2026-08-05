<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260701163935 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE appointment (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, patient_id INTEGER NOT NULL, agenda VARCHAR(255) NOT NULL, specialty VARCHAR(255) NOT NULL, location VARCHAR(255) NOT NULL, date_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , type VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, CONSTRAINT FK_FE38F8446B899279 FOREIGN KEY (patient_id) REFERENCES patient (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_FE38F8446B899279 ON appointment (patient_id)');
        $this->addSql('CREATE TABLE attendance (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, patient_id INTEGER NOT NULL, check_in_user_id INTEGER NOT NULL, check_out_user_id INTEGER DEFAULT NULL, check_in_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , check_out_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , tag INTEGER NOT NULL, evidence VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_6DE30D916B899279 FOREIGN KEY (patient_id) REFERENCES patient (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6DE30D917713D579 FOREIGN KEY (check_in_user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6DE30D914D154D9F FOREIGN KEY (check_out_user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_6DE30D916B899279 ON attendance (patient_id)');
        $this->addSql('CREATE INDEX IDX_6DE30D917713D579 ON attendance (check_in_user_id)');
        $this->addSql('CREATE INDEX IDX_6DE30D914D154D9F ON attendance (check_out_user_id)');
        $this->addSql('CREATE TABLE hospitalized (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, patient_id INTEGER NOT NULL, service VARCHAR(255) NOT NULL, bed VARCHAR(255) NOT NULL, CONSTRAINT FK_7E8E259E6B899279 FOREIGN KEY (patient_id) REFERENCES patient (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7E8E259E6B899279 ON hospitalized (patient_id)');
        $this->addSql('CREATE TABLE patient (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, file VARCHAR(12) NOT NULL, name VARCHAR(255) NOT NULL, disability BOOLEAN NOT NULL)');
        $this->addSql('CREATE TABLE stakeholder (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, check_in_user_id INTEGER NOT NULL, check_out_user_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, dni VARCHAR(255) NOT NULL, tag INTEGER NOT NULL, company VARCHAR(255) NOT NULL, destination VARCHAR(255) NOT NULL, subject VARCHAR(255) NOT NULL, check_in_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , check_out_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , evidence VARCHAR(255) DEFAULT NULL, sign VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_8B9823AA7713D579 FOREIGN KEY (check_in_user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_8B9823AA4D154D9F FOREIGN KEY (check_out_user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_8B9823AA7713D579 ON stakeholder (check_in_user_id)');
        $this->addSql('CREATE INDEX IDX_8B9823AA4D154D9F ON stakeholder (check_out_user_id)');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME ON user (username)');
        $this->addSql('CREATE TABLE visitor (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, check_in_user_id INTEGER NOT NULL, check_out_user_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, phone VARCHAR(255) DEFAULT NULL, dni VARCHAR(255) NOT NULL, tag INTEGER NOT NULL, destination VARCHAR(255) NOT NULL, check_in_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , check_out_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , relationship VARCHAR(255) DEFAULT NULL, evidence VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_CAE5E19F7713D579 FOREIGN KEY (check_in_user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CAE5E19F4D154D9F FOREIGN KEY (check_out_user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_CAE5E19F7713D579 ON visitor (check_in_user_id)');
        $this->addSql('CREATE INDEX IDX_CAE5E19F4D154D9F ON visitor (check_out_user_id)');
        $this->addSql('CREATE TABLE visitor_patient (visitor_id INTEGER NOT NULL, patient_id INTEGER NOT NULL, PRIMARY KEY(visitor_id, patient_id), CONSTRAINT FK_D8C9472370BEE6D FOREIGN KEY (visitor_id) REFERENCES visitor (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D8C947236B899279 FOREIGN KEY (patient_id) REFERENCES patient (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_D8C9472370BEE6D ON visitor_patient (visitor_id)');
        $this->addSql('CREATE INDEX IDX_D8C947236B899279 ON visitor_patient (patient_id)');
        $this->addSql('CREATE TABLE messenger_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, body CLOB NOT NULL, headers CLOB NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , available_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , delivered_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        )');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE appointment');
        $this->addSql('DROP TABLE attendance');
        $this->addSql('DROP TABLE hospitalized');
        $this->addSql('DROP TABLE patient');
        $this->addSql('DROP TABLE stakeholder');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE visitor');
        $this->addSql('DROP TABLE visitor_patient');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
