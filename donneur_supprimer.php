<?php
// Suppression par POST uniquement (TP5 act.11) - réservé à l'ADMIN
require_once __DIR__ . '/config/connexion.php';
require_once __DIR__ . '/includes/fonctions.php';
protegerAdmin();   // ADMIN uniquement (TP6 act.9)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    try {
        $stmt = $pdo->prepare("DELETE FROM donneurs WHERE id_donneur = :id");
        $stmt->execute(['id' => $id]);
        if ($stmt->rowCount() > 0) {
            setFlash('success', "Donneur supprimé.");
        } else {
            setFlash('warning', "Aucun donneur ne correspond à cet identifiant.");
        }
    } catch (PDOException $e) {
        setFlash('danger', "Suppression impossible : ce donneur a des dons enregistrés.");
    }
}
header("Location: donneurs.php");
exit;
