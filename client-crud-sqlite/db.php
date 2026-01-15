<?php
// db.php
// Connexion SQLite via PDO et création automatique de la table `clients`

// Chemin vers le fichier sqlite (créé automatiquement si inexistant)
$databaseFile = __DIR__ . '/database.sqlite';
$dsn = 'sqlite:' . $databaseFile;

try {
    // Création de l'objet PDO
    $pdo = new PDO($dsn);
    // Mode d'erreur : exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Création automatique de la table `clients` si elle n'existe pas
    $createTableSql = "
        CREATE TABLE IF NOT EXISTS clients (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nom TEXT NOT NULL,
            prenom TEXT NOT NULL,
            email TEXT NOT NULL,
            tel TEXT,
            ville TEXT,
            image TEXT
        )
    ";

    $pdo->exec($createTableSql);

    // Création automatique de la table `users` si nécessaire (authentification)
    $createUsersSql = "
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ";
    $pdo->exec($createUsersSql);

    // Si aucun utilisateur n'existe, insérer un compte admin par défaut (démonstration)
    // **Changez le mot de passe après premier démarrage en production**
    $stmt = $pdo->query('SELECT COUNT(*) FROM users');
    $usersCount = (int) $stmt->fetchColumn();
    if ($usersCount === 0) {
        $defaultPassword = password_hash('admin', PASSWORD_DEFAULT); // mot de passe par défaut : 'admin'
        $insert = $pdo->prepare('INSERT INTO users (email, password) VALUES (:email, :password)');
        $insert->execute([':email' => 'admin@example.com', ':password' => $defaultPassword]);
    }
} catch (PDOException $e) {
    // En cas d'erreur, on arrête l'exécution avec un message simple (pédagogique)
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
}

// Petite fonction utilitaire pour échapper les sorties HTML
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

?>