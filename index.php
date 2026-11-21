<?php
// Tableau de bord : statistiques, stock par groupe, alertes
require_once __DIR__ . '/config/connexion.php';
require_once __DIR__ . '/includes/fonctions.php';
protegerPage();

$nbDonneurs = (int) $pdo->query("SELECT COUNT(*) FROM donneurs")->fetchColumn();
$nbDonsValides = (int) $pdo->query("SELECT COUNT(*) FROM dons WHERE statut = 'VALIDE'")->fetchColumn();
$nbCentres = (int) $pdo->query("SELECT COUNT(*) FROM centres_collecte")->fetchColumn();

// Stock théorique : dons VALIDE (non encore utilisés) par groupe + rhésus
$stmt = $pdo->query("SELECT CONCAT(dn.groupe_sanguin, dn.rhesus) AS groupe,
                            COUNT(*) AS poches, SUM(d.quantite_ml) AS ml
                     FROM dons d
                     JOIN donneurs dn ON dn.id_donneur = d.id_donneur
                     WHERE d.statut = 'VALIDE'
                     GROUP BY groupe");
$stock = [];
foreach (GROUPES as $g) {
    $stock[$g] = ['poches' => 0, 'ml' => 0];
}
foreach ($stmt->fetchAll() as $ligne) {
    $stock[$ligne['groupe']] = ['poches' => (int) $ligne['poches'], 'ml' => (int) $ligne['ml']];
}

$alertes = $pdo->query("SELECT * FROM besoins
                        WHERE niveau_alerte IN ('URGENT','CRITIQUE')
                        ORDER BY niveau_alerte DESC")->fetchAll();

$titre = "Tableau de bord";
require __DIR__ . '/includes/header.php';
?>
<h2 class="mb-4">Tableau de bord</h2>

<div class="row g-3 mb-4">
  <div class="col-sm-6 col-lg-4">
    <div class="card text-bg-primary"><div class="card-body">
      <h6>Donneurs</h6><h2><?= $nbDonneurs ?></h2></div></div>
  </div>
  <div class="col-sm-6 col-lg-4">
    <div class="card text-bg-success"><div class="card-body">
      <h6>Dons valides</h6><h2><?= $nbDonsValides ?></h2></div></div>
  </div>
  <div class="col-sm-6 col-lg-4">
    <div class="card text-bg-secondary"><div class="card-body">
      <h6>Centres</h6><h2><?= $nbCentres ?></h2></div></div>
  </div>
</div>

<h4>Alertes de stock</h4>
<?php if (count($alertes) === 0): ?>
  <div class="alert alert-info">Aucune alerte en cours.</div>
<?php endif; ?>
<?php foreach ($alertes as $a): ?>
  <div class="alert alert-<?= $a['niveau_alerte'] === 'CRITIQUE' ? 'danger' : 'warning' ?>">
    <strong><?= e($a['niveau_alerte']) ?></strong> : groupe <strong><?= e($a['groupe_sanguin']) ?></strong>
    — stock actuel : <?= $stock[$a['groupe_sanguin']]['poches'] ?? 0 ?> poche(s)
  </div>
<?php endforeach; ?>

<h4 class="mt-4">État du stock par groupe sanguin</h4>
<div class="row g-3">
  <?php foreach ($stock as $groupe => $s): ?>
    <div class="col-6 col-md-3">
      <div class="card stock-card"><div class="card-body">
        <h3 class="text-danger"><?= e($groupe) ?></h3>
        <p class="mb-0"><?= $s['poches'] ?> poche(s)<br><small class="text-muted"><?= $s['ml'] ?> ml</small></p>
      </div></div>
    </div>
  <?php endforeach; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
