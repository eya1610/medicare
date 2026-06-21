<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header('Location: ../../login.php');
    exit();
}

require_once '../../config/database.php';
$pdo = getPDO();
$id = $_GET['id'] ?? 0;
$patient_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM rendez_vous WHERE id = ? AND patient_id = ?");
$stmt->execute([$id, $patient_id]);
$rdv = $stmt->fetch();

if(!$rdv || ($rdv['statut'] != 'en_attente' && $rdv['statut'] != 'confirme')) {
    header('Location: index.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date_rdv = $_POST['date_rdv'];
    $heure_rdv = $_POST['heure_rdv'];
    $motif = $_POST['motif'];
    
    $stmt = $pdo->prepare("UPDATE rendez_vous SET date_rdv = ?, heure_rdv = ?, motif = ? WHERE id = ?");
    $stmt->execute([$date_rdv, $heure_rdv, $motif, $id]);
    header('Location: index.php?modifie=1');
    exit();
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <h1>✏️ Modifier mon rendez-vous</h1>
    
    <form method="POST" class="table-container" style="padding: 24px;">
        <div class="form-group">
            <label>Date</label>
            <input type="date" name="date_rdv" value="<?= $rdv['date_rdv'] ?>" required>
        </div>
        <div class="form-group">
            <label>Heure</label>
            <input type="time" name="heure_rdv" value="<?= $rdv['heure_rdv'] ?>" required>
        </div>
        <div class="form-group">
            <label>Motif</label>
            <input type="text" name="motif" value="<?= htmlspecialchars($rdv['motif']) ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
        <a href="index.php" class="btn" style="background:#EBEBEB;">Retour</a>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>