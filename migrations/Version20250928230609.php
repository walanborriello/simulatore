<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250928230609 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE students_prospective ADD managedBy VARCHAR(255) DEFAULT NULL');
        $this->addSql('DROP INDEX CDL ON zcfu_CDL');
        $this->addSql('ALTER TABLE zcfu_CDL CHANGE ID_ORI ID_ORI INT DEFAULT NULL');
        $this->addSql('ALTER TABLE zcfu_dis CHANGE DIS_ID DIS_ID INT AUTO_INCREMENT NOT NULL');
        $this->addSql('DROP INDEX FK_offerta_did_discipline ON zcfu_offerta');
        $this->addSql('DROP INDEX FK_offerta_did_orientamenti ON zcfu_offerta');
        $this->addSql('ALTER TABLE zcfu_offerta CHANGE ORI_ID ORI_ID INT DEFAULT NULL, CHANGE DIS_ID DIS_ID INT DEFAULT NULL, CHANGE rosa rosa INT DEFAULT NULL, CHANGE maxCFU maxCFU INT DEFAULT NULL, CHANGE TAF TAF VARCHAR(255) DEFAULT NULL, CHANGE CFU CFU INT DEFAULT NULL, CHANGE ANNO ANNO INT DEFAULT NULL, CHANGE AA AA VARCHAR(255) DEFAULT NULL, CHANGE CDL CDL VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE zcfu_regole CHANGE priorita priorita INT NOT NULL, ADD PRIMARY KEY (ID_off, ID_ric)');
        $this->addSql('ALTER TABLE zcfu_riconoscibile CHANGE ID_ric ID_ric INT AUTO_INCREMENT NOT NULL, CHANGE CDL CDL VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE zcfu_riconoscibile CHANGE ID_ric ID_ric INT NOT NULL, CHANGE CDL CDL VARCHAR(20) DEFAULT NULL');
        $this->addSql('DROP INDEX `primary` ON zcfu_regole');
        $this->addSql('ALTER TABLE zcfu_regole CHANGE priorita priorita TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE zcfu_offerta CHANGE ORI_ID ORI_ID SMALLINT DEFAULT 0, CHANGE DIS_ID DIS_ID INT NOT NULL, CHANGE rosa rosa SMALLINT DEFAULT NULL, CHANGE maxCFU maxCFU SMALLINT DEFAULT NULL, CHANGE TAF TAF VARCHAR(2) DEFAULT NULL, CHANGE CFU CFU TINYINT(1) DEFAULT NULL, CHANGE ANNO ANNO TINYINT(1) DEFAULT NULL, CHANGE AA AA VARCHAR(10) DEFAULT NULL, CHANGE CDL CDL VARCHAR(10) DEFAULT NULL');
        $this->addSql('CREATE INDEX FK_offerta_did_discipline ON zcfu_offerta (DIS_ID)');
        $this->addSql('CREATE INDEX FK_offerta_did_orientamenti ON zcfu_offerta (ORI_ID)');
        $this->addSql('ALTER TABLE zcfu_dis CHANGE DIS_ID DIS_ID INT NOT NULL');
        $this->addSql('ALTER TABLE zcfu_CDL CHANGE ID_ORI ID_ORI SMALLINT DEFAULT NULL');
        $this->addSql('CREATE INDEX CDL ON zcfu_CDL (CDL)');
        $this->addSql('ALTER TABLE students_prospective DROP managedBy');
    }
}
