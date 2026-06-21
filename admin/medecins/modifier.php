<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../../login.php');
    exit();
}

require_once '../../config/database.php';
$pdo = getPDO();
$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'medecin'");
$stmt->execute([$id]);
$medecin = $stmt->fetch();

if(!$medecin) {
    header('Location: index.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $specialite = trim($_POST['specialite']);
    
    $stmt = $pdo->prepare("UPDATE users SET nom = ?, prenom = ?, email = ?, telephone = ?, specialite = ? WHERE id = ?");
    $stmt->execute([$nom, $prenom, $email, $telephone, $specialite, $id]);
    
    header('Location: index.php?modifie=1');
    exit();
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <h1>✏️ Modifier le médecin</h1>
    
    <form method="POST" class="table-container" style="padding: 24px;">
        <div class="form-group">
            <label>Nom</label>
            <input type="text" name="nom" value="<?= htmlspecialchars($medecin['nom']) ?>" required>
        </div>
        <div class="form-group">
            <label>Prénom</label>
            <input type="text" name="prenom" value="<?= htmlspecialchars($medecin['prenom']) ?>" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($medecin['email']) ?>" required>
        </div>
        <div class="form-group">
            <label>Téléphone</label>
            <input type="text" name="telephone" value="<?= htmlspecialchars($medecin['telephone']) ?>">
        </div>
        <div class="form-group">
            <label>Spécialité</label>
            <input type="text" name="specialite" value="<?= htmlspecialchars($medecin['specialite']) ?>">
        </div>
        <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
        <a href="index.php" class="btn" style="background:#EBEBEB;">Retour</a>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>