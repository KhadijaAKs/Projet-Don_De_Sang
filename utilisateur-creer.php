<?php
// TP6 act.14 : création d'un utilisateur par l'administrateur
require_once __DIR__ . '/config/connexion.php';
require_once __DIR__ . '/includes/fonctions.php';
protegerAdmin();

$centres = $pdo->query("SELECT id_centre, nom FROM centres_collecte ORDER BY nom")->fetchAll();
$erreurs = [];
$nom_utilisateur = $nom_complet = '';
$role = 'SECRETAIRE';
$id_centre = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_utilisateur = trim($_POST['nom_utilisateur'] ?? '');
    $nom_complet = trim($_POST['nom_complet'] ?? '');
    $mdp = $_POST['mot_de_passe'] ?? '';
    $confirmation = $_POST['confirmation'] ?? '';
    $role = $_POST['role'] ?? '';
    $id_centre = (int) ($_POST['id_centre'] ?? 0);

    if ($nom_utilisateur === '') {
        $erreurs[] = "Le nom d'utilisateur est obligatoire.";
    } elseif (strlen($nom_utilisateur) < 3) {
        $erreurs[] = "Le nom d'utilisateur doit contenir au moins 3 caractères.";
    }
    if ($nom_complet === '') $erreurs[] = "Le nom complet est obligatoire.";
    if (strlen($mdp) < 8) $erreurs[] = "Le mot de passe doit contenir au moins 8 caractères.";
    if ($mdp !== $confirmation) $erreurs[] = "Les deux mots de passe ne correspondent pas.";
    if (!in_array($role, ['ADMIN', 'MEDECIN', 'SECRETAIRE'], true)) $erreurs[] = "Rôle invalide.";

    if (count($erreurs) === 0) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE nom_utilisateur = :u");
        $stmt->execute(['u' => $nom_utilisateur]);
        if ((int) $stmt->fetchColumn() > 0) {
            $erreurs[] = "Ce nom d'utilisateur est déjà utilisé.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom_utilisateur, nom_complet, mot_de_passe, role, id_centre, actif)
                                   VALUES (:u, :n, :p, :r, :c, 1)");
            $stmt->execute([
                'u' => $nom_utilisateur, 'n' => $nom_complet,
                'p' => password_hash($mdp, PASSWORD_DEFAULT),
                'r' => $role, 'c' => $id_centre > 0 ? $id_centre : null,
            ]);
            setFlash('success', "Utilisateur créé.");
            header("Location: utilisateurs.php");
            exit;
        }
    }
}

$titre = "Créer un utilisateur";
require __DIR__ . '/includes/header.php';
?>
<h2 class="mb-3">Créer un utilisateur</h2>
<p><a href="utilisateurs.php">← Retour à la liste</a></p>
<?php afficherErreurs($erreurs); ?>

<form method="POST" class="row g-3">
  <div class="col-md-6">
    <label class="form-label" for="nom_utilisateur">Nom d'utilisateur</label>
    <input class="form-control" id="nom_utilisateur" name="nom_utilisateur" value="<?= e($nom_utilisateur) ?>" required>
  </div>
  <div class="col-md-6">
    <label class="form-label" for="nom_complet">Nom complet</label>
    <input class="form-control" id="nom_complet" name="nom_complet" value="<?= e($nom_complet) ?>" required>
  </div>
  <div class="col-md-6">
    <label class="form-label" for="mot_de_passe">Mot de passe (8 caractères min.)</label>
    <input class="form-control" type="password" id="mot_de_passe" name="mot_de_passe" required>
  </div>
  <div class="col-md-6">
    <label class="form-label" for="confirmation">Confirmation</label>
    <input class="form-control" type="password" id="confirmation" name="confirmation" required>
  </div>
  <div class="col-md-6">
    <label class="form-label" for="role">Rôle</label>
    <select class="form-select" id="role" name="role"><?php options(['ADMIN', 'MEDECIN', 'SECRETAIRE'], $role); ?></select>
  </div>
  <div class="col-md-6">
    <label class="form-label" for="id_centre">Centre</label>
    <select class="form-select" id="id_centre" name="id_centre">
      <option value="0">-- Aucun centre --</option>
      <?php foreach ($centres as $c): ?>
        <option value="<?= (int) $c['id_centre'] ?>" <?= $c['id_centre'] == $id_centre ? 'selected' : '' ?>><?= e($c['nom']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-12"><button class="btn btn-danger" type="submit">Créer</button></div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
