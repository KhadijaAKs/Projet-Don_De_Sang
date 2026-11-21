<?php
// TP6 act.9 : espace réservé au rôle ADMIN
require_once __DIR__ . '/config/connexion.php';
require_once __DIR__ . '/includes/fonctions.php';
protegerAdmin();

$nbUsers = (int) $pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
$nbAttente = (int) $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE actif = 0")->fetchColumn();

$titre = "Administration";
require __DIR__ . '/includes/header.php';
?>
<h2 class="mb-3">Espace administrateur</h2>
<p class="text-muted">Cette page est réservée aux administrateurs.</p>

<?php if ($nbAttente > 0): ?>
  <div class="alert alert-warning">
    <?= $nbAttente ?> compte(s) inactif(s) ou en attente d'activation.
    <a href="utilisateurs.php" class="alert-link">Gérer les utilisateurs</a>
  </div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-md-4"><div class="card"><div class="card-body">
    <h5>Utilisateurs (<?= $nbUsers ?>)</h5>
    <p>Créer, modifier le rôle, activer/désactiver, supprimer.</p>
    <a class="btn btn-danger" href="utilisateurs.php">Gérer</a>
  </div></div></div>
  <div class="col-md-4"><div class="card"><div class="card-body">
    <h5>Centres de collecte</h5>
    <p>Ajouter ou supprimer des centres.</p>
    <a class="btn btn-danger" href="centres.php">Gérer</a>
  </div></div></div>
  <div class="col-md-4"><div class="card"><div class="card-body">
    <h5>Transfusions</h5>
    <p>Tracer l'utilisation finale des dons valides.</p>
    <a class="btn btn-danger" href="transfusions.php">Gérer</a>
  </div></div></div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
