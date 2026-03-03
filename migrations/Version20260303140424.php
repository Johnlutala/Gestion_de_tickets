<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260303140424 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, profile_id INT NOT NULL, INDEX IDX_8D93D649CCFA12B8 (profile_id), UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649CCFA12B8 FOREIGN KEY (profile_id) REFERENCES tb_role (id)');
        $this->addSql('DROP TABLE tb_user');
        $this->addSql('ALTER TABLE tb_application CHANGE id id BIGINT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE role_privilege ADD CONSTRAINT FK_D6D4495BD60322AC FOREIGN KEY (role_id) REFERENCES tb_role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role_privilege ADD CONSTRAINT FK_D6D4495B32FB8AEA FOREIGN KEY (privilege_id) REFERENCES tb_privilege (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX IDX_2A77B4EF9CD0792D ON tb_ticket');
        $this->addSql('DROP INDEX IDX_2A77B4EF9D86650F ON tb_ticket');
        $this->addSql('ALTER TABLE tb_ticket ADD application_id BIGINT NOT NULL, ADD user_id INT NOT NULL, DROP application_id_id, DROP user_id_id, CHANGE id id BIGINT AUTO_INCREMENT NOT NULL, CHANGE description description LONGTEXT NOT NULL, CHANGE note note INT NOT NULL, CHANGE comment comment LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE tb_ticket ADD CONSTRAINT FK_2A77B4EF3E030ACD FOREIGN KEY (application_id) REFERENCES tb_application (id)');
        $this->addSql('ALTER TABLE tb_ticket ADD CONSTRAINT FK_2A77B4EFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_2A77B4EF3E030ACD ON tb_ticket (application_id)');
        $this->addSql('CREATE INDEX IDX_2A77B4EFA76ED395 ON tb_ticket (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tb_user (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, username VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, password VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, profile_id INT DEFAULT NULL, INDEX IDX_D6E3D458CCFA12B8 (profile_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649CCFA12B8');
        $this->addSql('DROP TABLE user');
        $this->addSql('ALTER TABLE role_privilege DROP FOREIGN KEY FK_D6D4495BD60322AC');
        $this->addSql('ALTER TABLE role_privilege DROP FOREIGN KEY FK_D6D4495B32FB8AEA');
        $this->addSql('ALTER TABLE tb_application CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE tb_ticket DROP FOREIGN KEY FK_2A77B4EF3E030ACD');
        $this->addSql('ALTER TABLE tb_ticket DROP FOREIGN KEY FK_2A77B4EFA76ED395');
        $this->addSql('DROP INDEX IDX_2A77B4EF3E030ACD ON tb_ticket');
        $this->addSql('DROP INDEX IDX_2A77B4EFA76ED395 ON tb_ticket');
        $this->addSql('ALTER TABLE tb_ticket ADD user_id_id INT NOT NULL, DROP application_id, CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE description description VARCHAR(255) NOT NULL, CHANGE note note VARCHAR(255) DEFAULT NULL, CHANGE comment comment VARCHAR(255) DEFAULT NULL, CHANGE user_id application_id_id INT NOT NULL');
        $this->addSql('CREATE INDEX IDX_2A77B4EF9CD0792D ON tb_ticket (application_id_id)');
        $this->addSql('CREATE INDEX IDX_2A77B4EF9D86650F ON tb_ticket (user_id_id)');
    }
}
