<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../../login.php');
    exit();
}

require_once '../../config/database.php';
$pdo = getPDO();

$patients = $pdo->query("SELECT id, nom, prenom FROM users WHERE role = 'patient' ORDER BY nom")->fetchAll();

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_id = $_POST['patient_id'];
    $sujet = trim($_POST['sujet']);
    $description = trim($_POST['description']);
    $statut = $_POST['statut'];
    
    if(empty($patient_id) || empty($sujet) || empty($description)) {
        $error = "Veuillez remplir tous les champs";
    } else {
        $stmt = $pdo->prepare("INSERT INTO reclamations (patient_id, sujet, description, statut) VALUES (?, ?, ?, ?)");
        $stmt->execute([$patient_id, $sujet, $description, $statut]);
        $success = "✅ Réclamation ajoutée avec succès !";
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <h1>➕ Ajouter une réclamation</h1>
    
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
                <label>Sujet *</label>
                <input type="text" name="sujet" required>
            </div>
            <div class="form-group">
                <label>Description *</label>
                <textarea name="description" rows="5" required></textarea>
            </div>
            <div class="form-group">
                <label>Statut</label>
                <select name="statut">
                    <option value="en_attente">En attente</option>
                    <option value="en_cours">En cours</option>
                    <option value="traite">Traité</option>
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

<?php include '../../includes/footer.php'; ?>