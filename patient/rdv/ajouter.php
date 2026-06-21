<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header('Location: ../../login.php');
    exit();
}

require_once '../../config/database.php';
$pdo = getPDO();
$patient_id = $_SESSION['user_id'];
$error = '';
$success = '';

$medecins = $pdo->query("SELECT id, nom, prenom, specialite FROM users WHERE role = 'medecin' ORDER BY nom")->fetchAll();

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $medecin_id = $_POST['medecin_id'];
    $date_rdv = $_POST['date_rdv'];
    $heure_rdv = $_POST['heure_rdv'];
    $motif = $_POST['motif'];
    
    // Anti-double réservation
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM rendez_vous WHERE medecin_id = ? AND date_rdv = ? AND heure_rdv = ? AND statut != 'annule'");
    $stmt->execute([$medecin_id, $date_rdv, $heure_rdv]);
    $exists = $stmt->fetchColumn();
    
    if($exists > 0) {
        $error = "Ce créneau est déjà pris !";
    } else {
        $stmt = $pdo->prepare("INSERT INTO rendez_vous (patient_id, medecin_id, date_rdv, heure_rdv, motif, statut) VALUES (?, ?, ?, ?, ?, 'en_attente')");
        $stmt->execute([$patient_id, $medecin_id, $date_rdv, $heure_rdv, $motif]);
        $success = "Rendez-vous demandé avec succès ! En attente de confirmation.";
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <h1>➕ Prendre un rendez-vous</h1>
    
    <?php if($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
    <?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    
    <form method="POST" class="table-container" style="padding: 24px;">
        <div class="form-group">
            <label>Médecin *</label>
            <select name="medecin_id" required>
                <option value="">-- Choisir un médecin --</option>
                <?php foreach($medecins as $m): ?>
                <option value="<?= $m['id'] ?>">Dr. <?= htmlspecialchars($m['prenom'] . ' ' . $m['nom']) ?> - <?= $m['specialite'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Date *</label>
            <input type="date" name="date_rdv" min="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="form-group">
            <label>Heure *</label>
            <input type="time" name="heure_rdv" required>
        </div>
        <div class="form-group">
            <label>Motif *</label>
            <input type="text" name="motif" required>
        </div>
        <button type="submit" class="btn btn-primary">📅 Demander le rendez-vous</button>
        <a href="index.php" class="btn" style="background:#EBEBEB;">Retour</a>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>