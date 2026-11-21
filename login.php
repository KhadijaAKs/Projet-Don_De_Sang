<?php
// TP6 act.5 : authentification en base (requête préparée + password_verify + compte actif)
require_once __DIR__ . '/config/connexion.php';
require_once __DIR__ . '/includes/fonctions.php';

if (estConnecte()) {
    header("Location: index.php");
    exit;
}

$erreur = '';
$nom_utilisateur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_utilisateur = trim($_POST['nom_utilisateur'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    if ($nom_utilisateur === '' || $mot_de_passe === '') {
        $erreur = "Veuillez remplir tous les champs.";
    } else {
        $stmt = $pdo->prepare("SELECT id_utilisateur, nom_complet, mot_de_passe, role, id_centre, actif
                               FROM utilisateurs WHERE nom_utilisateur = :nom");
        $stmt->execute(['nom' => $nom_utilisateur]);
        $user = $stmt->fetch();

        // Même message pour : compte inexistant, mauvais mot de passe, compte désactivé
        if ($user && (int) $user['actif'] === 1 && password_verify($mot_de_passe, $user['mot_de_passe'])) {
            $_SESSION['id_utilisateur'] = (int) $user['id_utilisateur'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['nom_complet'] = $user['nom_complet'];
            $_SESSION['id_centre'] = $user['id_centre'];
            header("Location: index.php");
            exit;
        }
        $erreur = "Identifiants incorrects.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Connexion</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container" style="max-width: 420px; margin-top: 10vh;">
  <div class="card shadow">
    <div class="card-body p-4">
      <h3 class="text-danger text-center mb-4">🩸 Don de Sang</h3>
      <?php if ($erreur): ?>
        <div class="alert alert-danger"><?= e($erreur) ?></div>
      <?php endif; ?>
      <form method="POST">
        <div class="mb-3">
          <label class="form-label" for="nom_utilisateur">Nom d'utilisateur</label>
          <input class="form-control" id="nom_utilisateur" name="nom_utilisateur" value="<?= e($nom_utilisateur) ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label" for="mot_de_passe">Mot de passe</label>
          <input class="form-control" type="password" id="mot_de_passe" name="mot_de_passe" required>
        </div>
        <button class="btn btn-danger w-100" type="submit">Se connecter</button>
      </form>
      <p class="text-center mt-3 mb-0"><a href="inscription.php">Créer un compte</a></p>
    </div>
  </div>
</div>
</body>
</html>
