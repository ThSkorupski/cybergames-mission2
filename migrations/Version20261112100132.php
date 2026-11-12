<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261112100132 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Modèle initial : user, categorie_machine, machine, jeu, forfait, voucher';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE categorie_machine (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_4A867126A4D60759 (libelle), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE forfait (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, duree_minutes INT NOT NULL, prix NUMERIC(6, 2) NOT NULL, actif TINYINT NOT NULL, categorie_id INT NOT NULL, INDEX IDX_BBB5C482BCF5E72D (categorie_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE jeu (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(150) NOT NULL, editeur VARCHAR(100) DEFAULT NULL, UNIQUE INDEX UNIQ_82E48DB5FF7747B4 (titre), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE machine (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, statut VARCHAR(20) NOT NULL, categorie_id INT NOT NULL, UNIQUE INDEX UNIQ_1505DF846C6E55B5 (nom), INDEX IDX_1505DF84BCF5E72D (categorie_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE machine_jeu (machine_id INT NOT NULL, jeu_id INT NOT NULL, INDEX IDX_3BCBD780F6B75B26 (machine_id), INDEX IDX_3BCBD7808C9E392E (jeu_id), PRIMARY KEY (machine_id, jeu_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, pseudo VARCHAR(50) DEFAULT NULL, actif TINYINT NOT NULL, date_creation DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D64986CC499D (pseudo), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE voucher (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(20) NOT NULL, duree_minutes INT NOT NULL, date_attribution DATETIME NOT NULL, date_expiration DATETIME DEFAULT NULL, date_debut DATETIME DEFAULT NULL, date_fin DATETIME DEFAULT NULL, client_id INT NOT NULL, forfait_id INT NOT NULL, categorie_id INT NOT NULL, attribue_par_id INT NOT NULL, machine_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_1392A5D877153098 (code), INDEX idx_voucher_machine_fin (machine_id, date_fin), INDEX IDX_1392A5D819EB6921 (client_id), INDEX IDX_1392A5D8906D5F2C (forfait_id), INDEX IDX_1392A5D8BCF5E72D (categorie_id), INDEX IDX_1392A5D893864229 (attribue_par_id), INDEX IDX_1392A5D8F6B75B26 (machine_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id))');
        $this->addSql('ALTER TABLE forfait ADD CONSTRAINT FK_BBB5C482BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie_machine (id)');
        $this->addSql('ALTER TABLE machine ADD CONSTRAINT FK_1505DF84BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie_machine (id)');
        $this->addSql('ALTER TABLE machine_jeu ADD CONSTRAINT FK_3BCBD780F6B75B26 FOREIGN KEY (machine_id) REFERENCES machine (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE machine_jeu ADD CONSTRAINT FK_3BCBD7808C9E392E FOREIGN KEY (jeu_id) REFERENCES jeu (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE voucher ADD CONSTRAINT FK_1392A5D819EB6921 FOREIGN KEY (client_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE voucher ADD CONSTRAINT FK_1392A5D8906D5F2C FOREIGN KEY (forfait_id) REFERENCES forfait (id)');
        $this->addSql('ALTER TABLE voucher ADD CONSTRAINT FK_1392A5D8BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie_machine (id)');
        $this->addSql('ALTER TABLE voucher ADD CONSTRAINT FK_1392A5D893864229 FOREIGN KEY (attribue_par_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE voucher ADD CONSTRAINT FK_1392A5D8F6B75B26 FOREIGN KEY (machine_id) REFERENCES machine (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE forfait DROP FOREIGN KEY FK_BBB5C482BCF5E72D');
        $this->addSql('ALTER TABLE machine DROP FOREIGN KEY FK_1505DF84BCF5E72D');
        $this->addSql('ALTER TABLE machine_jeu DROP FOREIGN KEY FK_3BCBD780F6B75B26');
        $this->addSql('ALTER TABLE machine_jeu DROP FOREIGN KEY FK_3BCBD7808C9E392E');
        $this->addSql('ALTER TABLE voucher DROP FOREIGN KEY FK_1392A5D819EB6921');
        $this->addSql('ALTER TABLE voucher DROP FOREIGN KEY FK_1392A5D8906D5F2C');
        $this->addSql('ALTER TABLE voucher DROP FOREIGN KEY FK_1392A5D8BCF5E72D');
        $this->addSql('ALTER TABLE voucher DROP FOREIGN KEY FK_1392A5D893864229');
        $this->addSql('ALTER TABLE voucher DROP FOREIGN KEY FK_1392A5D8F6B75B26');
        $this->addSql('DROP TABLE categorie_machine');
        $this->addSql('DROP TABLE forfait');
        $this->addSql('DROP TABLE jeu');
        $this->addSql('DROP TABLE machine');
        $this->addSql('DROP TABLE machine_jeu');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE voucher');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
