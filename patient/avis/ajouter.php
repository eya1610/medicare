<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header('Location: ../login.php');
    exit();
}

require_once '../../config/database.php';
$pdo = getPDO();
$patient_id = $_SESSION['user_id'];

// Récupérer les médecins
$medecins = $pdo->query("SELECT id, nom, prenom, specialite FROM users WHERE role = 'medecin' ORDER BY nom")->fetchAll();

$error = '';
$success = '';

// Vérifier si le patient a déjà donné un avis récemment (7 jours)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM avis WHERE patient_id = ? AND date_creation > DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stmt->execute([$patient_id]);
$avisRecents = $stmt->fetchColumn();

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $medecin_id = $_POST['medecin_id'] ?? null;
    $note = (int)$_POST['note'];
    $commentaire = trim($_POST['commentaire']);
    
    if(empty($commentaire) || $note < 1 || $note > 5) {
        $error = "Veuillez remplir tous les champs correctement";
    } else {
        $stmt = $pdo->prepare("INSERT INTO avis (patient_id, medecin_id, note, commentaire, statut) VALUES (?, ?, ?, ?, 'en_attente')");
        $stmt->execute([$patient_id, $medecin_id ?: null, $note, $commentaire]);
        $success = "✅ Votre avis a été envoyé avec succès ! Il sera publié après validation.";
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <h1>⭐ Donner un avis</h1>
    
    <?php if($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>
    <?php if($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    
    <?php if($avisRecents > 0): ?>
        <div class="alert alert-warning">⚠️ Vous avez déjà donné un avis récemment. Vous pouvez en donner un nouveau après 7 jours.</div>
    <?php endif; ?>
    
    <div class="form-card">
        <form method="POST">
            <div class="form-group">
                <label>👨‍⚕️ Médecin (optionnel)</label>
                <select name="medecin_id">
                    <option value="">-- Aucun médecin spécifique --</option>
                    <?php foreach($medecins as $m): ?>
                    <option value="<?= $m['id'] ?>">Dr. <?= htmlspecialchars($m['prenom'] . ' ' . $m['nom']) ?> (<?= $m['specialite'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Note *</label>
                <div class="note-selector">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                    <label class="note-option">
                        <input type="radio" name="note" value="<?= $i ?>" required>
                        <span><?= $i ?> ⭐</span>
                    </label>
                    <?php endfor; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label>Commentaire *</label>
                <textarea name="commentaire" rows="5" placeholder="Partagez votre expérience..." required></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary" <?= $avisRecents > 0 ? 'disabled' : '' ?>>📤 Envoyer l'avis</button>
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
.note-option input[type="radio"] { width: auto; padding: 0; }
.form-actions { display: flex; gap: 12px; margin-top: 20px; }
.btn-secondary {
    background: #EBEBEB;
    padding: 12px 24px;
    border-radius: 40px;
    border: none;
    cursor: pointer;
    font-weight: 500;
    text-decoration: none;
    color: var(--text);
}
.btn-primary {
    padding: 12px 24px;
    border-radius: 40px;
    border: none;
    background: var(--accent);
    color: white;
    font-weight: 500;
    cursor: pointer;
}
.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
.alert-error { background: var(--pastel-peche); padding: 12px 16px; border-radius: 12px; color: #E07A5F; margin-bottom: 16px; }
.alert-success { background: var(--pastel-vert); padding: 12px 16px; border-radius: 12px; color: #2D6A4F; margin-bottom: 16px; }
.alert-warning { background: #FFF2CC; padding: 12px 16px; border-radius: 12px; color: #856404; margin-bottom: 16px; }
</style>

<?php include '../../includes/footer.php'; ?>