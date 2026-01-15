<?php
// edit.php
// Formulaire et traitement pour modifier un client

require_once __DIR__ . '/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Récupération du client
$stmt = $pdo->prepare('SELECT * FROM clients WHERE id = :id');
$stmt->execute([':id' => $id]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    die('Client introuvable.');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $tel = trim($_POST['tel'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $currentImage = $client['image'];

    // Validation
    if ($nom === '') $errors[] = 'Le champ nom est requis.';
    if ($prenom === '') $errors[] = 'Le champ prénom est requis.';
    if ($email === '') {
        $errors[] = 'Le champ email est requis.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Le format de l\'email est invalide.';
    }

    // Gestion upload image (optionnel) — si un nouveau est uploadé, remplacer l'ancien
    $newImageFileName = $currentImage; // par défaut on garde l'actuelle

    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['image'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Erreur lors de l\'upload de l\'image.';
        } else {
            $info = @getimagesize($file['tmp_name']);
            $allowedTypes = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif'];

            if ($info === false || !isset($allowedTypes[$info[2]])) {
                $errors[] = 'Le fichier uploadé doit être une image (jpg, png, gif).';
            } else {
                $ext = $allowedTypes[$info[2]];
                $newImageFileName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                $destination = __DIR__ . '/uploads/' . $newImageFileName;

                if (!move_uploaded_file($file['tmp_name'], $destination)) {
                    $errors[] = 'Impossible de déplacer le fichier uploadé.';
                } else {
                    // Supprimer l'ancienne image si elle existe et est différente
                    if (!empty($currentImage) && file_exists(__DIR__ . '/uploads/' . $currentImage)) {
                        @unlink(__DIR__ . '/uploads/' . $currentImage);
                    }
                }
            }
        }
    }

    if (empty($errors)) {
        // Mise à jour
        $sql = 'UPDATE clients SET nom = :nom, prenom = :prenom, email = :email, tel = :tel, ville = :ville, image = :image WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':tel' => $tel,
            ':ville' => $ville,
            ':image' => $newImageFileName,
            ':id' => $id
        ]);

        header('Location: index.php?msg=' . urlencode('Client modifié avec succès.'));
        exit;
    }
}

?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Modifier un client</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <header class="topbar" style="margin-bottom:12px;display:flex;align-items:center;gap:12px">
        <div>
            <h1>Modifier le client</h1>
            <p class="muted">Mettre à jour les informations du client</p>
        </div>
        <div style="margin-left:auto;display:flex;gap:12px;align-items:center">
            <?php if (isset($_SESSION['user_email'])): ?>
                <span class="muted" style="font-weight:600"><?php echo e($_SESSION['user_email']); ?></span>
                <a class="btn secondary small" href="logout.php">Se déconnecter</a>
            <?php endif; ?>
        </div>
    </header>

    <?php if (!empty($errors)): ?>
        <!-- Error box (accessible) -->
        <div class="errors" role="alert" aria-live="assertive">
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?php echo e($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Responsive form: two columns on desktop, single column on mobile -->
    <form class="form-grid" method="post" enctype="multipart/form-data" novalidate>
        <!-- Left column: form fields -->
        <div class="form-column">
            <div class="form-card">
                <!-- Name -->
                <div class="form-group">
                    <label class="form-label" for="nom">Nom <span class="required" aria-hidden="true">*</span></label>
                    <div class="form-field">
                        <input id="nom" type="text" name="nom" value="<?php echo e($_POST['nom'] ?? $client['nom']); ?>" aria-required="true">
                    </div>
                </div>

                <!-- First name -->
                <div class="form-group">
                    <label class="form-label" for="prenom">Prénom <span class="required" aria-hidden="true">*</span></label>
                    <div class="form-field">
                        <input id="prenom" type="text" name="prenom" value="<?php echo e($_POST['prenom'] ?? $client['prenom']); ?>" aria-required="true">
                    </div>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label class="form-label" for="email">Email <span class="required" aria-hidden="true">*</span></label>
                    <div class="form-field">
                        <input id="email" type="email" name="email" value="<?php echo e($_POST['email'] ?? $client['email']); ?>" aria-required="true">
                    </div>
                </div>

                <!-- Phone -->
                <div class="form-group">
                    <label class="form-label" for="tel">Téléphone</label>
                    <div class="form-field">
                        <input id="tel" type="text" name="tel" value="<?php echo e($_POST['tel'] ?? $client['tel']); ?>">
                    </div>
                </div>

                <!-- City -->
                <div class="form-group">
                    <label class="form-label" for="ville">Ville</label>
                    <div class="form-field">
                        <input id="ville" type="text" name="ville" value="<?php echo e($_POST['ville'] ?? $client['ville']); ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Right column: image preview and actions -->
        <aside class="form-column">
            <div class="form-card">
                <h2 class="muted" style="margin-top:0;margin-bottom:10px">Image de profil</h2>

                <div class="image-preview">
                    <?php if (!empty($client['image']) && file_exists(__DIR__ . '/uploads/' . $client['image'])): ?>
                        <img src="uploads/<?php echo e($client['image']); ?>" alt="Aperçu de l'image">
                    <?php else: ?>
                        <div class="image-placeholder"><?php echo strtoupper(substr($client['prenom'], 0, 1) . substr($client['nom'], 0, 1)); ?></div>
                    <?php endif; ?>

                    <div style="flex:1">
                        <label class="file-drop" for="image">Sélectionner/Remplacer l'image</label>
                        <div class="file-input">
                            <input id="image" type="file" name="image" accept="image/*" aria-describedby="image-help">
                        </div>
                        <p id="image-help" class="muted" style="margin-top:8px;font-size:0.9rem">Formats: jpg, png, gif · Taille max: selon configuration serveur</p>
                    </div>
                </div>

                <div class="form-actions" style="margin-top:18px">
                    <button class="btn primary" type="submit">Enregistrer</button>
                    <a class="btn secondary" href="index.php" role="button">Annuler</a>
                </div>
            </div>
        </aside>
    </form>
</div>
</body>
</html>