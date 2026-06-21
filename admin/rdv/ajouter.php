<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../../login.php');
    exit();
}

require_once '../../config/database.php';
require_once '../../includes/functions.php';
$pdo = getPDO();

// Récupérer les patients
$patients = $pdo->query("SELECT id, nom, prenom FROM users WHERE role = 'patient' ORDER BY nom")->fetchAll();

// Récupérer les médecins
$medecins = $pdo->query("SELECT id, nom, prenom, specialite FROM users WHERE role = 'medecin' ORDER BY nom")->fetchAll();

$error = '';
$success = '';
$patient_id = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;
$date_default = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$from = isset($_GET['from']) ? $_GET['from'] : '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_id = $_POST['patient_id'];
    $medecin_id = $_POST['medecin_id'];
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
        $stmt = $pdo->prepare("INSERT INTO rendez_vous (patient_id, medecin_id, date_rdv, heure_rdv, motif, statut) VALUES (?, ?, ?, ?, ?, 'confirme')");
        $stmt->execute([$patient_id, $medecin_id, $date_rdv, $heure_rdv, $motif]);
        
        $_SESSION['message'] = "✅ Rendez-vous ajouté avec succès !";
        
        if($from == 'medecin') {
            header('Location: ../medecin/rdv/index.php?patient_id=' . $patient_id . '&date=' . $date_rdv . '&rdv_ajoute=1');
        } else {
            header('Location: index.php');
        }
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
            <input type="hidden" name="from" value="<?= $from ?>">
            
            <div class="form-group">
                <label>👤 Patient *</label>
                <select name="patient_id" required>
                    <option value="">-- Choisir un patient --</option>
                    <?php foreach($patients as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $p['id'] == $patient_id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>👨‍⚕️ Médecin *</label>
                <select name="medecin_id" required>
                    <option value="">-- Choisir un médecin --</option>
                    <?php foreach($medecins as $m): ?>
                    <option value="<?= $m['id'] ?>">
                        Dr. <?= htmlspecialchars($m['prenom'] . ' ' . $m['nom']) ?> (<?= $m['specialite'] ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>📅 Date *</label>
                <input type="date" name="date_rdv" value="<?= $date_default ?>" required>
            </div>
            
            <?= heureInput('heure_rdv', '', 'Heure', true) ?>
            
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
.alert-error {
    background: var(--pastel-peche);
    padding: 12px 20px;
    border-radius: 16px;
    margin-bottom: 20px;
    border-left: 4px solid #E07A5F;
}
</style>

<?php include '../../includes/footer.php'; ?>