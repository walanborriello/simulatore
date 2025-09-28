<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250928133557 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE simulations (id INT AUTO_INCREMENT NOT NULL, student_id INT NOT NULL, cdl VARCHAR(50) NOT NULL, inputData JSON NOT NULL, resultData JSON NOT NULL, userToken VARCHAR(255) DEFAULT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, INDEX IDX_DC12BDF9CB944F1A (student_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE students_prospective (id INT AUTO_INCREMENT NOT NULL, firstName VARCHAR(100) NOT NULL, lastName VARCHAR(100) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(50) DEFAULT NULL, notes LONGTEXT DEFAULT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE simulations ADD CONSTRAINT FK_DC12BDF9CB944F1A FOREIGN KEY (student_id) REFERENCES students_prospective (id)');
        $this->addSql('DROP TABLE zcfu_CDL');
        $this->addSql('DROP TABLE zcfu_dis');
        $this->addSql('DROP TABLE zcfu_offerta');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE zcfu_CDL (ID INT AUTO_INCREMENT NOT NULL, CDL VARCHAR(255) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, ID_ORI SMALLINT DEFAULT NULL, Orient VARCHAR(255) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, INDEX CDL (CDL), PRIMARY KEY(ID)) DEFAULT CHARACTER SET latin1 COLLATE `latin1_swedish_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE zcfu_dis (DIS_ID INT NOT NULL, disciplina VARCHAR(255) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, ssd VARCHAR(255) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, PRIMARY KEY(DIS_ID)) DEFAULT CHARACTER SET latin1 COLLATE `latin1_swedish_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE zcfu_offerta (OFF_ID INT AUTO_INCREMENT NOT NULL, ORI_ID SMALLINT DEFAULT 0, DIS_ID INT NOT NULL, rosa SMALLINT DEFAULT NULL, maxCFU SMALLINT DEFAULT NULL, TAF VARCHAR(2) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, CFU TINYINT(1) DEFAULT NULL, ANNO TINYINT(1) DEFAULT NULL, AA VARCHAR(10) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, CDL VARCHAR(10) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, INDEX FK_offerta_did_discipline (DIS_ID), INDEX FK_offerta_did_orientamenti (ORI_ID), PRIMARY KEY(OFF_ID)) DEFAULT CHARACTER SET latin1 COLLATE `latin1_swedish_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE simulations DROP FOREIGN KEY FK_DC12BDF9CB944F1A');
        $this->addSql('DROP TABLE simulations');
        $this->addSql('DROP TABLE students_prospective');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
