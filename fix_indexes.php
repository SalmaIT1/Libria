<?php

require_once 'vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load('.env');

$host = $_ENV['DATABASE_HOST'] ?? '127.0.0.1';
$port = $_ENV['DATABASE_PORT'] ?? '3306';
$dbname = $_ENV['DATABASE_NAME'] ?? 'libria';
$user = $_ENV['DATABASE_USER'] ?? 'root';
$password = $_ENV['DATABASE_PASSWORD'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to database successfully\n";

    $sqlCommands = [
        'DROP INDEX IF EXISTS uniq_4a5b03785e237e06 ON commande',
        'CREATE UNIQUE INDEX UNIQ_6EEAA67DAEA34913 ON commande (reference)',
        'DROP INDEX IF EXISTS idx_4a5b0378a76ed395 ON commande',
        'CREATE INDEX IDX_6EEAA67DA76ED395 ON commande (user_id)',
        'DROP INDEX IF EXISTS idx_9a9a31c88959f0f8 ON ligne_commande',
        'CREATE INDEX IDX_3170B74B82EA2E54 ON ligne_commande (commande_id)',
        'DROP INDEX IF EXISTS idx_9a9a31c86a983o06 ON ligne_commande',
        'CREATE INDEX IDX_3170B74B37D925CB ON ligne_commande (livre_id)',
        'DROP INDEX IF EXISTS uniq_b86f2ba5a76ed395 ON panier',
        'CREATE UNIQUE INDEX UNIQ_24CC0DF2A76ED395 ON panier (user_id)',
        'DROP INDEX IF EXISTS idx_9a9a31c8b881cco0 ON ligne_panier',
        'CREATE INDEX IDX_21691B4F77D927C ON ligne_panier (panier_id)',
        'DROP INDEX IF EXISTS idx_9a9a31c86a983o06 ON ligne_panier',
        'CREATE INDEX IDX_21691B437D925CB ON ligne_panier (livre_id)',
    ];

    foreach ($sqlCommands as $sql) {
        echo "Executing: $sql\n";
        $pdo->exec($sql);
        echo "Success!\n";
    }

    echo "\nAll indexes fixed successfully!\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
