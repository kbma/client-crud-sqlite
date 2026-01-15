<?php
// delete.php
// Suppression d'un client et suppression du fichier image associé

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Pour sécurité simple : ne permettre que POST
    header('Location: index.php');
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

// Récupérer le client pour connaître le nom du fichier image
$stmt = $pdo->prepare('SELECT image FROM clients WHERE id = :id');
$stmt->execute([':id' => $id]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    header('Location: index.php?msg=' . urlencode('Client introuvable.'));
    exit;
}

// Supprimer l'enregistrement
$stmt = $pdo->prepare('DELETE FROM clients WHERE id = :id');
$stmt->execute([':id' => $id]);

// Supprimer le fichier image s'il existe
if (!empty($client['image']) && file_exists(__DIR__ . '/uploads/' . $client['image'])) {
    @unlink(__DIR__ . '/uploads/' . $client['image']);
}

header('Location: index.php?msg=' . urlencode('Client supprimé.'));
exit;

?>