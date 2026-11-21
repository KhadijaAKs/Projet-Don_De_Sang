<?php
// À exécuter UNE SEULE FOIS après database.sql, puis à SUPPRIMER.
// Crée les 3 utilisateurs avec mots de passe hachés (TP4 act.16 : password_hash)
require_once __DIR__ . '/config/connexion.php';

$n = (int) $pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
if ($n > 0) {
    exit("Les utilisateurs existent déjà. Supprimez setup.php.");
}

$users = [
    ['admin',      'Administrateur',   'Admin@2025',      'ADMIN'],
    ['medecin',    'Dr. Médecin',      'Medecin@2025',    'MEDECIN'],
    ['secretaire', 'Secrétaire',       'Secret@2025', 'SECRETAIRE'],
];

$stmt = $pdo->prepare("INSERT INTO utilisateurs (nom_utilisateur, nom_complet, mot_de_passe, role, id_centre)
                       VALUES (:u, :n, :p, :r, 1)");
foreach ($users as [$u, $nom, $mdp, $role]) {
    $stmt->execute(['u' => $u, 'n' => $nom, 'p' => password_hash($mdp, PASSWORD_DEFAULT), 'r' => $role]);
}
echo "Utilisateurs créés. Supprimez maintenant setup.php. <a href='login.php'>Connexion</a>";
