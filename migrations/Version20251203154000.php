<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251203154000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add coupon support to cart and orders';
    }

    public function up(Schema $schema): void
    {
        // Add coupon fields to commande table
        $this->addSql('ALTER TABLE commande ADD coupon_code VARCHAR(20) DEFAULT NULL, ADD discount_amount NUMERIC(10, 2) DEFAULT NULL');
        
        // Add coupon relationship to panier table
        $this->addSql('ALTER TABLE panier ADD coupon_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE panier ADD CONSTRAINT FK_24CC0DF266C5951B FOREIGN KEY (coupon_id) REFERENCES coupon (id)');
        $this->addSql('CREATE INDEX IDX_24CC0DF266C5951B ON panier (coupon_id)');
    }

    public function down(Schema $schema): void
    {
        // Remove coupon fields from commande table
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DA76ED395');
        $this->addSql('ALTER TABLE commande DROP coupon_code, DROP discount_amount');
        
        // Remove coupon relationship from panier table
        $this->addSql('ALTER TABLE panier DROP FOREIGN KEY FK_24CC0DF266C5951B');
        $this->addSql('DROP INDEX IDX_24CC0DF266C5951B ON panier');
        $this->addSql('ALTER TABLE panier DROP coupon_id');
    }
}
