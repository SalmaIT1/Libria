<?php
// Test script pour vérifier l'extension Intl dans le contexte web
phpinfo();
echo "<hr>";
echo "Extension Intl chargée: " . (extension_loaded('intl') ? 'OUI' : 'NON') . "<br>";
if (extension_loaded('intl')) {
    echo "Version Intl: " . INTL_ICU_VERSION . "<br>";
    echo "ICU Version: " . INTL_ICU_DATA_VERSION . "<br>";
} else {
    echo "ERREUR: L'extension Intl n'est pas chargée!<br>";
    echo "Veuillez redémarrer votre serveur web (Apache/XAMPP).<br>";
}

