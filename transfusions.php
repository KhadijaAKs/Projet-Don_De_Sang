<?php
// Traçabilité / transfusions (ADMIN) : dons VALIDE -> UTILISÉ
require_once __DIR__ . '/config/connexion.php';
require_once __DIR__ . '/includes/fonctions.php';
protegerAdmin();   // ADMIN uniquement (TP6 act.9)

$id_don = (int) ($_POST['id_don'] ?? $_GET['id_don'] ?? 0);
$don = null;

if ($id_don > 0) {
    $stmt = $pdo->prepare("SELECT d.*, dn.nom, dn.prenom, dn.groupe_sanguin, dn.rhesus
                           FROM dons d JOIN donneurs dn ON dn.id_donneur = d.id_donneur
                           WHERE d.id_don = :id AND d.statut = 'VALIDE'");
    $stmt->execute(['id' => $id_don]);
    $don = $stmt->fetch();
    if (!$don) {
        setFlash('warning', "Don introuvable ou non disponible (statut différent de VALIDE).");
        header("Location: transfusions.php");
        exit;
    }
}

$erreurs = [];
$hopital = trim($_POST['hopital_recepteur'] ?? '');
$date = $_POST['date_transfusion'] ?? date('Y-m-d');

if ($don && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($hopital === '') $erreurs[] = "L'hôpital récepteur est obligatoire.";
    $dt = DateTime::createFromFormat('Y-m-d', $date);
    if (!$dt || $dt > new DateTime()) $erreurs[] = "Date de transfusion invalide.";

    if (count($erreurs) === 0) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO transfusions (id_don, hopital_recepteur, date_transfusion)
                                   VALUES (:id, :h, :d)");
            $stmt->execute(['id' => $id_don, 'h' => $hopital, 'd' => $date]);
            $stmt = $pdo->prepare("UPDATE dons SET statut = 'UTILISÉ' WHERE id_don = :id");
            $stmt->execute(['id' => $id_don]);
            $pdo->commit();
            setFlash('success', "Transfusion enregistrée : le don est maintenant UTILISÉ.");
            header("Location: transfusions.php");
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $erreurs[] = "Erreur lors de l'enregistrement.";
        }
    }
}

$valides = $pdo->query("SELECT d.id_don, d.date_don, d.quantite_ml, dn.nom, dn.prenom, dn.groupe_sanguin, dn.rhesus
                        FROM dons d JOIN donneurs dn ON dn.id_donneur = d.id_donneur
                        WHERE d.statut = 'VALIDE' ORDER BY d.date_don")->fetchAll();

$historique = $pdo->query("SELECT t.*, dn.nom, dn.prenom, dn.groupe_sanguin, dn.rhesus
                           FROM transfusions t
                           JOIN dons d ON d.id_don = t.id_don
                           JOIN donneurs dn ON dn.id_donneur = d.id_donneur
                           ORDER BY t.date_transfusion DESC")->fetchAll();

$titre = "Transfusions";
require __DIR__ . '/includes/header.php';
?>
<h2 class="mb-3">Transfusions</h2>
<?php afficherErreurs($erreurs); ?>

<?php if ($don): ?>
  <div class="card mb-4"><div class="card-body">
    <h5>Don n°<?= (int) $don['id_don'] ?> — <?= e($don['groupe_sanguin'] . $don['rhesus']) ?>
      (<?= e($don['prenom'] . ' ' . $don['nom']) ?>)</h5>
    <form method="POST" class="row g-3 mt-1">
      <input type="hidden" name="id_don" value="<?= (int) $don['id_don'] ?>">
      <div class="col-md-6">
        <label class="form-label">Hôpital récepteur</label>
        <input class="form-control" name="hopital_recepteur" value="<?= e($hopital) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Date de transfusion</label>
        <input class="form-control" type="date" name="date_transfusion" value="<?= e($date) ?>" required>
      </div>
      <div class="col-12">
        <button class="btn btn-danger" type="submit">Enregistrer la transfusion</button>
        <a class="btn btn-outline-secondary" href="transfusions.php">Annuler</a>
      </div>
    </form>
  </div></div>
<?php endif; ?>

<h4>Dons valides disponibles</h4>
<div class="table-responsive mb-4">
<table class="table table-striped align-middle">
  <thead class="table-dark"><tr><th>N°</th><th>Date</th><th>Groupe</th><th>Quantité</th><th></th></tr></thead>
  <tbody>
  <?php if (count($valides) === 0): ?>
    <tr><td colspan="5" class="text-center text-muted">Aucun don valide disponible.</td></tr>
  <?php endif; ?>
  <?php foreach ($valides as $r): ?>
    <tr>
      <td><?= (int) $r['id_don'] ?></td>
      <td><?= e($r['date_don']) ?></td>
      <td><?= e($r['groupe_sanguin'] . $r['rhesus']) ?></td>
      <td><?= (int) $r['quantite_ml'] ?> ml</td>
      <td><a class="btn btn-sm btn-primary" href="transfusions.php?id_don=<?= (int) $r['id_don'] ?>">Utiliser</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<h4>Historique des transfusions</h4>
<div class="table-responsive">
<table class="table table-striped">
  <thead class="table-dark"><tr><th>Date</th><th>Don</th><th>Groupe</th><th>Hôpital</th></tr></thead>
  <tbody>
  <?php foreach ($historique as $t): ?>
    <tr>
      <td><?= e($t['date_transfusion']) ?></td>
      <td>n°<?= (int) $t['id_don'] ?> (<?= e($t['prenom'] . ' ' . $t['nom']) ?>)</td>
      <td><?= e($t['groupe_sanguin'] . $t['rhesus']) ?></td>
      <td><?= e($t['hopital_recepteur']) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
