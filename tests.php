<?php
// Validation des tests (MEDECIN) : liste des dons EN STOCK + formulaire de résultats
require_once __DIR__ . '/config/connexion.php';
require_once __DIR__ . '/includes/fonctions.php';
exigerRole(['MEDECIN']);

$id_don = (int) ($_POST['id_don'] ?? $_GET['id_don'] ?? 0);
$don = null;

// Charger le don à tester (uniquement s'il est EN STOCK)
if ($id_don > 0) {
    $stmt = $pdo->prepare("SELECT d.*, dn.nom, dn.prenom, dn.groupe_sanguin, dn.rhesus
                           FROM dons d JOIN donneurs dn ON dn.id_donneur = d.id_donneur
                           WHERE d.id_don = :id AND d.statut = 'EN STOCK'");
    $stmt->execute(['id' => $id_don]);
    $don = $stmt->fetch();
    if (!$don) {
        setFlash('warning', "Don introuvable ou déjà testé.");
        header("Location: tests.php");
        exit;
    }
}

$erreurs = [];
$choix = ['NEGATIF', 'POSITIF'];
$vih = $_POST['vih'] ?? 'NEGATIF';
$hb  = $_POST['hepatite_b'] ?? 'NEGATIF';
$hc  = $_POST['hepatite_c'] ?? 'NEGATIF';
$remarques = trim($_POST['remarques'] ?? '');

if ($don && $_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ([$vih, $hb, $hc] as $r) {
        if (!in_array($r, $choix, true)) $erreurs[] = "Résultat de test invalide.";
    }
    if (strlen($remarques) > 255) $erreurs[] = "Remarques trop longues (255 caractères max).";

    if (count($erreurs) === 0) {
        // Conforme seulement si tous les tests sont négatifs
        $conforme = ($vih === 'NEGATIF' && $hb === 'NEGATIF' && $hc === 'NEGATIF') ? 1 : 0;
        $nouveauStatut = $conforme ? 'VALIDE' : 'REJETÉ';

        // Transaction : les deux opérations réussissent ensemble ou pas du tout
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO tests_don
                (id_don, id_utilisateur, date_test, vih, hepatite_b, hepatite_c, est_conforme, remarques)
                VALUES (:don, :user, CURDATE(), :vih, :hb, :hc, :conf, :rem)");
            $stmt->execute(['don' => $id_don, 'user' => $_SESSION['id_utilisateur'], 'vih' => $vih,
                            'hb' => $hb, 'hc' => $hc, 'conf' => $conforme, 'rem' => $remarques]);

            $stmt = $pdo->prepare("UPDATE dons SET statut = :s WHERE id_don = :id");
            $stmt->execute(['s' => $nouveauStatut, 'id' => $id_don]);
            $pdo->commit();

            setFlash($conforme ? 'success' : 'warning', "Tests enregistrés : don $nouveauStatut.");
            header("Location: tests.php");
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $erreurs[] = "Erreur lors de l'enregistrement des tests.";
        }
    }
}

$enAttente = $pdo->query("SELECT d.id_don, d.date_don, d.quantite_ml, dn.nom, dn.prenom, dn.groupe_sanguin, dn.rhesus
                          FROM dons d JOIN donneurs dn ON dn.id_donneur = d.id_donneur
                          WHERE d.statut = 'EN STOCK' ORDER BY d.date_don")->fetchAll();

$titre = "Validation des tests";
require __DIR__ . '/includes/header.php';
?>
<h2 class="mb-3">Validation des tests</h2>
<?php afficherErreurs($erreurs); ?>

<?php if ($don): ?>
  <div class="card mb-4"><div class="card-body">
    <h5>Don n°<?= (int) $don['id_don'] ?> — <?= e($don['prenom'] . ' ' . $don['nom']) ?>
      <span class="badge bg-danger"><?= e($don['groupe_sanguin'] . $don['rhesus']) ?></span></h5>
    <form method="POST" class="row g-3 mt-1">
      <input type="hidden" name="id_don" value="<?= (int) $don['id_don'] ?>">
      <div class="col-md-4">
        <label class="form-label">VIH</label>
        <select class="form-select" name="vih"><?php options($choix, $vih); ?></select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Hépatite B</label>
        <select class="form-select" name="hepatite_b"><?php options($choix, $hb); ?></select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Hépatite C</label>
        <select class="form-select" name="hepatite_c"><?php options($choix, $hc); ?></select>
      </div>
      <div class="col-12">
        <label class="form-label">Remarques</label>
        <input class="form-control" name="remarques" maxlength="255" value="<?= e($remarques) ?>">
      </div>
      <div class="col-12">
        <button class="btn btn-danger" type="submit">Enregistrer les résultats</button>
        <a class="btn btn-outline-secondary" href="tests.php">Annuler</a>
      </div>
    </form>
  </div></div>
<?php endif; ?>

<h4>Dons en attente de validation</h4>
<div class="table-responsive">
<table class="table table-striped align-middle">
  <thead class="table-dark"><tr><th>N°</th><th>Date</th><th>Donneur</th><th>Groupe</th><th>Quantité</th><th></th></tr></thead>
  <tbody>
  <?php if (count($enAttente) === 0): ?>
    <tr><td colspan="6" class="text-center text-muted">Aucun don en attente.</td></tr>
  <?php endif; ?>
  <?php foreach ($enAttente as $r): ?>
    <tr>
      <td><?= (int) $r['id_don'] ?></td>
      <td><?= e($r['date_don']) ?></td>
      <td><?= e($r['nom'] . ' ' . $r['prenom']) ?></td>
      <td><?= e($r['groupe_sanguin'] . $r['rhesus']) ?></td>
      <td><?= (int) $r['quantite_ml'] ?> ml</td>
      <td><a class="btn btn-sm btn-primary" href="tests.php?id_don=<?= (int) $r['id_don'] ?>">Saisir les résultats</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
