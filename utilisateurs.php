<?php
// TP6 act.13 : liste des utilisateurs (ADMIN)
require_once __DIR__ . '/config/connexion.php';
require_once __DIR__ . '/includes/fonctions.php';
protegerAdmin();

$utilisateurs = $pdo->query("SELECT u.id_utilisateur, u.nom_utilisateur, u.nom_complet, u.role, u.actif, u.created_at,
                                    c.nom AS centre
                             FROM utilisateurs u
                             LEFT JOIN centres_collecte c ON c.id_centre = u.id_centre
                             ORDER BY u.nom_utilisateur")->fetchAll();

$titre = "Gestion des utilisateurs";
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h2>Gestion des utilisateurs</h2>
  <a class="btn btn-danger" href="utilisateur-creer.php">+ Créer un utilisateur</a>
</div>

<div class="table-responsive">
<table class="table table-striped align-middle">
  <thead class="table-dark">
    <tr><th>ID</th><th>Utilisateur</th><th>Nom complet</th><th>Rôle</th><th>Centre</th><th>Statut</th><th>Créé le</th><th>Actions</th></tr>
  </thead>
  <tbody>
  <?php foreach ($utilisateurs as $u): ?>
    <?php $id = (int) $u['id_utilisateur']; ?>
    <tr>
      <td><?= $id ?></td>
      <td><?= e($u['nom_utilisateur']) ?></td>
      <td><?= e($u['nom_complet']) ?></td>
      <td><span class="badge bg-secondary"><?= e($u['role']) ?></span></td>
      <td><?= e($u['centre'] ?? '-') ?></td>
      <td><?= $u['actif'] ? '<span class="badge bg-success">Actif</span>' : '<span class="badge bg-danger">Inactif</span>' ?></td>
      <td><?= e($u['created_at']) ?></td>
      <td class="text-nowrap">
        <a class="btn btn-sm btn-warning" href="utilisateur-role.php?id=<?= $id ?>">Rôle</a>
        <a class="btn btn-sm btn-info" href="utilisateur-statut.php?id=<?= $id ?>">Activer / Désactiver</a>
        <a class="btn btn-sm btn-outline-danger" href="utilisateur-supprimer.php?id=<?= $id ?>">Supprimer</a>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
