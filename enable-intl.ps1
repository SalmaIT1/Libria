# Script PowerShell pour activer l'extension PHP Intl
$phpIniPath = "C:\xampp\php\php.ini"

if (-not (Test-Path $phpIniPath)) {
    Write-Host "Erreur: Le fichier php.ini n'a pas été trouvé à: $phpIniPath" -ForegroundColor Red
    Write-Host "Veuillez modifier manuellement votre fichier php.ini" -ForegroundColor Yellow
    exit 1
}

Write-Host "Activation de l'extension PHP Intl..." -ForegroundColor Green

# Lire le contenu du fichier
$content = Get-Content $phpIniPath -Raw

# Remplacer ;extension=intl par extension=intl
$content = $content -replace ';extension=intl', 'extension=intl'

# Sauvegarder le fichier
Set-Content -Path $phpIniPath -Value $content -NoNewline

Write-Host "Extension Intl activée avec succès!" -ForegroundColor Green
Write-Host ""
Write-Host "IMPORTANT: Vous devez redémarrer votre serveur web (Apache) pour que les changements prennent effet." -ForegroundColor Yellow
Write-Host "Dans XAMPP Control Panel, cliquez sur 'Stop' puis 'Start' pour Apache." -ForegroundColor Yellow

