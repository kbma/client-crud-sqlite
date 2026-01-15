<?php
// login.php
// Simple login form + authentication using users table

require_once __DIR__ . '/db.php';

// Start session (auth.php is not required here because we do login)
if (session_status() === PHP_SESSION_NONE) session_start();

$errors = [];
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Veuillez renseigner l\'email et le mot de passe.';
    } else {
        // Prepared statement to avoid injection
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Auth success
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];

            // Redirect to requested page or index
            $next = isset($_GET['next']) ? $_GET['next'] : 'index.php';
            header('Location: ' . $next);
            exit;
        } else {
            $errors[] = 'Email ou mot de passe invalide.';
        }
    }
}

?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Connexion</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="login-wrapper">
    <div class="login-card">
        <h1>Se connecter</h1>
        <p class="muted">Accédez à votre tableau de bord</p>

        <?php if (!empty($msg)): ?>
            <div class="notice"><?php echo e($msg); ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="errors" role="alert">
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?php echo e($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" class="login-form" novalidate>
            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input id="password" type="password" name="password" required>
            </div>

            <div class="form-actions" style="margin-top:12px">
                <button class="btn primary" type="submit">Se connecter</button>
                <a class="btn secondary" href="index.php">Retour</a>
            </div>

            <p class="muted" style="margin-top:12px;font-size:0.9rem">Compte démo : <strong>admin@example.com / admin</strong></p>
        </form>
    </div>
</div>
</body>
</html>