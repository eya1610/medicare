<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../../login.php');
    exit();
}

require_once '../../config/database.php';
$pdo = getPDO();
$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM reclamations WHERE id = ?");
$stmt->execute([$id]);
$reclamation = $stmt->fetch();

if(!$reclamation) {
    header('Location: index.php');
    exit();
}

$patients = $pdo->query("SELECT id, nom, prenom FROM users WHERE role = 'patient' ORDER BY nom")->fetchAll();

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_id = $_POST['patient_id'];
    $sujet = trim($_POST['sujet']);
    $description = trim($_POST['description']);
    $statut = $_POST['statut'];
    
    $stmt = $pdo->prepare("UPDATE reclamations SET patient_id = ?, sujet = ?, description = ?, statut = ? WHERE id = ?");
    $stmt->execute([$patient_id, $sujet, $description, $statut, $id]);
    
    header('Location: index.php?modifie=1');
    exit();
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <h1>✏️ Modifier la réclamation</h1>
    
    <div class="form-card">
        <form method="POST">
            <div class="form-group">
                <label>Patient *</label>
                <select name="patient_id" required>
                    <?php foreach($patients as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $p['id'] == $reclamation['patient_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Sujet *</label>
                <input type="text" name="sujet" value="<?= htmlspecialchars($reclamation['sujet']) ?>" required>
            </div>
            <div class="form-group">
                <label>Description *</label>
                <textarea name="description" rows="5" required><?= htmlspecialchars($reclamation['description']) ?></textarea>
            </div>
            <div class="form-group">
                <label>Statut</label>
                <select name="statut">
                    <option value="en_attente" <?= $reclamation['statut'] == 'en_attente' ? 'selected' : '' ?>>En attente</option>
                    <option value="en_cours" <?= $reclamation['statut'] == 'en_cours' ? 'selected' : '' ?>>En cours</option>
                    <option value="traite" <?= $reclamation['statut'] == 'traite' ? 'selected' : '' ?>>Traité</option>
                    <option value="rejete" <?= $reclamation['statut'] == 'rejete' ? 'selected' : '' ?>>Rejeté</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
                <a href="index.php" class="btn btn-secondary">Retour</a>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>