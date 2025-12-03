<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231101205820 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, statut_id INT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, adresse VARCHAR(60) NOT NULL, cp VARCHAR(6) NOT NULL, ville VARCHAR(50) NOT NULL, email VARCHAR(40) NOT NULL, nbhcompta SMALLINT NOT NULL, nbhbur SMALLINT NOT NULL, tel VARCHAR(16) DEFAULT NULL, UNIQUE INDEX UNIQ_C7440455F85E0677 (username), INDEX IDX_C7440455F6203804 (statut_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE formation (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(50) NOT NULL, niveau VARCHAR(40) NOT NULL, typeform VARCHAR(50) NOT NULL, description VARCHAR(255) DEFAULT NULL, diplomante TINYINT(1) NOT NULL, duree INT NOT NULL, coutrevient DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE inscription (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, sessionformation_id INT NOT NULL, date_inscription DATE NOT NULL, INDEX IDX_5E90F6D619EB6921 (client_id), INDEX IDX_5E90F6D6F0E484A7 (sessionformation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE plan_formation (id INT AUTO_INCREMENT NOT NULL, formation_id INT NOT NULL, client_id INT NOT NULL, effectue TINYINT(1) NOT NULL, INDEX IDX_F09EDCAA5200282E (formation_id), INDEX IDX_F09EDCAA19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE session_formation (id INT AUTO_INCREMENT NOT NULL, formation_id INT NOT NULL, date_debut DATE NOT NULL, nb_places SMALLINT NOT NULL, nb_inscrits SMALLINT DEFAULT NULL, close TINYINT(1) NOT NULL, INDEX IDX_3A264B55200282E (formation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE statut (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(40) NOT NULL, taux_horaire DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455F6203804 FOREIGN KEY (statut_id) REFERENCES statut (id)');
        $this->addSql('ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D619EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D6F0E484A7 FOREIGN KEY (sessionformation_id) REFERENCES session_formation (id)');
        $this->addSql('ALTER TABLE plan_formation ADD CONSTRAINT FK_F09EDCAA5200282E FOREIGN KEY (formation_id) REFERENCES formation (id)');
        $this->addSql('ALTER TABLE plan_formation ADD CONSTRAINT FK_F09EDCAA19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE session_formation ADD CONSTRAINT FK_3A264B55200282E FOREIGN KEY (formation_id) REFERENCES formation (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C7440455F6203804');
        $this->addSql('ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D619EB6921');
        $this->addSql('ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D6F0E484A7');
        $this->addSql('ALTER TABLE plan_formation DROP FOREIGN KEY FK_F09EDCAA5200282E');
        $this->addSql('ALTER TABLE plan_formation DROP FOREIGN KEY FK_F09EDCAA19EB6921');
        $this->addSql('ALTER TABLE session_formation DROP FOREIGN KEY FK_3A264B55200282E');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE formation');
        $this->addSql('DROP TABLE inscription');
        $this->addSql('DROP TABLE plan_formation');
        $this->addSql('DROP TABLE session_formation');
        $this->addSql('DROP TABLE statut');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
