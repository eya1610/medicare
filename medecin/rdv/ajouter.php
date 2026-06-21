<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'medecin') {
    header('Location: ../../login.php');
    exit();
}

require_once '../../config/database.php';
require_once '../../includes/functions.php';
$pdo = getPDO();

$medecin_id = $_SESSION['user_id'];

// Récupérer les patients
$patients = $pdo->query("SELECT id, nom, prenom FROM users WHERE role = 'patient' ORDER BY nom")->fetchAll();

$error = '';
$date_default = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_id = $_POST['patient_id'];
    $date_rdv = $_POST['date_rdv'];
    $heure_rdv = $_POST['heure_rdv'];
    $motif = $_POST['motif'];
    
    // Vérification anti-double réservation
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM rendez_vous WHERE medecin_id = ? AND date_rdv = ? AND heure_rdv = ? AND statut != 'annule'");
    $stmt->execute([$medecin_id, $date_rdv, $heure_rdv]);
    $exists = $stmt->fetchColumn();
    
    if($exists > 0) {
        $error = "Ce créneau est déjà pris ! Choisissez un autre horaire.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO rendez_vous (patient_id, medecin_id, date_rdv, heure_rdv, motif, statut) VALUES (?, ?, ?, ?, ?, 'en_attente')");
        $stmt->execute([$patient_id, $medecin_id, $date_rdv, $heure_rdv, $motif]);
        
        $_SESSION['message'] = "✅ Rendez-vous ajouté avec succès !";
        header('Location: index.php');
        exit();
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <h1>➕ Ajouter un rendez-vous</h1>
    
    <?php if($error): ?>
        <div class="alert alert-error" style="background:var(--pastel-peche);padding:12px 20px;border-radius:16px;margin-bottom:20px;border-left:4px solid #E07A5F;">
            <?= $error ?>
        </div>
    <?php endif; ?>
    
    <div class="form-card">
        <form method="POST">
            <div class="form-group">
                <label>👤 Patient *</label>
                <select name="patient_id" required>
                    <option value="">-- Choisir un patient --</option>
                    <?php foreach($patients as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>📅 Date *</label>
                <input type="date" name="date_rdv" value="<?= $date_default ?>" required>
            </div>
            <div class="form-group">
                <label>⏰ Heure *</label>
                <input type="time" name="heure_rdv" required>
            </div>
            <div class="form-group">
                <label>📝 Motif *</label>
                <input type="text" name="motif" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">✅ Créer le rendez-vous</button>
                <a href="index.php" class="btn btn-secondary">Retour</a>
            </div>
        </form>
    </div>
</div>

<style>
.form-card {
    background: white;
    border-radius: 24px;
    padding: 24px;
    box-shadow: var(--shadow);
    max-width: 600px;
}
.form-group {
    margin-bottom: 16px;
}
.form-group label {
    display: block;
    font-weight: 500;
    margin-bottom: 6px;
    font-size: 13px;
    color: var(--text-light);
}
.form-group input, .form-group select {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--border);
    border-radius: 16px;
    font-size: 14px;
}
.form-group input:focus, .form-group select:focus {
    outline: none;
    border-color: var(--accent);
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