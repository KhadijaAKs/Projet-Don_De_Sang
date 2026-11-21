<?php
// Détails + historique des dons (jointure donneurs / dons / centres)
require_once __DIR__ . '/config/connexion.php';
require_once __DIR__ . '/includes/fonctions.php';
exigerRole(['SECRETAIRE']);

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM donneurs WHERE id_donneur = :id");
$stmt->execute(['id' => $id]);
$d = $stmt->fetch();
if (!$d) {
    setFlash('danger', "Donneur introuvable.");
    header("Location: donneurs.php");
    exit;
}

$stmt = $pdo->prepare("SELECT dons.*, c.nom AS centre
                       FROM dons
                       JOIN centres_collecte c ON c.id_centre = dons.id_centre
                       WHERE dons.id_donneur = :id
                       ORDER BY dons.date_don DESC");
$stmt->execute(['id' => $id]);
$dons = $stmt->fetchAll();

$titre = "Détails du donneur";
require __DIR__ . '/includes/header.php';
?>
<h2 class="mb-3"><?= e($d['prenom'] . ' ' . $d['nom']) ?>
  <span class="badge bg-danger"><?= e($d['groupe_sanguin'] . $d['rhesus']) ?></span></h2>

<div class="card mb-4"><div class="card-body">
  <div class="row">
    <div class="col-md-6">
      <p><strong>CIN :</strong> <?= e($d['cin']) ?></p>
      <p><strong>Date de naissance :</strong> <?= e($d['date_naissance']) ?></p>
      <p><strong>Sexe :</strong> <?= e($d['sexe']) ?></p>
    </div>
    <div class="col-md-6">
      <p><strong>Téléphone :</strong> <?= e($d['telephone']) ?></p>
      <p><strong>Ville :</strong> <?= e($d['ville']) ?></p>
      <p><strong>Inscrit le :</strong> <?= e($d['date_inscription']) ?></p>
    </div>
  </div>
  <a class="btn btn-danger" href="don_ajouter.php?id_donneur=<?= $id ?>">Enregistrer un don</a>
  <a class="btn btn-outline-secondary" href="donneurs.php">Retour</a>
</div></div>

<h4>Historique des dons</h4>
<div class="table-responsive">
<table class="table table-striped">
  <thead class="table-dark"><tr><th>Date</th><th>Centre</th><th>Quantité</th><th>Statut</th></tr></thead>
  <tbody>
  <?php if (count($dons) === 0): ?>
    <tr><td colspan="4" class="text-center text-muted">Aucun don enregistré.</td></tr>
  <?php endif; ?>
  <?php foreach ($dons as $don): ?>
    <tr>
      <td><?= e($don['date_don']) ?></td>
      <td><?= e($don['centre']) ?></td>
      <td><?= (int) $don['quantite_ml'] ?> ml</td>
      <td><?= badgeStatut($don['statut']) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
