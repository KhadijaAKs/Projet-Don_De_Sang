<?php
// Ajout ET modification d'un donneur (TP3 act.5-8, TP5 act.7-10)
require_once __DIR__ . '/config/connexion.php';
require_once __DIR__ . '/includes/fonctions.php';
exigerRole(['SECRETAIRE']);

$champs = ['cin', 'nom', 'prenom', 'date_naissance', 'sexe', 'groupe_sanguin', 'rhesus', 'telephone', 'ville'];
$d = array_fill_keys($champs, '');
$d['sexe'] = 'M'; $d['groupe_sanguin'] = 'A'; $d['rhesus'] = '+';

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

// Mode modification : charger le donneur (TP5 act.10)
if ($id > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $stmt = $pdo->prepare("SELECT * FROM donneurs WHERE id_donneur = :id");
    $stmt->execute(['id' => $id]);
    $ligne = $stmt->fetch();
    if (!$ligne) {
        setFlash('danger', "Donneur introuvable.");
        header("Location: donneurs.php");
        exit;
    }
    foreach ($champs as $c) {
        $d[$c] = $ligne[$c];
    }
}

$erreurs = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($champs as $c) {
        $d[$c] = trim($_POST[$c] ?? '');
    }

    // Validation côté serveur (TP3 act.5-6)
    if (!preg_match('/^\d{8}$/', $d['cin'])) $erreurs[] = "Le CIN doit contenir 8 chiffres.";
    if ($d['nom'] === '') $erreurs[] = "Le nom est obligatoire.";
    if ($d['prenom'] === '') $erreurs[] = "Le prénom est obligatoire.";
    if ($d['ville'] === '') $erreurs[] = "La ville est obligatoire.";
    if (!preg_match('/^\d{8}$/', $d['telephone'])) $erreurs[] = "Le téléphone doit contenir 8 chiffres.";
    if (!in_array($d['sexe'], ['M', 'F'], true)) $erreurs[] = "Sexe invalide.";
    if (!in_array($d['groupe_sanguin'], ['A', 'B', 'AB', 'O'], true)) $erreurs[] = "Groupe sanguin invalide.";
    if (!in_array($d['rhesus'], ['+', '-'], true)) $erreurs[] = "Rhésus invalide.";

    $dn = DateTime::createFromFormat('Y-m-d', $d['date_naissance']);
    if (!$dn) {
        $erreurs[] = "Date de naissance invalide.";
    } else {
        $age = $dn->diff(new DateTime())->y;
        if ($age < 18 || $age > 65) $erreurs[] = "Le donneur doit avoir entre 18 et 65 ans.";
    }

    // CIN unique
    if (count($erreurs) === 0) {
        $stmt = $pdo->prepare("SELECT id_donneur FROM donneurs WHERE cin = :cin AND id_donneur <> :id");
        $stmt->execute(['cin' => $d['cin'], 'id' => $id]);
        if ($stmt->fetch()) $erreurs[] = "Ce CIN existe déjà.";
    }

    // Traitement : INSERT ou UPDATE avec requête préparée
    if (count($erreurs) === 0) {
        if ($id > 0) {
            $sql = "UPDATE donneurs SET cin=:cin, nom=:nom, prenom=:prenom, date_naissance=:date_naissance,
                    sexe=:sexe, groupe_sanguin=:groupe_sanguin, rhesus=:rhesus, telephone=:telephone, ville=:ville
                    WHERE id_donneur = :id";
            $d['id'] = $id;
            $msg = "Donneur modifié avec succès.";
        } else {
            $sql = "INSERT INTO donneurs (cin, nom, prenom, date_naissance, sexe, groupe_sanguin, rhesus, telephone, ville)
                    VALUES (:cin, :nom, :prenom, :date_naissance, :sexe, :groupe_sanguin, :rhesus, :telephone, :ville)";
            $msg = "Donneur ajouté avec succès.";
        }
        $pdo->prepare($sql)->execute($d);
        setFlash('success', $msg);
        header("Location: donneurs.php");
        exit;
    }
}

$titre = $id > 0 ? "Modifier un donneur" : "Ajouter un donneur";
require __DIR__ . '/includes/header.php';
?>
<h2 class="mb-3"><?= e($titre) ?></h2>
<?php afficherErreurs($erreurs); ?>

<form method="POST" class="row g-3">
  <input type="hidden" name="id" value="<?= $id ?>">
  <div class="col-md-4">
    <label class="form-label" for="cin">CIN</label>
    <input class="form-control" id="cin" name="cin" value="<?= e($d['cin']) ?>" required>
  </div>
  <div class="col-md-4">
    <label class="form-label" for="nom">Nom</label>
    <input class="form-control" id="nom" name="nom" value="<?= e($d['nom']) ?>" required>
  </div>
  <div class="col-md-4">
    <label class="form-label" for="prenom">Prénom</label>
    <input class="form-control" id="prenom" name="prenom" value="<?= e($d['prenom']) ?>" required>
  </div>
  <div class="col-md-4">
    <label class="form-label" for="date_naissance">Date de naissance</label>
    <input class="form-control" type="date" id="date_naissance" name="date_naissance" value="<?= e($d['date_naissance']) ?>" required>
  </div>
  <div class="col-md-2">
    <label class="form-label" for="sexe">Sexe</label>
    <select class="form-select" id="sexe" name="sexe"><?php options(['M', 'F'], $d['sexe']); ?></select>
  </div>
  <div class="col-md-3">
    <label class="form-label" for="groupe_sanguin">Groupe</label>
    <select class="form-select" id="groupe_sanguin" name="groupe_sanguin"><?php options(['A', 'B', 'AB', 'O'], $d['groupe_sanguin']); ?></select>
  </div>
  <div class="col-md-3">
    <label class="form-label" for="rhesus">Rhésus</label>
    <select class="form-select" id="rhesus" name="rhesus"><?php options(['+', '-'], $d['rhesus']); ?></select>
  </div>
  <div class="col-md-6">
    <label class="form-label" for="telephone">Téléphone</label>
    <input class="form-control" id="telephone" name="telephone" value="<?= e($d['telephone']) ?>" required>
  </div>
  <div class="col-md-6">
    <label class="form-label" for="ville">Ville</label>
    <input class="form-control" id="ville" name="ville" value="<?= e($d['ville']) ?>" required>
  </div>
  <div class="col-12">
    <button class="btn btn-danger" type="submit">Enregistrer</button>
    <a class="btn btn-outline-secondary" href="donneurs.php">Annuler</a>
  </div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
