<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'medecin') {
    header('Location: ../login.php');
    exit();
}

require_once '../config/database.php';
$pdo = getPDO();
$medecin_id = $_SESSION['user_id'];

// Cartes KPI
$stmt = $pdo->prepare("SELECT COUNT(*) FROM rendez_vous WHERE medecin_id = ? AND date_rdv = CURDATE()");
$stmt->execute([$medecin_id]);
$rdvAujourdhui = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM rendez_vous WHERE medecin_id = ? AND date_rdv > CURDATE()");
$stmt->execute([$medecin_id]);
$rdvAVenir = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM rendez_vous WHERE medecin_id = ?");
$stmt->execute([$medecin_id]);
$totalRdvs = $stmt->fetchColumn();

// Rendez-vous du jour
$stmt = $pdo->prepare("
    SELECT r.*, u.nom as patient_nom, u.prenom as patient_prenom, u.telephone
    FROM rendez_vous r
    JOIN users u ON r.patient_id = u.id
    WHERE r.medecin_id = ? AND r.date_rdv = CURDATE() AND r.statut != 'annule'
    ORDER BY r.heure_rdv
");
$stmt->execute([$medecin_id]);
$rdvDuJour = $stmt->fetchAll();

// Prochains rendez-vous
$stmt = $pdo->prepare("
    SELECT r.*, u.nom as patient_nom, u.prenom as patient_prenom
    FROM rendez_vous r
    JOIN users u ON r.patient_id = u.id
    WHERE r.medecin_id = ? AND r.date_rdv >= CURDATE()
    ORDER BY r.date_rdv ASC, r.heure_rdv ASC
    LIMIT 10
");
$stmt->execute([$medecin_id]);
$prochainsRdvs = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="welcome-header">
        <h1>👋 Bonjour Dr. <?= htmlspecialchars($_SESSION['nom']) ?></h1>
    </div>
    
    <div class="dashboard-stats">
        <div class="stat-card">
            <div class="stat-title">📅 RDV aujourd'hui</div>
            <div class="stat-value"><?= $rdvAujourdhui ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-title">📆 RDV à venir</div>
            <div class="stat-value"><?= $rdvAVenir ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-title">📊 Total RDV</div>
            <div class="stat-value"><?= $totalRdvs ?></div>
        </div>
    </div>
    
    <?php if(count($rdvDuJour) > 0): ?>
    <div class="notification-card">
        <h3>📋 Vos rendez-vous aujourd'hui</h3>
        <?php foreach($rdvDuJour as $rdv): ?>
        <div class="notification-item">
            <strong><?= substr($rdv['heure_rdv'], 0, 5) ?></strong> - 
            <?= htmlspecialchars($rdv['patient_prenom']) ?> <?= htmlspecialchars($rdv['patient_nom']) ?>
            (📞 <?= $rdv['telephone'] ?>) - <?= htmlspecialchars($rdv['motif']) ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <div class="table-container">
        <h3>📅 Vos prochains rendez-vous</h3>
        <table>
            <thead>
                <tr><th>Date</th><th>Heure</th><th>Patient</th><th>Motif</th><th>Statut</th></tr>
            </thead>
            <tbody>
                <?php foreach($prochainsRdvs as $rdv): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($rdv['date_rdv'])) ?></td>
                    <td><?= substr($rdv['heure_rdv'], 0, 5) ?></td>
                    <td><?= htmlspecialchars($rdv['patient_prenom'] . ' ' . $rdv['patient_nom']) ?></td>
                    <td><?= htmlspecialchars($rdv['motif']) ?></td>
                    <td><span class="badge badge-<?= $rdv['statut'] ?>"><?= $rdv['statut'] ?></span></td>
                </tr>
                <?php endforeach; ?>
                <?php if(count($prochainsRdvs) == 0): ?>
                <tr><td colspan="5" style="text-align: center;">Aucun rendez-vous à venir</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>