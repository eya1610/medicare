<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'medecin') {
    header('Location: ../../login.php');
    exit();
}

require_once '../../config/database.php';
$pdo = getPDO();
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $new_password = $_POST['new_password'];
    
    $sql = "UPDATE users SET email = ?, telephone = ?";
    $params = [$email, $telephone];
    
    if(!empty($new_password)) {
        $sql .= ", password = ?";
        $params[] = password_hash($new_password, PASSWORD_DEFAULT);
    }
    
    $sql .= " WHERE id = ?";
    $params[] = $user_id;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    header('Location: profil.php?modifie=1');
    exit();
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <h1>✏️ Modifier mon profil</h1>
    
    <form method="POST" class="table-container" style="padding: 24px;">
        <div class="form-group">
            <label>Nom complet</label>
            <input type="text" value="<?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>" disabled>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        <div class="form-group">
            <label>Téléphone</label>
            <input type="text" name="telephone" value="<?= htmlspecialchars($user['telephone']) ?>">
        </div>
        <div class="form-group">
            <label>Nouveau mot de passe (laisser vide pour ne pas changer)</label>
            <input type="password" name="new_password" placeholder="Nouveau mot de passe">
        </div>
        <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
        <a href="profil.php" class="btn" style="background:#EBEBEB;">Retour</a>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>