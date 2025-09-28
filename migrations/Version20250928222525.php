<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250928222525 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE simulations DROP FOREIGN KEY FK_DC12BDF9CB944F1A');
        $this->addSql('ALTER TABLE simulation_log_token DROP FOREIGN KEY FK_C8334E3CFEC09103');
        $this->addSql('DROP TABLE simulations');
        $this->addSql('DROP TABLE simulation_log_token');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE simulations (id INT AUTO_INCREMENT NOT NULL, student_id INT NOT NULL, cdl VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, inputData JSON NOT NULL, resultData JSON NOT NULL, userToken VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, INDEX IDX_DC12BDF9CB944F1A (student_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE simulation_log_token (id INT AUTO_INCREMENT NOT NULL, simulation_id INT NOT NULL, oldToken VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, newToken VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, changedAt DATETIME NOT NULL, changedBy VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_C8334E3CFEC09103 (simulation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE simulations ADD CONSTRAINT FK_DC12BDF9CB944F1A FOREIGN KEY (student_id) REFERENCES students_prospective (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE simulation_log_token ADD CONSTRAINT FK_C8334E3CFEC09103 FOREIGN KEY (simulation_id) REFERENCES simulations (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
