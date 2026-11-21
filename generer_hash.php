<?php
// TP6 act.3 : générer un hash en ligne de commande : php generer_hash.php MotDePasse
$motDePasse = $argv[1] ?? "Admin@2025";
echo password_hash($motDePasse, PASSWORD_DEFAULT), PHP_EOL;
