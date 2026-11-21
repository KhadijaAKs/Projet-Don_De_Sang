<?php
// TP6 act.10 : inscription. Le rôle n'est JAMAIS choisi par le visiteur.
// Adaptation au projet : le compte est créé avec le rôle SECRETAIRE (moindre privilège)
// et INACTIF : un administrateur doit l'activer et pourra changer son rôle.
require_once __DIR__ . '/config/connexion.php';
require_once __DIR__ . '/includes/fonctions.php';

$erreurs = [];
$succes = '';
$nom_utilisateur = $nom_complet = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_utilisateur = trim($_POST['nom_utilisateur'] ?? '');
    $nom_complet = trim($_POST['nom_complet'] ?? '');
    $mdp = $_POST['mot_de_passe'] ?? '';
    $confirmation = $_POST['confirmation'] ?? '';

    if ($nom_utilisateur === '') {
        $erreurs[] = "Le nom d'utilisateur est obligatoire.";
    } elseif (strlen($nom_utilisateur) < 3) {
        $erreurs[] = "Le nom d'utilisateur doit contenir au moins 3 caractères.";
    }
    if ($nom_complet === '') $erreurs[] = "Le nom complet est obligatoire.";
    if (strlen($mdp) < 8) $erreurs[] = "Le mot de passe doit contenir au moins 8 caractères.";
    if ($mdp !== $confirmation) $erreurs[] = "Les deux mots de passe ne correspondent pas.";

    if (count($erreurs) === 0) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE nom_utilisateur = :u");
        $stmt->execute(['u' => $nom_utilisateur]);
        if ((int) $stmt->fetchColumn() > 0) {
            $erreurs[] = "Ce nom d'utilisateur est déjà utilisé.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom_utilisateur, nom_complet, mot_de_passe, role, actif)
                                   VALUES (:u, :n, :p, 'SECRETAIRE', 0)");
            $stmt->execute(['u' => $nom_utilisateur, 'n' => $nom_complet,
                            'p' => password_hash($mdp, PASSWORD_DEFAULT)]);
            $succes = "Compte créé. Il doit être activé par un administrateur avant de pouvoir vous connecter.";
            $nom_utilisateur = $nom_complet = '';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inscription</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container" style="max-width: 480px; margin-top: 6vh;">
  <div class="card shadow">
    <div class="card-body p-4">
      <h3 class="text-danger text-center mb-4">Créer un compte</h3>
      <?php afficherErreurs($erreurs); ?>
      <?php if ($succes !== ''): ?>
        <div class="alert alert-success"><?= e($succes) ?></div>
      <?php endif; ?>
      <form method="POST">
        <div class="mb-3">
          <label class="form-label" for="nom_utilisateur">Nom d'utilisateur</label>
          <input class="form-control" id="nom_utilisateur" name="nom_utilisateur" value="<?= e($nom_utilisateur) ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label" for="nom_complet">Nom complet</label>
          <input class="form-control" id="nom_complet" name="nom_complet" value="<?= e($nom_complet) ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label" for="mot_de_passe">Mot de passe (8 caractères min.)</label>
          <input class="form-control" type="password" id="mot_de_passe" name="mot_de_passe" required>
        </div>
        <div class="mb-3">
          <label class="form-label" for="confirmation">Confirmation</label>
          <input class="form-control" type="password" id="confirmation" name="confirmation" required>
        </div>
        <button class="btn btn-danger w-100" type="submit">S'inscrire</button>
      </form>
      <p class="text-center mt-3 mb-0"><a href="login.php">Déjà un compte ? Se connecter</a></p>
    </div>
  </div>
</div>
</body>
</html>
