<?php
// Liste paginée des donneurs avec filtres GET (TP3 act.12, TP5 act.9)
require_once __DIR__ . '/config/connexion.php';
require_once __DIR__ . '/includes/fonctions.php';
exigerRole(['SECRETAIRE']);

$groupe = $_GET['groupe'] ?? '';
$ville  = trim($_GET['ville'] ?? '');
$page   = max(1, (int) ($_GET['page'] ?? 1));
$parPage = 10;

$where = [];
$params = [];
if ($groupe !== '') {
    $where[] = "CONCAT(groupe_sanguin, rhesus) = :groupe";
    $params['groupe'] = $groupe;
}
if ($ville !== '') {
    $where[] = "ville LIKE :ville";
    $params['ville'] = '%' . $ville . '%';
}
$sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $pdo->prepare("SELECT COUNT(*) FROM donneurs $sqlWhere");
$stmt->execute($params);
$total = (int) $stmt->fetchColumn();

$nbPages = max(1, (int) ceil($total / $parPage));
$page = min($page, $nbPages);
$offset = ($page - 1) * $parPage;   // entiers : pas d'injection possible

$stmt = $pdo->prepare("SELECT * FROM donneurs $sqlWhere ORDER BY nom, prenom LIMIT $parPage OFFSET $offset");
$stmt->execute($params);
$donneurs = $stmt->fetchAll();

$qs = http_build_query(['groupe' => $groupe, 'ville' => $ville]);

$titre = "Donneurs";
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h2>Donneurs <small class="text-muted">(<?= $total ?>)</small></h2>
  <a class="btn btn-danger" href="donneur_form.php">+ Ajouter un donneur</a>
</div>

<form method="GET" class="row g-2 mb-3">
  <div class="col-md-3">
    <select name="groupe" class="form-select">
      <option value="">Tous les groupes</option>
      <?php options(GROUPES, $groupe); ?>
    </select>
  </div>
  <div class="col-md-4">
    <input type="text" name="ville" class="form-control" placeholder="Ville" value="<?= e($ville) ?>">
  </div>
  <div class="col-md-3">
    <button class="btn btn-primary" type="submit">Filtrer</button>
    <a class="btn btn-outline-secondary" href="donneurs.php">Réinitialiser</a>
  </div>
</form>

<div class="table-responsive">
<table class="table table-striped table-hover align-middle">
  <thead class="table-dark">
    <tr><th>CIN</th><th>Nom</th><th>Prénom</th><th>Groupe</th><th>Ville</th><th>Téléphone</th><th>Actions</th></tr>
  </thead>
  <tbody>
  <?php if (count($donneurs) === 0): ?>
    <tr><td colspan="7" class="text-center text-muted">Aucun donneur trouvé.</td></tr>
  <?php endif; ?>
  <?php foreach ($donneurs as $d): ?>
    <tr>
      <td><?= e($d['cin']) ?></td>
      <td><?= e($d['nom']) ?></td>
      <td><?= e($d['prenom']) ?></td>
      <td><span class="badge bg-danger"><?= e($d['groupe_sanguin'] . $d['rhesus']) ?></span></td>
      <td><?= e($d['ville']) ?></td>
      <td><?= e($d['telephone']) ?></td>
      <td class="text-nowrap">
        <a class="btn btn-sm btn-info" href="donneur_details.php?id=<?= (int) $d['id_donneur'] ?>">Détails</a>
        <a class="btn btn-sm btn-warning" href="donneur_form.php?id=<?= (int) $d['id_donneur'] ?>">Modifier</a>
        <?php if (estAdmin()): ?>
        <form method="POST" action="donneur_supprimer.php" class="d-inline"
              onsubmit="return confirm('Supprimer ce donneur ?');">
          <input type="hidden" name="id" value="<?= (int) $d['id_donneur'] ?>">
          <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<nav>
  <ul class="pagination">
    <?php for ($i = 1; $i <= $nbPages; $i++): ?>
      <li class="page-item <?= $i === $page ? 'active' : '' ?>">
        <a class="page-link" href="?<?= $qs ?>&page=<?= $i ?>"><?= $i ?></a>
      </li>
    <?php endfor; ?>
  </ul>
</nav>
<?php require __DIR__ . '/includes/footer.php'; ?>
