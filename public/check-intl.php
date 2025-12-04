<?php
/**
 * Script de vérification de l'extension Intl
 * Accédez à ce fichier via: http://127.0.0.1:8000/check-intl.php
 */

echo "<h1>Vérification de l'extension PHP Intl</h1>";
echo "<hr>";

if (extension_loaded('intl')) {
    echo "<p style='color: green; font-weight: bold;'>✓ Extension Intl est CHARGÉE</p>";
    echo "<p>Version Intl: " . INTL_ICU_VERSION . "</p>";
    echo "<p>ICU Version: " . INTL_ICU_DATA_VERSION . "</p>";
    echo "<p style='color: green;'>Tout est correct! EasyAdmin devrait fonctionner.</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>✗ Extension Intl N'EST PAS chargée</p>";
    echo "<h2>Solution:</h2>";
    echo "<ol>";
    echo "<li>Ouvrez le fichier: <code>C:\\xampp\\php\\php.ini</code></li>";
    echo "<li>Recherchez la ligne: <code>;extension=intl</code></li>";
    echo "<li>Décommentez-la en enlevant le point-virgule: <code>extension=intl</code></li>";
    echo "<li><strong>REDÉMARREZ Apache dans XAMPP Control Panel</strong></li>";
    echo "<li>Ou redémarrez le serveur Symfony</li>";
    echo "</ol>";
}

echo "<hr>";
echo "<p><a href='/'>Retour à l'accueil</a> | <a href='/admin'>Admin Dashboard</a></p>";

