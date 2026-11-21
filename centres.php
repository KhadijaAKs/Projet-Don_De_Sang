<?php
// Gestion des centres (ADMIN) : liste, ajout, suppression
require_once __DIR__ . '/config/connexion.php';
require_once __DIR__ . '/includes/fonctions.php';
protegerAdmin();   // ADMIN uniquement (TP6 act.9)

$erreurs = [];
$nom = $ville = $adresse = $telephone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['action'] ?? '') === 'supprimer') {
        try {
            $stmt = $pdo->prepare("DELETE FROM centres_collecte WHERE id_centre = :id");
            $stmt->execute(['id' => (int) ($_POST['id'] ?? 0)]);
            setFlash('success', "Centre supprimé.");
        } catch (PDOException $e) {
            setFlash('danger', "Suppression impossible : des dons sont liés à ce centre.");
        }
        header("Location: centres.php");
        exit;
    }

    $nom = trim($_POST['nom'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    if ($nom === '') $erreurs[] = "Le nom est obligatoire.";
    if ($ville === '') $erreurs[] = "La ville est obligatoire.";
    if ($adresse === '') $erreurs[] = "L'adresse est obligatoire.";

    if (count($erreurs) === 0) {
        $stmt = $pdo->prepare("INSERT INTO centres_collecte (nom, ville, adresse, telephone)
                               VALUES (:n, :v, :a, :t)");
        $stmt->execute(['n' => $nom, 'v' => $ville, 'a' => $adresse, 't' => $telephone]);
        setFlash('success', "Centre ajouté.");
        header("Location: centres.php");
        exit;
    }
}

$centres = $pdo->query("SELECT * FROM centres_collecte ORDER BY nom")->fetchAll();

$titre = "Centres de collecte";
require __DIR__ . '/includes/header.php';
?>
<h2 class="mb-3">Centres de collecte</h2>
<?php afficherErreurs($erreurs); ?>

<form method="POST" class="row g-2 mb-4">
  <div class="col-md-3"><input class="form-control" name="nom" placeholder="Nom" value="<?= e($nom) ?>" required></div>
  <div class="col-md-2"><input class="form-control" name="ville" placeholder="Ville" value="<?= e($ville) ?>" required></div>
  <div class="col-md-3"><input class="form-control" name="adresse" placeholder="Adresse" value="<?= e($adresse) ?>" required></div>
  <div class="col-md-2"><input class="form-control" name="telephone" placeholder="Téléphone" value="<?= e($telephone) ?>"></div>
  <div class="col-md-2"><button class="btn btn-danger w-100" type="submit">Ajouter</button></div>
</form>

<div class="table-responsive">
<table class="table table-striped align-middle">
  <thead class="table-dark"><tr><th>Nom</th><th>Ville</th><th>Adresse</th><th>Téléphone</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($centres as $c): ?>
    <tr>
      <td><?= e($c['nom']) ?></td>
      <td><?= e($c['ville']) ?></td>
      <td><?= e($c['adresse']) ?></td>
      <td><?= e($c['telephone']) ?></td>
      <td>
        <form method="POST" onsubmit="return confirm('Supprimer ce centre ?');">
          <input type="hidden" name="action" value="supprimer">
          <input type="hidden" name="id" value="<?= (int) $c['id_centre'] ?>">
          <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
