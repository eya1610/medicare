<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header('Location: ../login.php');
    exit();
}

require_once '../../config/database.php';
$pdo = getPDO();
$patient_id = $_SESSION['user_id'];

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sujet = trim($_POST['sujet']);
    $description = trim($_POST['description']);
    
    if(empty($sujet) || empty($description)) {
        $error = "Veuillez remplir tous les champs";
    } else {
        $stmt = $pdo->prepare("INSERT INTO reclamations (patient_id, sujet, description, statut) VALUES (?, ?, ?, 'en_attente')");
        $stmt->execute([$patient_id, $sujet, $description]);
        $success = "✅ Votre réclamation a été envoyée avec succès ! Nous la traiterons dans les plus brefs délais.";
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <h1>📩 Nouvelle réclamation</h1>
    
    <?php if($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>
    <?php if($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    
    <div class="form-card">
        <form method="POST">
            <div class="form-group">
                <label>Sujet *</label>
                <input type="text" name="sujet" placeholder="Résumé de votre réclamation..." required>
            </div>
            <div class="form-group">
                <label>Description *</label>
                <textarea name="description" rows="6" placeholder="Décrivez votre réclamation en détail..." required></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">📤 Envoyer la réclamation</button>
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
.form-group input, .form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--border);
    border-radius: 12px;
    font-size: 14px;
}
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
.alert-error { background: var(--pastel-peche); padding: 12px 16px; border-radius: 12px; color: #E07A5F; margin-bottom: 16px; }
.alert-success { background: var(--pastel-vert); padding: 12px 16px; border-radius: 12px; color: #2D6A4F; margin-bottom: 16px; }
</style>

<?php include '../../includes/footer.php'; ?>