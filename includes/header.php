<?php
// En-tête commun : Bootstrap 5 + navbar + sidebar adaptée au rôle
require_once __DIR__ . '/fonctions.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($titre ?? 'Don de sang') ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark bg-danger sticky-top">
  <div class="container-fluid">
    <button class="navbar-toggler d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <a class="navbar-brand" href="index.php">🩸 Don de Sang</a>
    <span class="navbar-text text-white ms-auto me-3">
      <?= e($_SESSION['nom_complet']) ?> (<?= e($_SESSION['role']) ?>)
    </span>
    <a class="btn btn-outline-light btn-sm" href="logout.php">Se déconnecter</a>
  </div>
</nav>

<div class="container-fluid">
  <div class="row">
    <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
      <ul class="nav flex-column pt-3">
        <?php
        lienMenu('index.php', 'Tableau de bord', ['ADMIN', 'MEDECIN', 'SECRETAIRE']);
        lienMenu('donneurs.php', 'Liste des donneurs', ['SECRETAIRE']);
        lienMenu('donneur_form.php', 'Ajouter un donneur', ['SECRETAIRE']);
        lienMenu('don_ajouter.php', 'Enregistrer un don', ['SECRETAIRE']);
        lienMenu('tests.php', 'Validation des tests', ['MEDECIN']);
        lienMenu('admin.php', 'Administration', []);
        lienMenu('transfusions.php', 'Transfusions', []);        // ADMIN seulement
        lienMenu('centres.php', 'Centres de collecte', []);      // ADMIN seulement
        lienMenu('utilisateurs.php', 'Utilisateurs', []);        // ADMIN seulement
        ?>
      </ul>
    </nav>
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
      <?php afficherFlash(); ?>
