<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260309100731 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE role_privilege ADD CONSTRAINT FK_D6D4495BD60322AC FOREIGN KEY (role_id) REFERENCES tb_role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role_privilege ADD CONSTRAINT FK_D6D4495B32FB8AEA FOREIGN KEY (privilege_id) REFERENCES tb_privilege (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tb_ticket ADD created_at DATETIME DEFAULT NULL, ADD parent_id BIGINT DEFAULT NULL, CHANGE application_id application_id BIGINT DEFAULT NULL, CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tb_ticket ADD CONSTRAINT FK_2A77B4EF3E030ACD FOREIGN KEY (application_id) REFERENCES tb_application (id)');
        $this->addSql('ALTER TABLE tb_ticket ADD CONSTRAINT FK_2A77B4EFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tb_ticket ADD CONSTRAINT FK_2A77B4EFF0B5AF0B FOREIGN KEY (createdby_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tb_ticket ADD CONSTRAINT FK_2A77B4EF727ACA70 FOREIGN KEY (parent_id) REFERENCES tb_ticket (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_2A77B4EF727ACA70 ON tb_ticket (parent_id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649CCFA12B8 FOREIGN KEY (profile_id) REFERENCES tb_role (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE role_privilege DROP FOREIGN KEY FK_D6D4495BD60322AC');
        $this->addSql('ALTER TABLE role_privilege DROP FOREIGN KEY FK_D6D4495B32FB8AEA');
        $this->addSql('ALTER TABLE tb_ticket DROP FOREIGN KEY FK_2A77B4EF3E030ACD');
        $this->addSql('ALTER TABLE tb_ticket DROP FOREIGN KEY FK_2A77B4EFA76ED395');
        $this->addSql('ALTER TABLE tb_ticket DROP FOREIGN KEY FK_2A77B4EFF0B5AF0B');
        $this->addSql('ALTER TABLE tb_ticket DROP FOREIGN KEY FK_2A77B4EF727ACA70');
        $this->addSql('DROP INDEX IDX_2A77B4EF727ACA70 ON tb_ticket');
        $this->addSql('ALTER TABLE tb_ticket DROP created_at, DROP parent_id, CHANGE application_id application_id BIGINT NOT NULL, CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649CCFA12B8');
    }
}
