<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../../login.php');
    exit();
}

require_once '../../config/database.php';
$pdo = getPDO();
$id = $_GET['id'] ?? 0;
$error = '';
$success = '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'patient'");
$stmt->execute([$id]);
$patient = $stmt->fetch();

if(!$patient) {
    header('Location: index.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    
    $stmt = $pdo->prepare("UPDATE users SET nom = ?, prenom = ?, email = ?, telephone = ? WHERE id = ?");
    $stmt->execute([$nom, $prenom, $email, $telephone, $id]);
    $success = "Patient modifié avec succès !";
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'patient'");
    $stmt->execute([$id]);
    $patient = $stmt->fetch();
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <h1>✏️ Modifier le patient</h1>
    
    <?php if($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
    <?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    
    <form method="POST" class="table-container" style="padding: 24px;">
        <div class="form-group">
            <label>Nom</label>
            <input type="text" name="nom" value="<?= htmlspecialchars($patient['nom']) ?>" required>
        </div>
        <div class="form-group">
            <label>Prénom</label>
            <input type="text" name="prenom" value="<?= htmlspecialchars($patient['prenom']) ?>" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($patient['email']) ?>" required>
        </div>
        <div class="form-group">
            <label>Téléphone</label>
            <input type="text" name="telephone" value="<?= htmlspecialchars($patient['telephone']) ?>">
        </div>
        <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
        <a href="index.php" class="btn" style="background:#EBEBEB;">Retour</a>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>