<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header('Location: ../../login.php');
    exit();
}

require_once '../../config/database.php';
$pdo = getPDO();
$patient_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT r.*, u.nom as medecin_nom, u.prenom as medecin_prenom, u.specialite
    FROM rendez_vous r
    JOIN users u ON r.medecin_id = u.id
    WHERE r.patient_id = ?
    ORDER BY r.date_rdv DESC, r.heure_rdv ASC
");
$stmt->execute([$patient_id]);
$rdvs = $stmt->fetchAll();

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <h1>📅 Mes rendez-vous</h1>
    <a href="ajouter.php" class="btn btn-primary" style="margin-bottom: 20px;">➕ Prendre un rendez-vous</a>
    
    <div class="table-container">
        <table>
            <thead>
                <tr><th>Date</th><th>Heure</th><th>Médecin</th><th>Spécialité</th><th>Motif</th><th>Statut</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach($rdvs as $r): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($r['date_rdv'])) ?></td>
                    <td><?= substr($r['heure_rdv'], 0, 5) ?></td>
                    <td>Dr. <?= htmlspecialchars($r['medecin_nom']) ?></td>
                    <td><?= htmlspecialchars($r['specialite']) ?></td>
                    <td><?= htmlspecialchars($r['motif']) ?></td>
                    <td><span class="badge badge-<?= $r['statut'] ?>"><?= $r['statut'] ?></span></td>
                    <td>
                        <?php if($r['statut'] == 'en_attente' || $r['statut'] == 'confirme'): ?>
                            <a href="annuler.php?id=<?= $r['id'] ?>" class="action-btn action-delete" onclick="return confirm('Annuler ce rendez-vous ?')">🗑️ Annuler</a>
                            <a href="modifier.php?id=<?= $r['id'] ?>" class="action-btn action-edit">✏️ Modifier</a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>