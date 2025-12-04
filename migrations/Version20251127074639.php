<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251127074639 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // SQL statements generated from doctrine:schema:update --dump-sql
        // Ensure previous partial table (from an earlier failed migration) is removed first
        $this->addSql('DROP TABLE IF EXISTS stock_movement');
        $this->addSql("CREATE TABLE stock_movement (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(20) NOT NULL, quantity INT NOT NULL, stock_before INT NOT NULL, stock_after INT NOT NULL, reason VARCHAR(50) DEFAULT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, livre_id INT NOT NULL, user_id INT DEFAULT NULL, commande_id INT DEFAULT NULL, INDEX IDX_BB1BC1B537D925CB (livre_id), INDEX IDX_BB1BC1B5A76ED395 (user_id), INDEX IDX_BB1BC1B582EA2E54 (commande_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4");
        $this->addSql('ALTER TABLE stock_movement ADD CONSTRAINT FK_BB1BC1B537D925CB FOREIGN KEY (livre_id) REFERENCES livre (id)');
        $this->addSql('ALTER TABLE stock_movement ADD CONSTRAINT FK_BB1BC1B5A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE stock_movement ADD CONSTRAINT FK_BB1BC1B582EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE auteur CHANGE prenom prenom VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE commande CHANGE paid_at paid_at DATETIME DEFAULT NULL, CHANGE shipped_at shipped_at DATETIME DEFAULT NULL, CHANGE delivered_at delivered_at DATETIME DEFAULT NULL, CHANGE shipping_cost shipping_cost NUMERIC(10, 2) DEFAULT NULL, CHANGE payment_method payment_method VARCHAR(255) DEFAULT NULL, CHANGE payment_intent_id payment_intent_id VARCHAR(255) DEFAULT NULL, CHANGE tracking_number tracking_number VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE coupon CHANGE minimum_amount minimum_amount NUMERIC(10, 2) DEFAULT NULL, CHANGE expires_at expires_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE editeur CHANGE pays pays VARCHAR(255) DEFAULT NULL, CHANGE adresse adresse VARCHAR(255) DEFAULT NULL, CHANGE telephone telephone VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE emprunt CHANGE date_retour_prevu date_retour_prevu DATETIME DEFAULT NULL, CHANGE date_retour_effectif date_retour_effectif DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE livre CHANGE date_edition date_edition DATE DEFAULT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE livre_auteur DROP FOREIGN KEY `FK_A11876B537D925CB`');
        $this->addSql('ALTER TABLE livre_auteur DROP FOREIGN KEY `FK_A11876B560BB6FE6`');
        $this->addSql('ALTER TABLE livre_auteur ADD CONSTRAINT FK_A11876B537D925CB FOREIGN KEY (livre_id) REFERENCES livre (id)');
        $this->addSql('ALTER TABLE livre_auteur ADD CONSTRAINT FK_A11876B560BB6FE6 FOREIGN KEY (auteur_id) REFERENCES auteur (id)');
        $this->addSql('ALTER TABLE notification CHANGE lien lien VARCHAR(255) DEFAULT NULL');
        // Note: index renames omitted for compatibility with MySQL/MariaDB server versions.
        $this->addSql('ALTER TABLE `user` CHANGE roles roles JSON NOT NULL, CHANGE first_name first_name VARCHAR(255) DEFAULT NULL, CHANGE last_name last_name VARCHAR(255) DEFAULT NULL, CHANGE avatar avatar VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Note: reversing all changes automatically is complex. We'll drop the `stock_movement` table created by this migration.
        $this->addSql('DROP TABLE IF EXISTS stock_movement');
    }
}
