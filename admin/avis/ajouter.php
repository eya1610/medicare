<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../../login.php');
    exit();
}

require_once '../../config/database.php';
$pdo = getPDO();

// Liste des patients et médecins
$patients = $pdo->query("SELECT id, nom, prenom FROM users WHERE role = 'patient' ORDER BY nom")->fetchAll();
$medecins = $pdo->query("SELECT id, nom, prenom FROM users WHERE role = 'medecin' ORDER BY nom")->fetchAll();

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_id = $_POST['patient_id'];
    $medecin_id = $_POST['medecin_id'] ?: null;
    $note = (int)$_POST['note'];
    $commentaire = trim($_POST['commentaire']);
    $statut = $_POST['statut'];
    
    if(empty($patient_id) || empty($commentaire) || $note < 1 || $note > 5) {
        $error = "Veuillez remplir tous les champs correctement";
    } else {
        $stmt = $pdo->prepare("INSERT INTO avis (patient_id, medecin_id, note, commentaire, statut) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$patient_id, $medecin_id, $note, $commentaire, $statut]);
        $success = "✅ Avis ajouté avec succès !";
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <h1>➕ Ajouter un avis</h1>
    
    <?php if($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
    <?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    
    <div class="form-card">
        <form method="POST">
            <div class="form-group">
                <label>Patient *</label>
                <select name="patient_id" required>
                    <option value="">-- Choisir un patient --</option>
                    <?php foreach($patients as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Médecin (optionnel)</label>
                <select name="medecin_id">
                    <option value="">-- Aucun --</option>
                    <?php foreach($medecins as $m): ?>
                    <option value="<?= $m['id'] ?>">Dr. <?= htmlspecialchars($m['prenom'] . ' ' . $m['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Note *</label>
                <select name="note" required>
                    <option value="">-- Choisir une note --</option>
                    <option value="1">1 ⭐</option>
                    <option value="2">2 ⭐⭐</option>
                    <option value="3">3 ⭐⭐⭐</option>
                    <option value="4">4 ⭐⭐⭐⭐</option>
                    <option value="5">5 ⭐⭐⭐⭐⭐</option>
                </select>
            </div>
            <div class="form-group">
                <label>Commentaire *</label>
                <textarea name="commentaire" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label>Statut</label>
                <select name="statut">
                    <option value="en_attente">En attente</option>
                    <option value="publie">Publié</option>
                    <option value="rejete">Rejeté</option>
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
.alert-error { background: var(--pastel-peche); padding: 12px 16px; border-radius: 12px; color: #E07A5F; margin-bottom: 16px; }
.alert-success { background: var(--pastel-vert); padding: 12px 16px; border-radius: 12px; color: #2D6A4F; margin-bottom: 16px; }
</style>

<?php include '../../includes/footer.php'; ?>