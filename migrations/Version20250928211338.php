<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250928211338 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE students_prospective ADD codiceFiscale VARCHAR(16) NOT NULL, ADD ateneoProvenienza VARCHAR(100) NOT NULL, ADD corsoStudioInteresse VARCHAR(100) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A59E5067F4D5169D ON students_prospective (codiceFiscale)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_A59E5067F4D5169D ON students_prospective');
        $this->addSql('ALTER TABLE students_prospective DROP codiceFiscale, DROP ateneoProvenienza, DROP corsoStudioInteresse');
    }
}
