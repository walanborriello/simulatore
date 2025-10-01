<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250930125720 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Aggiorna gli stati delle azioni nella tabella students_history per conformità alla guideline aggiornata';
    }

    public function up(Schema $schema): void
    {
        // Aggiorna gli stati delle azioni per conformità alla guideline
        $this->addSql("UPDATE students_history SET action = 'create_student' WHERE action = 'create'");
        $this->addSql("UPDATE students_history SET action = 'edit_student' WHERE action = 'edit'");
        $this->addSql("UPDATE students_history SET action = 'delete_student' WHERE action = 'deleted'");
    }

    public function down(Schema $schema): void
    {
        // Ripristina gli stati originali
        $this->addSql("UPDATE students_history SET action = 'create' WHERE action = 'create_student'");
        $this->addSql("UPDATE students_history SET action = 'edit' WHERE action = 'edit_student'");
        $this->addSql("UPDATE students_history SET action = 'deleted' WHERE action = 'delete_student'");
    }
}
