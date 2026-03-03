<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260303121731 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE role_privilege (role_id INT NOT NULL, privilege_id INT NOT NULL, INDEX IDX_D6D4495BD60322AC (role_id), INDEX IDX_D6D4495B32FB8AEA (privilege_id), PRIMARY KEY (role_id, privilege_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE role_privilege ADD CONSTRAINT FK_D6D4495BD60322AC FOREIGN KEY (role_id) REFERENCES tb_role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role_privilege ADD CONSTRAINT FK_D6D4495B32FB8AEA FOREIGN KEY (privilege_id) REFERENCES tb_privilege (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tb_ticket ADD application_id_id INT NOT NULL, ADD user_id_id INT NOT NULL, DROP application_id, DROP user_id');
        $this->addSql('ALTER TABLE tb_ticket ADD CONSTRAINT FK_2A77B4EF9CD0792D FOREIGN KEY (application_id_id) REFERENCES tb_application (id)');
        $this->addSql('ALTER TABLE tb_ticket ADD CONSTRAINT FK_2A77B4EF9D86650F FOREIGN KEY (user_id_id) REFERENCES tb_user (id)');
        $this->addSql('CREATE INDEX IDX_2A77B4EF9CD0792D ON tb_ticket (application_id_id)');
        $this->addSql('CREATE INDEX IDX_2A77B4EF9D86650F ON tb_ticket (user_id_id)');
        $this->addSql('ALTER TABLE tb_user ADD profile_id INT DEFAULT NULL, DROP profile');
        $this->addSql('ALTER TABLE tb_user ADD CONSTRAINT FK_D6E3D458CCFA12B8 FOREIGN KEY (profile_id) REFERENCES tb_role (id)');
        $this->addSql('CREATE INDEX IDX_D6E3D458CCFA12B8 ON tb_user (profile_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE role_privilege DROP FOREIGN KEY FK_D6D4495BD60322AC');
        $this->addSql('ALTER TABLE role_privilege DROP FOREIGN KEY FK_D6D4495B32FB8AEA');
        $this->addSql('DROP TABLE role_privilege');
        $this->addSql('ALTER TABLE tb_ticket DROP FOREIGN KEY FK_2A77B4EF9CD0792D');
        $this->addSql('ALTER TABLE tb_ticket DROP FOREIGN KEY FK_2A77B4EF9D86650F');
        $this->addSql('DROP INDEX IDX_2A77B4EF9CD0792D ON tb_ticket');
        $this->addSql('DROP INDEX IDX_2A77B4EF9D86650F ON tb_ticket');
        $this->addSql('ALTER TABLE tb_ticket ADD application_id INT NOT NULL, ADD user_id INT NOT NULL, DROP application_id_id, DROP user_id_id');
        $this->addSql('ALTER TABLE tb_user DROP FOREIGN KEY FK_D6E3D458CCFA12B8');
        $this->addSql('DROP INDEX IDX_D6E3D458CCFA12B8 ON tb_user');
        $this->addSql('ALTER TABLE tb_user ADD profile VARCHAR(255) NOT NULL, DROP profile_id');
    }
}
