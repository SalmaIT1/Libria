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
        'ALTER TABLE auteur CHANGE prenom prenom VARCHAR(255) DEFAULT NULL',
        'ALTER TABLE editeur CHANGE pays pays VARCHAR(255) DEFAULT NULL, CHANGE adresse adresse VARCHAR(255) DEFAULT NULL, CHANGE telephone telephone VARCHAR(255) DEFAULT NULL',
        'ALTER TABLE emprunt CHANGE date_retour_prevu date_retour_prevu DATETIME DEFAULT NULL, CHANGE date_retour_effectif date_retour_effectif DATETIME DEFAULT NULL',
        'ALTER TABLE livre CHANGE date_edition date_edition DATE DEFAULT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL',
        'ALTER TABLE notification CHANGE lien lien VARCHAR(255) DEFAULT NULL',
        'ALTER TABLE `user` CHANGE roles roles JSON NOT NULL, CHANGE first_name first_name VARCHAR(255) DEFAULT NULL, CHANGE last_name last_name VARCHAR(255) DEFAULT NULL, CHANGE avatar avatar VARCHAR(255) DEFAULT NULL',
        'ALTER TABLE commande CHANGE paid_at paid_at DATETIME DEFAULT NULL, CHANGE shipped_at shipped_at DATETIME DEFAULT NULL, CHANGE delivered_at delivered_at DATETIME DEFAULT NULL, CHANGE shipping_cost shipping_cost NUMERIC(10, 2) DEFAULT NULL, CHANGE payment_method payment_method VARCHAR(255) DEFAULT NULL, CHANGE payment_intent_id payment_intent_id VARCHAR(255) DEFAULT NULL, CHANGE tracking_number tracking_number VARCHAR(255) DEFAULT NULL',
        'ALTER TABLE ligne_panier ADD CONSTRAINT FK_21691B437D925CB FOREIGN KEY (livre_id) REFERENCES livre (id)',
    ];

    foreach ($sqlCommands as $sql) {
        echo "Executing: $sql\n";
        $pdo->exec($sql);
        echo "Success!\n";
    }

    echo "\nSchema fixed successfully!\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
