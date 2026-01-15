<?php
// create.php
// Formulaire et traitement pour ajouter un client

require_once __DIR__ . '/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des champs
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $tel = trim($_POST['tel'] ?? '');
    $ville = trim($_POST['ville'] ?? '');

    // Validation côté serveur
    if ($nom === '') $errors[] = 'Le champ nom est requis.';
    if ($prenom === '') $errors[] = 'Le champ prénom est requis.';
    if ($email === '') {
        $errors[] = 'Le champ email est requis.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Le format de l\'email est invalide.';
    }

    // Gestion de l'upload d'image (optionnel)
    $imageFileName = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['image'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Erreur lors de l\'upload de l\'image.';
        } else {
            // Vérification type image via getimagesize
            $info = @getimagesize($file['tmp_name']);
            $allowedTypes = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif'];

            if ($info === false || !isset($allowedTypes[$info[2]])) {
                $errors[] = 'Le fichier uploadé doit être une image (jpg, png, gif).';
            } else {
                $ext = $allowedTypes[$info[2]];
                // Renommage automatique pour éviter les collisions
                $imageFileName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                $destination = __DIR__ . '/uploads/' . $imageFileName;

                if (!move_uploaded_file($file['tmp_name'], $destination)) {
                    $errors[] = 'Impossible de déplacer le fichier uploadé.';
                }
            }
        }
    }

    // Si pas d'erreurs : insertion en base
    if (empty($errors)) {
        $sql = 'INSERT INTO clients (nom, prenom, email, tel, ville, image) VALUES (:nom, :prenom, :email, :tel, :ville, :image)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':tel' => $tel,
            ':ville' => $ville,
            ':image' => $imageFileName
        ]);

        // Redirection vers la liste avec message
        header('Location: index.php?msg=' . urlencode('Client ajouté avec succès.'));
        exit;
    }
}

?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Ajouter un client</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <header class="topbar" style="margin-bottom:12px;align-items:center;gap:10px;">
        <div>
            <h1>Ajouter un client <span class="badge-new" aria-hidden="true">Nouveau</span></h1>
            <p class="muted">Créer une nouvelle fiche client</p>
        </div>
        <div class="topbar-right" style="margin-left:auto;display:flex;gap:12px;align-items:center;">
            <a class="btn" href="index.php" aria-label="Retour à la liste">← Retour</a>

            <?php if (isset($_SESSION['user_email'])): ?>
                <div class="user-area" style="display:inline-flex;gap:8px;align-items:center">
                    <span class="muted" style="font-weight:600"><?php echo e($_SESSION['user_email']); ?></span>
                    <a class="btn secondary small" href="logout.php">Se déconnecter</a>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <?php if (!empty($errors)): ?>
        <div class="errors" role="alert" aria-live="assertive">
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?php echo e($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form class="form-grid" method="post" enctype="multipart/form-data" novalidate>
        <!-- Left: Inputs -->
        <div class="form-column">
            <div class="form-card">
                <div class="form-group">
                    <label class="form-label" for="nom">Nom <span class="required" aria-hidden="true">*</span></label>
                    <div class="form-field">
                        <input id="nom" type="text" name="nom" value="<?php echo e($_POST['nom'] ?? ''); ?>" aria-required="true">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="prenom">Prénom <span class="required" aria-hidden="true">*</span></label>
                    <div class="form-field">
                        <input id="prenom" type="text" name="prenom" value="<?php echo e($_POST['prenom'] ?? ''); ?>" aria-required="true">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email <span class="required" aria-hidden="true">*</span></label>
                    <div class="form-field">
                        <input id="email" type="email" name="email" value="<?php echo e($_POST['email'] ?? ''); ?>" aria-required="true">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="tel">Téléphone</label>
                    <div class="form-field">
                        <input id="tel" type="text" name="tel" value="<?php echo e($_POST['tel'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="ville">Ville</label>
                    <div class="form-field">
                        <input id="ville" type="text" name="ville" value="<?php echo e($_POST['ville'] ?? ''); ?>">
                    </div>
                </div>

            </div>
        </div>

        <!-- Right: Avatar + Upload + actions -->
        <aside class="form-column">
            <div class="form-card">
                <h2 class="muted" style="margin-top:0;margin-bottom:10px">Image de profil</h2>

                <div class="image-preview">
                    <div class="image-placeholder">+?</div>
                    <div style="flex:1">
                        <label class="file-drop" for="image">Sélectionner ou glisser une image</label>
                        <div class="file-input">
                            <input id="image" type="file" name="image" accept="image/*" aria-describedby="image-help">
                        </div>
                        <p id="image-help" class="muted" style="margin-top:8px;font-size:0.9rem">Formats: jpg, png, gif · Taille max: selon configuration serveur</p>
                    </div>
                </div>

                <div class="form-actions" style="margin-top:18px">
                    <button class="btn primary" type="submit">Ajouter le client</button>
                    <a class="btn secondary" href="index.php" role="button">Annuler</a>
                </div>
            </div>
        </aside>
    </form>
</div>
</body>
</html>