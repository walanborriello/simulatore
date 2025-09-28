<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250928135317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE simulation_log_token (id INT AUTO_INCREMENT NOT NULL, simulation_id INT NOT NULL, oldToken VARCHAR(255) NOT NULL, newToken VARCHAR(255) NOT NULL, changedAt DATETIME NOT NULL, changedBy VARCHAR(100) DEFAULT NULL, INDEX IDX_C8334E3CFEC09103 (simulation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE simulation_log_token ADD CONSTRAINT FK_C8334E3CFEC09103 FOREIGN KEY (simulation_id) REFERENCES simulations (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE simulation_log_token DROP FOREIGN KEY FK_C8334E3CFEC09103');
        $this->addSql('DROP TABLE simulation_log_token');
    }
}
