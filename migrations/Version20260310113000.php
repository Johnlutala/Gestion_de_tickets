<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260310113000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute les colonnes de pièce jointe aux tickets pour le chat';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tb_ticket ADD attachment_path VARCHAR(255) DEFAULT NULL, ADD attachment_original_name VARCHAR(255) DEFAULT NULL, ADD attachment_mime_type VARCHAR(100) DEFAULT NULL, ADD attachment_size INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tb_ticket DROP attachment_path, DROP attachment_original_name, DROP attachment_mime_type, DROP attachment_size');
    }
}
