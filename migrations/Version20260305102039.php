<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260305102039 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tb_user DROP FOREIGN KEY `FK_D6E3D458CCFA12B8`');
        $this->addSql('DROP TABLE tb_user');
        $this->addSql('ALTER TABLE tb_application CHANGE id id BIGINT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE tb_ticket DROP FOREIGN KEY `FK_2A77B4EF9CD0792D`');
        $this->addSql('ALTER TABLE tb_ticket DROP FOREIGN KEY `FK_2A77B4EF9D86650F`');
        $this->addSql('DROP INDEX IDX_2A77B4EF9CD0792D ON tb_ticket');
        $this->addSql('DROP INDEX IDX_2A77B4EF9D86650F ON tb_ticket');
        $this->addSql('ALTER TABLE tb_ticket ADD enabled TINYINT NOT NULL, ADD deleted TINYINT NOT NULL, ADD year INT DEFAULT NULL, ADD month INT DEFAULT NULL, ADD quarter INT DEFAULT NULL, ADD application_id BIGINT NOT NULL, ADD user_id INT NOT NULL, ADD createdby_id INT DEFAULT NULL, DROP application_id_id, DROP user_id_id, CHANGE id id BIGINT AUTO_INCREMENT NOT NULL, CHANGE description description LONGTEXT NOT NULL, CHANGE note note INT NOT NULL, CHANGE comment comment LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE tb_ticket ADD CONSTRAINT FK_2A77B4EF3E030ACD FOREIGN KEY (application_id) REFERENCES tb_application (id)');
        $this->addSql('ALTER TABLE tb_ticket ADD CONSTRAINT FK_2A77B4EFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tb_ticket ADD CONSTRAINT FK_2A77B4EFF0B5AF0B FOREIGN KEY (createdby_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_2A77B4EF3E030ACD ON tb_ticket (application_id)');
        $this->addSql('CREATE INDEX IDX_2A77B4EFA76ED395 ON tb_ticket (user_id)');
        $this->addSql('CREATE INDEX IDX_2A77B4EFF0B5AF0B ON tb_ticket (createdby_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tb_user (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, username VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, password VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, profile_id INT DEFAULT NULL, INDEX IDX_D6E3D458CCFA12B8 (profile_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE tb_user ADD CONSTRAINT `FK_D6E3D458CCFA12B8` FOREIGN KEY (profile_id) REFERENCES tb_role (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE tb_application CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE tb_ticket DROP FOREIGN KEY FK_2A77B4EF3E030ACD');
        $this->addSql('ALTER TABLE tb_ticket DROP FOREIGN KEY FK_2A77B4EFA76ED395');
        $this->addSql('ALTER TABLE tb_ticket DROP FOREIGN KEY FK_2A77B4EFF0B5AF0B');
        $this->addSql('DROP INDEX IDX_2A77B4EF3E030ACD ON tb_ticket');
        $this->addSql('DROP INDEX IDX_2A77B4EFA76ED395 ON tb_ticket');
        $this->addSql('DROP INDEX IDX_2A77B4EFF0B5AF0B ON tb_ticket');
        $this->addSql('ALTER TABLE tb_ticket ADD user_id_id INT NOT NULL, DROP enabled, DROP deleted, DROP year, DROP month, DROP quarter, DROP application_id, DROP createdby_id, CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE description description VARCHAR(255) NOT NULL, CHANGE note note VARCHAR(255) DEFAULT NULL, CHANGE comment comment VARCHAR(255) DEFAULT NULL, CHANGE user_id application_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE tb_ticket ADD CONSTRAINT `FK_2A77B4EF9CD0792D` FOREIGN KEY (application_id_id) REFERENCES tb_application (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE tb_ticket ADD CONSTRAINT `FK_2A77B4EF9D86650F` FOREIGN KEY (user_id_id) REFERENCES tb_user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_2A77B4EF9CD0792D ON tb_ticket (application_id_id)');
        $this->addSql('CREATE INDEX IDX_2A77B4EF9D86650F ON tb_ticket (user_id_id)');
    }
}
