<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../../login.php');
    exit();
}

require_once '../../config/database.php';
$pdo = getPDO();
$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM avis WHERE id = ?");
$stmt->execute([$id]);
$avis = $stmt->fetch();

if(!$avis) {
    header('Location: index.php');
    exit();
}

$patients = $pdo->query("SELECT id, nom, prenom FROM users WHERE role = 'patient' ORDER BY nom")->fetchAll();
$medecins = $pdo->query("SELECT id, nom, prenom FROM users WHERE role = 'medecin' ORDER BY nom")->fetchAll();

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_id = $_POST['patient_id'];
    $medecin_id = $_POST['medecin_id'] ?: null;
    $note = (int)$_POST['note'];
    $commentaire = trim($_POST['commentaire']);
    $statut = $_POST['statut'];
    
    $stmt = $pdo->prepare("UPDATE avis SET patient_id = ?, medecin_id = ?, note = ?, commentaire = ?, statut = ? WHERE id = ?");
    $stmt->execute([$patient_id, $medecin_id, $note, $commentaire, $statut, $id]);
    
    header('Location: index.php?modifie=1');
    exit();
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <h1>✏️ Modifier l'avis</h1>
    
    <div class="form-card">
        <form method="POST">
            <div class="form-group">
                <label>Patient *</label>
                <select name="patient_id" required>
                    <?php foreach($patients as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $p['id'] == $avis['patient_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Médecin</label>
                <select name="medecin_id">
                    <option value="">-- Aucun --</option>
                    <?php foreach($medecins as $m): ?>
                    <option value="<?= $m['id'] ?>" <?= $m['id'] == $avis['medecin_id'] ? 'selected' : '' ?>>
                        Dr. <?= htmlspecialchars($m['prenom'] . ' ' . $m['nom']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Note *</label>
                <select name="note" required>
                    <option value="1" <?= $avis['note'] == 1 ? 'selected' : '' ?>>1 ⭐</option>
                    <option value="2" <?= $avis['note'] == 2 ? 'selected' : '' ?>>2 ⭐⭐</option>
                    <option value="3" <?= $avis['note'] == 3 ? 'selected' : '' ?>>3 ⭐⭐⭐</option>
                    <option value="4" <?= $avis['note'] == 4 ? 'selected' : '' ?>>4 ⭐⭐⭐⭐</option>
                    <option value="5" <?= $avis['note'] == 5 ? 'selected' : '' ?>>5 ⭐⭐⭐⭐⭐</option>
                </select>
            </div>
            <div class="form-group">
                <label>Commentaire *</label>
                <textarea name="commentaire" rows="4" required><?= htmlspecialchars($avis['commentaire']) ?></textarea>
            </div>
            <div class="form-group">
                <label>Statut</label>
                <select name="statut">
                    <option value="en_attente" <?= $avis['statut'] == 'en_attente' ? 'selected' : '' ?>>En attente</option>
                    <option value="publie" <?= $avis['statut'] == 'publie' ? 'selected' : '' ?>>Publié</option>
                    <option value="rejete" <?= $avis['statut'] == 'rejete' ? 'selected' : '' ?>>Rejeté</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
                <a href="index.php" class="btn btn-secondary">Retour</a>
            </div>
        </form>
    </div>
</div>

<style>
.form-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: var(--shadow);
    max-width: 600px;
}
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-weight: 500; margin-bottom: 6px; }
.form-group input, .form-group select, .form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--border);
    border-radius: 12px;
    font-size: 14px;
}
.form-actions { display: flex; gap: 12px; margin-top: 20px; }
.btn-secondary { background: #EBEBEB; padding: 12px 24px; border-radius: 40px; border: none; cursor: pointer; font-weight: 500; text-decoration: none; color: var(--text); }
.btn-primary { padding: 12px 24px; border-radius: 40px; border: none; background: var(--accent); color: white; font-weight: 500; cursor: pointer; }
</style>

<?php include '../../includes/footer.php'; ?>