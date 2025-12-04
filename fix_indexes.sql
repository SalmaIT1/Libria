-- Fix indexes for MariaDB compatibility
-- Drop old indexes and create new ones

-- Fix commande table indexes
DROP INDEX uniq_4a5b03785e237e06 ON commande;
CREATE UNIQUE INDEX UNIQ_6EEAA67DAEA34913 ON commande (reference);

DROP INDEX idx_4a5b0378a76ed395 ON commande;
CREATE INDEX IDX_6EEAA67DA76ED395 ON commande (user_id);

-- Fix ligne_commande table indexes
DROP INDEX idx_9a8a31c88959f0f8 ON ligne_commande;
CREATE INDEX IDX_3170B74B82EA2E54 ON ligne_commande (commande_id);

DROP INDEX idx_9a8a31c86a983o06 ON ligne_commande;
CREATE INDEX IDX_3170B74B37D925CB ON ligne_commande (livre_id);

-- Fix panier table indexes
DROP INDEX uniq_b86f2ba5a76ed395 ON panier;
CREATE UNIQUE INDEX UNIQ_24CC0DF2A76ED395 ON panier (user_id);

-- Fix ligne_panier table indexes
DROP INDEX idx_9a8a31c8b881cco0 ON ligne_panier;
CREATE INDEX IDX_21691B4F77D927C ON ligne_panier (panier_id);

DROP INDEX idx_9a8a31c86a983o06 ON ligne_panier;
CREATE INDEX IDX_21691B437D925CB ON ligne_panier (livre_id);
