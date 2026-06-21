<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../../login.php');
    exit();
}

require_once '../../config/database.php';
$pdo = getPDO();
$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT r.*, u.nom as patient_nom, u.prenom as patient_prenom FROM reclamations r JOIN users u ON r.patient_id = u.id WHERE r.id = ?");
$stmt->execute([$id]);
$reclamation = $stmt->fetch();

if(!$reclamation) {
    header('Location: index.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $statut = $_POST['statut'];
    $reponse = trim($_POST['reponse']);
    
    $stmt = $pdo->prepare("UPDATE reclamations SET statut = ?, reponse = ?, date_traitement = NOW() WHERE id = ?");
    $stmt->execute([$statut, $reponse, $id]);
    
    header('Location: index.php?traite=1');
    exit();
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <h1>✅ Traiter une réclamation</h1>
    
    <div class="form-card">
        <div class="reclamation-info">
            <p><strong>👤 Patient :</strong> <?= htmlspecialchars($reclamation['patient_prenom'] . ' ' . $reclamation['patient_nom']) ?></p>
            <p><strong>📌 Sujet :</strong> <?= htmlspecialchars($reclamation['sujet']) ?></p>
            <p><strong>📅 Date :</strong> <?= date('d/m/Y à H:i', strtotime($reclamation['date_creation'])) ?></p>
            <p><strong>📝 Description :</strong></p>
            <div class="description-box"><?= nl2br(htmlspecialchars($reclamation['description'])) ?></div>
            <?php if($reclamation['reponse']): ?>
            <p><strong>💬 Réponse précédente :</strong></p>
            <div class="description-box response"><?= nl2br(htmlspecialchars($reclamation['reponse'])) ?></div>
            <?php endif; ?>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label>Statut</label>
                <select name="statut" required>
                    <option value="en_attente" <?= $reclamation['statut'] == 'en_attente' ? 'selected' : '' ?>>En attente</option>
                    <option value="en_cours" <?= $reclamation['statut'] == 'en_cours' ? 'selected' : '' ?>>En cours</option>
                    <option value="traite" <?= $reclamation['statut'] == 'traite' ? 'selected' : '' ?>>Traité</option>
                    <option value="rejete" <?= $reclamation['statut'] == 'rejete' ? 'selected' : '' ?>>Rejeté</option>
                </select>
            </div>
            <div class="form-group">
                <label>Réponse</label>
                <textarea name="reponse" rows="5" placeholder="Votre réponse à la réclamation..."><?= htmlspecialchars($reclamation['reponse'] ?? '') ?></textarea>
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
    max-width: 700px;
}
.reclamation-info {
    background: var(--bg-primary);
    padding: 16px;
    border-radius: 12px;
    margin-bottom: 20px;
}
.reclamation-info p { margin-bottom: 8px; }
.description-box {
    background: white;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid var(--border);
    margin-top: 4px;
    margin-bottom: 12px;
}
.description-box.response {
    border-color: var(--pastel-vert);
    background: #F8FDFA;
}
.form-group {
    margin-bottom: 16px;
}
.form-group label {
    display: block;
    font-weight: 500;
    margin-bottom: 6px;
}
.form-group input, .form-group select, .form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--border);
    border-radius: 12px;
    font-size: 14px;
}
.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 20px;
}
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
</style>

<?php include '../../includes/footer.php'; ?>