<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250929082807 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE simulations (id INT AUTO_INCREMENT NOT NULL, studentId INT NOT NULL, cdl VARCHAR(10) NOT NULL, inputData JSON NOT NULL, detailResults JSON NOT NULL, summaryResults JSON NOT NULL, leftoverResults JSON NOT NULL, totalCfuRecognized INT NOT NULL, totalCfuRequired INT NOT NULL, totalCfuIntegrative INT NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, managedBy VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE simulations');
    }
}
