<?php
// TP5 - Activité 5 : connexion PDO (une seule fois, réutilisée partout)
$host     = 'localhost';
$dbname   = 'don_sang';
$username = 'root';          // à adapter
$password = '';              // à adapter

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // On n'affiche jamais les détails techniques (TP5 - Activité 5, question 4)
    exit("Impossible de se connecter à la base de données.");
}
