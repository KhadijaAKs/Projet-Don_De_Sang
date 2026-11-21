<?php
// TP6 act.16 : activer / désactiver un compte (par POST)
require_once __DIR__ . '/config/connexion.php';
require_once __DIR__ . '/includes/fonctions.php';
protegerAdmin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id === null) {
    setFlash('danger', "Identifiant invalide.");
    header("Location: utilisateurs.php");
    exit;
}

$stmt = $pdo->prepare("SELECT id_utilisateur, nom_utilisateur, role, actif FROM utilisateurs WHERE id_utilisateur = :id");
$stmt->execute(['id' => $id]);
$utilisateur = $stmt->fetch();
if (!$utilisateur) {
    setFlash('danger', "Utilisateur introuvable.");
    header("Location: utilisateurs.php");
    exit;
}

$erreur = '';
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nouvelEtat = (int) ($_POST['actif'] ?? -1);

    if (!in_array($nouvelEtat, [0, 1], true)) {
        $erreur = "Valeur invalide.";
    } elseif ((int) $utilisateur['id_utilisateur'] === (int) $_SESSION['id_utilisateur'] && $nouvelEtat === 0) {
        $erreur = "Vous ne pouvez pas désactiver votre propre compte.";
    } elseif ($utilisateur['role'] === 'ADMIN' && (int) $utilisateur['actif'] === 1 && $nouvelEtat === 0) {
        $nb = (int) $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'ADMIN' AND actif = 1")->fetchColumn();
        if ($nb <= 1) {
            $erreur = "Impossible : il doit rester au moins un administrateur actif.";
        }
    }

    if ($erreur === '') {
        $stmt = $pdo->prepare("UPDATE utilisateurs SET actif = :actif WHERE id_utilisateur = :id");
        $stmt->execute(['actif' => $nouvelEtat, 'id' => $utilisateur['id_utilisateur']]);
        $utilisateur['actif'] = $nouvelEtat;
        $succes = "Statut mis à jour.";
    }
}

$titre = "Activer / Désactiver";
require __DIR__ . '/includes/header.php';
?>
<h2 class="mb-3">Statut de <?= e($utilisateur['nom_utilisateur']) ?></h2>
<p><a href="utilisateurs.php">← Retour à la liste</a></p>
<?php if ($erreur !== ''): ?><div class="alert alert-danger"><?= e($erreur) ?></div><?php endif; ?>
<?php if ($succes !== ''): ?><div class="alert alert-success"><?= e($succes) ?></div><?php endif; ?>

<p>Statut actuel : <?= $utilisateur['actif'] ? '<span class="badge bg-success">Actif</span>' : '<span class="badge bg-danger">Inactif</span>' ?></p>

<form method="POST">
  <?php if ($utilisateur['actif']): ?>
    <input type="hidden" name="actif" value="0">
    <button class="btn btn-warning" type="submit">Désactiver</button>
  <?php else: ?>
    <input type="hidden" name="actif" value="1">
    <button class="btn btn-success" type="submit">Activer</button>
  <?php endif; ?>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
