<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251203000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add facture table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE facture (id INT AUTO_INCREMENT NOT NULL, numero VARCHAR(30) NOT NULL, created_at DATETIME NOT NULL, montant_ht NUMERIC(10, 2) NOT NULL, montant_tva NUMERIC(10, 2) NOT NULL, montant_ttc NUMERIC(10, 2) NOT NULL, file_path VARCHAR(255) DEFAULT NULL, is_paid TINYINT(1) NOT NULL, commande_id INT NOT NULL, UNIQUE INDEX UNIQ_FE866410F55AE19E (numero), INDEX IDX_FE86641082EA2E54 (commande_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE facture ADD CONSTRAINT FK_FE86641082EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE facture');
    }
}
