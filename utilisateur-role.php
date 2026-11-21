<?php
// TP6 act.15 : modifier le rôle d'un utilisateur
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
    $nouveauRole = $_POST['role'] ?? '';

    if (!in_array($nouveauRole, ['ADMIN', 'MEDECIN', 'SECRETAIRE'], true)) {
        $erreur = "Rôle invalide.";
    } elseif ((int) $utilisateur['id_utilisateur'] === (int) $_SESSION['id_utilisateur'] && $nouveauRole !== 'ADMIN') {
        $erreur = "Vous ne pouvez pas retirer votre propre rôle administrateur.";
    } elseif ($utilisateur['role'] === 'ADMIN' && (int) $utilisateur['actif'] === 1 && $nouveauRole !== 'ADMIN') {
        $nb = (int) $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'ADMIN' AND actif = 1")->fetchColumn();
        if ($nb <= 1) {
            $erreur = "Impossible : il doit rester au moins un administrateur actif.";
        }
    }

    if ($erreur === '') {
        $stmt = $pdo->prepare("UPDATE utilisateurs SET role = :role WHERE id_utilisateur = :id");
        $stmt->execute(['role' => $nouveauRole, 'id' => $utilisateur['id_utilisateur']]);
        $utilisateur['role'] = $nouveauRole;
        $succes = "Rôle mis à jour.";
    }
}

$titre = "Modifier le rôle";
require __DIR__ . '/includes/header.php';
?>
<h2 class="mb-3">Modifier le rôle de <?= e($utilisateur['nom_utilisateur']) ?></h2>
<p><a href="utilisateurs.php">← Retour à la liste</a></p>
<?php if ($erreur !== ''): ?><div class="alert alert-danger"><?= e($erreur) ?></div><?php endif; ?>
<?php if ($succes !== ''): ?><div class="alert alert-success"><?= e($succes) ?></div><?php endif; ?>

<form method="POST" class="row g-3" style="max-width: 400px;">
  <div class="col-12">
    <label class="form-label" for="role">Nouveau rôle</label>
    <select class="form-select" id="role" name="role"><?php options(['ADMIN', 'MEDECIN', 'SECRETAIRE'], $utilisateur['role']); ?></select>
  </div>
  <div class="col-12"><button class="btn btn-danger" type="submit">Enregistrer</button></div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
