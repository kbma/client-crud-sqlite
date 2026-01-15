<?php
// index.php
// Liste des clients avec affichage de l'image de profil

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_auth();

// Récupérer tous les clients
$stmt = $pdo->query('SELECT * FROM clients ORDER BY id DESC');
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Messages d'alerte (succès / erreur) via query string (pédagogique)
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Gestion Clients - Liste</title>
    <!-- Google Font: Inter (fallback to system fonts) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <!-- Top bar with title and create button -->
        <header class="topbar" role="banner">
            <div class="topbar-left">
                <h1>Gestion des clients</h1>
                <p class="muted">Tableau de bord · Liste des clients</p>
            </div>
            <div class="topbar-right">
                <a class="btn primary" href="create.php" aria-label="Ajouter un client">➕ Ajouter un client</a>

                <?php if (isset($_SESSION['user_email'])): ?>
                    <div class="user-area" style="display:inline-flex;gap:12px;align-items:center;margin-left:12px">
                        <span class="muted" style="font-weight:600"><?php echo e($_SESSION['user_email']); ?></span>
                        <a class="btn secondary small" href="logout.php">Se déconnecter</a>
                    </div>
                <?php endif; ?>
            </div>
        </header>

        <!-- Alerts -->
        <div class="alerts" aria-live="polite">
            <?php if ($msg): ?>
                <div class="notice" role="status"><?php echo e($msg); ?></div>
            <?php endif; ?>
        </div>

        <!-- Data area: desktop table + mobile cards (both use the same $clients array) -->
        <?php if (count($clients) === 0): ?>
            <div class="empty-state" role="region" aria-label="Aucun client">
                <p>Aucun client trouvé.</p>
                <a class="btn primary" href="create.php">+ Ajouter votre premier client</a>
            </div>
        <?php else: ?>
            <div class="data-area">
                <!-- Table view (desktop / tablet) -->
                <div class="table-wrap">
                    <table class="clients-table" role="table" aria-label="Liste des clients">
                        <thead>
                            <tr>
                                <th scope="col">Photo</th>
                                <th scope="col">Nom</th>
                                <th scope="col">Prénom</th>
                                <th scope="col">Email</th>
                                <th scope="col">Téléphone</th>
                                <th scope="col">Ville</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td class="avatar">
                                    <?php if (!empty($client['image']) && file_exists(__DIR__ . '/uploads/' . $client['image'])): ?>
                                        <img src="uploads/<?php echo e($client['image']); ?>" alt="<?php echo e($client['prenom'] . ' ' . $client['nom']); ?>">
                                    <?php else: ?>
                                        <div class="placeholder-avatar"><?php echo strtoupper(substr($client['prenom'], 0, 1) . substr($client['nom'], 0, 1)); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($client['nom']); ?></td>
                                <td><?php echo e($client['prenom']); ?></td>
                                <td><?php echo e($client['email']); ?></td>
                                <td><?php echo e($client['tel']); ?></td>
                                <td><?php echo e($client['ville']); ?></td>
                                <td class="actions">
                                    <a class="btn edit small" href="edit.php?id=<?php echo $client['id']; ?>" aria-label="Modifier <?php echo e($client['prenom'] . ' ' . $client['nom']); ?>">✎ Modifier</a>

                                    <!-- Suppression via POST pour être plus sûr -->
                                    <form method="post" action="delete.php" onsubmit="return confirm('Supprimer ce client ?');" style="display:inline-block; margin:0;">
                                        <input type="hidden" name="id" value="<?php echo $client['id']; ?>">
                                        <button class="btn danger small" type="submit" aria-label="Supprimer <?php echo e($client['prenom'] . ' ' . $client['nom']); ?>">🗑 Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Card view (mobile) -->
                <div class="cards-wrap">
                    <?php foreach ($clients as $client): ?>
                        <article class="client-card" role="article" aria-labelledby="client-<?php echo $client['id']; ?>">
                            <div class="card-top">
                                <div class="avatar card-avatar">
                                    <?php if (!empty($client['image']) && file_exists(__DIR__ . '/uploads/' . $client['image'])): ?>
                                        <img src="uploads/<?php echo e($client['image']); ?>" alt="<?php echo e($client['prenom'] . ' ' . $client['nom']); ?>">
                                    <?php else: ?>
                                        <div class="placeholder-avatar"><?php echo strtoupper(substr($client['prenom'], 0, 1) . substr($client['nom'], 0, 1)); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="client-meta">
                                    <h3 id="client-<?php echo $client['id']; ?>"><?php echo e($client['prenom'] . ' ' . $client['nom']); ?></h3>
                                    <p class="muted"><?php echo e($client['email']); ?> · <?php echo e($client['tel']); ?></p>
                                    <p class="muted"><?php echo e($client['ville']); ?></p>
                                </div>
                            </div>
                            <div class="card-actions">
                                <a class="btn edit" href="edit.php?id=<?php echo $client['id']; ?>" aria-label="Modifier <?php echo e($client['prenom'] . ' ' . $client['nom']); ?>">✎ Modifier</a>
                                <form method="post" action="delete.php" onsubmit="return confirm('Supprimer ce client ?');">
                                    <input type="hidden" name="id" value="<?php echo $client['id']; ?>">
                                    <button class="btn danger" type="submit" aria-label="Supprimer <?php echo e($client['prenom'] . ' ' . $client['nom']); ?>">🗑 Supprimer</button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <footer>
            <p>Serveur de développement : <code>php -S localhost:8000</code> → <a href="http://localhost:8000">http://localhost:8000</a></p>
        </footer>
    </div>
</body>
</html>