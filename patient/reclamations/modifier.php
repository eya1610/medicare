<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header('Location: ../login.php');
    exit();
}

require_once '../../config/database.php';
$pdo = getPDO();
$patient_id = $_SESSION['user_id'];
$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM reclamations WHERE id = ? AND patient_id = ?");
$stmt->execute([$id, $patient_id]);
$reclamation = $stmt->fetch();

if(!$reclamation || ($reclamation['statut'] != 'en_attente' && $reclamation['statut'] != 'en_cours')) {
    header('Location: index.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sujet = trim($_POST['sujet']);
    $description = trim($_POST['description']);
    
    $stmt = $pdo->prepare("UPDATE reclamations SET sujet = ?, description = ? WHERE id = ? AND patient_id = ?");
    $stmt->execute([$sujet, $description, $id, $patient_id]);
    
    header('Location: index.php?modifie=1');
    exit();
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <h1>✏️ Modifier ma réclamation</h1>
    
    <div class="form-card">
        <form method="POST">
            <div class="form-group">
                <label>Sujet *</label>
                <input type="text" name="sujet" value="<?= htmlspecialchars($reclamation['sujet']) ?>" required>
            </div>
            <div class="form-group">
                <label>Description *</label>
                <textarea name="description" rows="6" required><?= htmlspecialchars($reclamation['description']) ?></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
                <a href="index.php" class="btn btn-secondary">Retour</a>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>