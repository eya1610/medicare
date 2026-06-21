<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header('Location: ../../login.php');
    exit();
}

require_once '../../config/database.php';
$pdo = getPDO();
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <h1>👤 Mon profil</h1>
    
    <div class="table-container" style="padding: 24px;">
        <div class="form-group"><label>Nom complet</label><input type="text" value="<?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>" disabled></div>
        <div class="form-group"><label>Email</label><input type="text" value="<?= htmlspecialchars($user['email']) ?>" disabled></div>
        <div class="form-group"><label>Téléphone</label><input type="text" value="<?= htmlspecialchars($user['telephone']) ?>" disabled></div>
        <div class="form-group"><label>Nom d'utilisateur</label><input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled></div>
        
        <a href="modifier.php" class="btn btn-primary">✏️ Modifier mon profil</a>
        <a href="supprimer.php" class="btn btn-danger" onclick="return confirm('Supprimer définitivement votre compte ?')">🗑️ Supprimer mon compte</a>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>