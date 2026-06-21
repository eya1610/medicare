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

// Récupérer l'avis
$stmt = $pdo->prepare("SELECT * FROM avis WHERE id = ? AND patient_id = ?");
$stmt->execute([$id, $patient_id]);
$avis = $stmt->fetch();

if(!$avis || $avis['statut'] != 'en_attente') {
    header('Location: index.php');
    exit();
}

$medecins = $pdo->query("SELECT id, nom, prenom, specialite FROM users WHERE role = 'medecin' ORDER BY nom")->fetchAll();

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $medecin_id = $_POST['medecin_id'] ?? null;
    $note = (int)$_POST['note'];
    $commentaire = trim($_POST['commentaire']);
    
    $stmt = $pdo->prepare("UPDATE avis SET medecin_id = ?, note = ?, commentaire = ? WHERE id = ? AND patient_id = ?");
    $stmt->execute([$medecin_id ?: null, $note, $commentaire, $id, $patient_id]);
    
    header('Location: index.php?modifie=1');
    exit();
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <h1>✏️ Modifier mon avis</h1>
    
    <div class="form-card">
        <form method="POST">
            <div class="form-group">
                <label>👨‍⚕️ Médecin</label>
                <select name="medecin_id">
                    <option value="">-- Aucun --</option>
                    <?php foreach($medecins as $m): ?>
                    <option value="<?= $m['id'] ?>" <?= $m['id'] == $avis['medecin_id'] ? 'selected' : '' ?>>
                        Dr. <?= htmlspecialchars($m['prenom'] . ' ' . $m['nom']) ?> (<?= $m['specialite'] ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Note *</label>
                <div class="note-selector">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                    <label class="note-option <?= $i == $avis['note'] ? 'active' : '' ?>">
                        <input type="radio" name="note" value="<?= $i ?>" <?= $i == $avis['note'] ? 'checked' : '' ?> required>
                        <span><?= $i ?> ⭐</span>
                    </label>
                    <?php endfor; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label>Commentaire *</label>
                <textarea name="commentaire" rows="5" required><?= htmlspecialchars($avis['commentaire']) ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
                <a href="index.php" class="btn btn-secondary">Retour</a>
            </div>
        </form>
    </div>
</div>

<style>
.form-card { background: white; border-radius: 16px; padding: 24px; box-shadow: var(--shadow); max-width: 600px; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-weight: 500; margin-bottom: 6px; }
.form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px 16px; border: 2px solid var(--border); border-radius: 12px; font-size: 14px; }
.note-selector { display: flex; gap: 12px; flex-wrap: wrap; }
.note-option {
    display: flex;
    align-items: center;
    gap: 4px;
    cursor: pointer;
    padding: 8px 14px;
    border: 2px solid var(--border);
    border-radius: 8px;
    transition: all 0.3s;
}
.note-option:hover { border-color: var(--accent); background: var(--bg-primary); }
.note-option.active { border-color: var(--accent); background: var(--pastel-lavande); }
.note-option input[type="radio"] { width: auto; padding: 0; }
.form-actions { display: flex; gap: 12px; margin-top: 20px; }
.btn-secondary { background: #EBEBEB; padding: 12px 24px; border-radius: 40px; border: none; cursor: pointer; font-weight: 500; text-decoration: none; color: var(--text); }
.btn-primary { padding: 12px 24px; border-radius: 40px; border: none; background: var(--accent); color: white; font-weight: 500; cursor: pointer; }
</style>

<?php include '../../includes/footer.php'; ?>