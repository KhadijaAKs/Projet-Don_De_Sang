<?php
// Fonctions communes (TP2 act.18, TP3 act.9, TP4 act.20)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const GROUPES = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

// Échapper avant affichage (TP3 act.7, TP4 act.21) - protection XSS
function e($valeur)
{
    return htmlspecialchars((string) $valeur, ENT_QUOTES, 'UTF-8');
}

// TP4 act.20
function estConnecte()
{
    return isset($_SESSION['id_utilisateur']);
}

function protegerPage()
{
    global $pdo;
    if (!estConnecte()) {
        header("Location: login.php");
        exit;
    }
    // Revérifier le compte en base à chaque requête (TP6 act.16, question 4) :
    // un compte désactivé ou supprimé perd immédiatement son accès,
    // et un changement de rôle est pris en compte tout de suite.
    if (isset($pdo)) {
        $stmt = $pdo->prepare("SELECT role, actif FROM utilisateurs WHERE id_utilisateur = :id");
        $stmt->execute(['id' => $_SESSION['id_utilisateur']]);
        $u = $stmt->fetch();
        if (!$u || (int) $u['actif'] !== 1) {
            $_SESSION = [];
            session_destroy();
            header("Location: login.php");
            exit;
        }
        $_SESSION['role'] = $u['role'];
    }
}

// TP6 act.6
function utilisateurCourant()
{
    if (!estConnecte()) {
        return null;
    }
    return ['id' => $_SESSION['id_utilisateur'], 'nom' => $_SESSION['nom_complet'], 'role' => $_SESSION['role']];
}

function estAdmin()
{
    return estConnecte() && ($_SESSION['role'] ?? '') === 'ADMIN';
}

// TP6 act.9 : page réservée au rôle ADMIN (contrôle d'accès par rôle, RBAC)
function protegerAdmin()
{
    protegerPage();
    if (!estAdmin()) {
        setFlash('danger', "Accès réservé aux administrateurs.");
        header("Location: index.php");
        exit;
    }
}

// ADMIN a accès à tout ; les autres rôles seulement s'ils sont dans la liste
function aRole(array $roles)
{
    return estConnecte() && ($_SESSION['role'] === 'ADMIN' || in_array($_SESSION['role'], $roles, true));
}

function exigerRole(array $roles)
{
    protegerPage();
    if (!aRole($roles)) {
        setFlash('danger', "Accès refusé : vous n'avez pas le droit d'accéder à cette page.");
        header("Location: index.php");
        exit;
    }
}

// Messages (alertes Bootstrap) conservés dans la session
function setFlash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function afficherFlash()
{
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        echo '<div class="alert alert-' . e($f['type']) . ' alert-dismissible fade show">'
           . e($f['message'])
           . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        unset($_SESSION['flash']);
    }
}

function afficherErreurs(array $erreurs)
{
    if (count($erreurs) > 0) {
        echo '<div class="alert alert-danger"><ul class="mb-0">';
        foreach ($erreurs as $erreur) {
            echo '<li>' . e($erreur) . '</li>';
        }
        echo '</ul></div>';
    }
}

// Liste d'<option> avec une valeur présélectionnée
function options(array $valeurs, $selection = '')
{
    foreach ($valeurs as $v) {
        $sel = ((string) $v === (string) $selection) ? ' selected' : '';
        echo '<option value="' . e($v) . '"' . $sel . '>' . e($v) . '</option>';
    }
}

function badgeStatut($statut)
{
    $couleurs = ['EN STOCK' => 'secondary', 'VALIDE' => 'success', 'REJETÉ' => 'danger', 'UTILISÉ' => 'primary'];
    return '<span class="badge bg-' . ($couleurs[$statut] ?? 'dark') . '">' . e($statut) . '</span>';
}

function lienMenu($fichier, $texte, array $roles)
{
    if (!aRole($roles)) {
        return;
    }
    $actif = (basename($_SERVER['SCRIPT_NAME']) === $fichier) ? ' active' : '';
    echo '<li class="nav-item"><a class="nav-link' . $actif . '" href="' . $fichier . '">' . e($texte) . '</a></li>';
}
