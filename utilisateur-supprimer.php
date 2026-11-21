<?php
// TP6 act.17 : suppression d'un compte, avec confirmation par POST
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
    if ((int) $utilisateur['id_utilisateur'] === (int) $_SESSION['id_utilisateur']) {
        $erreur = "Vous ne pouvez pas supprimer votre propre compte.";
    } elseif ($utilisateur['role'] === 'ADMIN' && (int) $utilisateur['actif'] === 1) {
        $nb = (int) $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'ADMIN' AND actif = 1")->fetchColumn();
        if ($nb <= 1) {
            $erreur = "Impossible : il doit rester au moins un administrateur actif.";
        }
    }

    if ($erreur === '') {
        $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id_utilisateur = :id");
        $stmt->execute(['id' => $utilisateur['id_utilisateur']]);
        $succes = "Utilisateur supprimé.";
        $utilisateur = null;
    }
}

$titre = "Supprimer un utilisateur";
require __DIR__ . '/includes/header.php';
?>
<h2 class="mb-3">Supprimer un utilisateur</h2>
<p><a href="utilisateurs.php">← Retour à la liste</a></p>
<?php if ($erreur !== ''): ?><div class="alert alert-danger"><?= e($erreur) ?></div><?php endif; ?>
<?php if ($succes !== ''): ?><div class="alert alert-success"><?= e($succes) ?></div><?php endif; ?>

<?php if ($utilisateur !== null): ?>
  <p>Confirmer la suppression de <strong><?= e($utilisateur['nom_utilisateur']) ?></strong> ?
     <small class="text-muted">(Désactiver le compte est souvent préférable : l'historique est conservé.)</small></p>
  <form method="POST">
    <button class="btn btn-danger" type="submit">Supprimer définitivement</button>
    <a class="btn btn-outline-secondary" href="utilisateurs.php">Annuler</a>
  </form>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
