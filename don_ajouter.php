<?php
// Enregistrement d'un don (SECRETAIRE) - listes déroulantes peuplées par PHP
require_once __DIR__ . '/config/connexion.php';
require_once __DIR__ . '/includes/fonctions.php';
exigerRole(['SECRETAIRE']);

$donneurs = $pdo->query("SELECT id_donneur, nom, prenom, cin, groupe_sanguin, rhesus
                         FROM donneurs ORDER BY nom, prenom")->fetchAll();
$centres = $pdo->query("SELECT id_centre, nom FROM centres_collecte ORDER BY nom")->fetchAll();

$id_donneur = (int) ($_POST['id_donneur'] ?? $_GET['id_donneur'] ?? 0);
$id_centre  = (int) ($_POST['id_centre'] ?? ($_SESSION['id_centre'] ?? 0));
$date_don   = $_POST['date_don'] ?? date('Y-m-d');
$quantite   = $_POST['quantite_ml'] ?? 450;
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation
    $stmt = $pdo->prepare("SELECT id_donneur FROM donneurs WHERE id_donneur = :id");
    $stmt->execute(['id' => $id_donneur]);
    if (!$stmt->fetch()) $erreurs[] = "Veuillez choisir un donneur valide.";

    $stmt = $pdo->prepare("SELECT id_centre FROM centres_collecte WHERE id_centre = :id");
    $stmt->execute(['id' => $id_centre]);
    if (!$stmt->fetch()) $erreurs[] = "Veuillez choisir un centre valide.";

    $dt = DateTime::createFromFormat('Y-m-d', $date_don);
    if (!$dt || $dt > new DateTime()) $erreurs[] = "Date de don invalide (pas de date future).";

    if (!ctype_digit((string) $quantite) || $quantite < 250 || $quantite > 500) {
        $erreurs[] = "La quantité doit être comprise entre 250 et 500 ml.";
    }

    // Règle simple : au moins 56 jours entre deux dons
    if (count($erreurs) === 0) {
        $stmt = $pdo->prepare("SELECT MAX(date_don) FROM dons WHERE id_donneur = :id");
        $stmt->execute(['id' => $id_donneur]);
        $dernier = $stmt->fetchColumn();
        if ($dernier && (new DateTime($dernier))->diff($dt)->days < 56) {
            $erreurs[] = "Ce donneur a déjà donné il y a moins de 56 jours (dernier don : $dernier).";
        }
    }

    if (count($erreurs) === 0) {
        $stmt = $pdo->prepare("INSERT INTO dons (id_donneur, id_centre, date_don, quantite_ml, statut)
                               VALUES (:d, :c, :date, :q, 'EN STOCK')");
        $stmt->execute(['d' => $id_donneur, 'c' => $id_centre, 'date' => $date_don, 'q' => (int) $quantite]);
        setFlash('success', "Don enregistré (statut : EN STOCK).");
        header("Location: don_ajouter.php");
        exit;
    }
}

$recents = $pdo->query("SELECT d.date_don, d.quantite_ml, d.statut, dn.nom, dn.prenom, c.nom AS centre
                        FROM dons d
                        JOIN donneurs dn ON dn.id_donneur = d.id_donneur
                        JOIN centres_collecte c ON c.id_centre = d.id_centre
                        ORDER BY d.id_don DESC LIMIT 10")->fetchAll();

$titre = "Enregistrer un don";
require __DIR__ . '/includes/header.php';
?>
<h2 class="mb-3">Enregistrer un don</h2>
<?php afficherErreurs($erreurs); ?>

<form method="POST" class="row g-3 mb-4">
  <div class="col-md-6">
    <label class="form-label" for="id_donneur">Donneur</label>
    <select class="form-select" id="id_donneur" name="id_donneur" required>
      <option value="">-- Choisir --</option>
      <?php foreach ($donneurs as $d): ?>
        <option value="<?= (int) $d['id_donneur'] ?>" <?= $d['id_donneur'] == $id_donneur ? 'selected' : '' ?>>
          <?= e($d['nom'] . ' ' . $d['prenom'] . ' (' . $d['cin'] . ') - ' . $d['groupe_sanguin'] . $d['rhesus']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-6">
    <label class="form-label" for="id_centre">Centre de collecte</label>
    <select class="form-select" id="id_centre" name="id_centre" required>
      <option value="">-- Choisir --</option>
      <?php foreach ($centres as $c): ?>
        <option value="<?= (int) $c['id_centre'] ?>" <?= $c['id_centre'] == $id_centre ? 'selected' : '' ?>>
          <?= e($c['nom']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-6">
    <label class="form-label" for="date_don">Date du don</label>
    <input class="form-control" type="date" id="date_don" name="date_don" value="<?= e($date_don) ?>" required>
  </div>
  <div class="col-md-6">
    <label class="form-label" for="quantite_ml">Quantité (ml)</label>
    <input class="form-control" type="number" id="quantite_ml" name="quantite_ml" min="250" max="500" value="<?= e($quantite) ?>" required>
  </div>
  <div class="col-12"><button class="btn btn-danger" type="submit">Enregistrer le don</button></div>
</form>

<h4>Derniers dons</h4>
<div class="table-responsive">
<table class="table table-striped">
  <thead class="table-dark"><tr><th>Date</th><th>Donneur</th><th>Centre</th><th>Quantité</th><th>Statut</th></tr></thead>
  <tbody>
  <?php foreach ($recents as $r): ?>
    <tr>
      <td><?= e($r['date_don']) ?></td>
      <td><?= e($r['nom'] . ' ' . $r['prenom']) ?></td>
      <td><?= e($r['centre']) ?></td>
      <td><?= (int) $r['quantite_ml'] ?> ml</td>
      <td><?= badgeStatut($r['statut']) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
