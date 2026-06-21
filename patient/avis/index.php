<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header('Location: ../login.php');
    exit();
}

require_once '../../config/database.php';
$pdo = getPDO();
$patient_id = $_SESSION['user_id'];

// Récupérer les avis du patient
$stmt = $pdo->prepare("
    SELECT a.*, 
           m.nom as medecin_nom, m.prenom as medecin_prenom
    FROM avis a
    LEFT JOIN users m ON a.medecin_id = m.id
    WHERE a.patient_id = ?
    ORDER BY a.date_creation DESC
");
$stmt->execute([$patient_id]);
$avis = $stmt->fetchAll();

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <div>
            <h1>⭐ Mes avis</h1>
        </div>
        <a href="ajouter.php" class="btn btn-primary">➕ Donner un avis</a>
    </div>
    
    <div class="table-container">
        <div class="table-header-info">
            📋 <?= count($avis) ?> avis donné(s)
        </div>
        <table>
            <thead>
                <tr>
                    <th>Médecin</th>
                    <th>Note</th>
                    <th>Commentaire</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($avis) > 0): ?>
                    <?php foreach($avis as $a): ?>
                    <tr>
                        <td><?= $a['medecin_nom'] ? 'Dr. ' . htmlspecialchars($a['medecin_prenom'] . ' ' . $a['medecin_nom']) : '-' ?></td>
                        <td>
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <?= $i <= $a['note'] ? '⭐' : '☆' ?>
                            <?php endfor; ?>
                        </td>
                        <td><?= htmlspecialchars(substr($a['commentaire'], 0, 50)) . (strlen($a['commentaire']) > 50 ? '...' : '') ?></td>
                        <td><span class="badge badge-<?= $a['statut'] ?>"><?= $a['statut'] ?></span></td>
                        <td><?= date('d/m/Y', strtotime($a['date_creation'])) ?></td>
                        <td class="actions">
                            <?php if($a['statut'] == 'en_attente'): ?>
                                <a href="modifier.php?id=<?= $a['id'] ?>" class="action-btn action-edit">✏️ Modifier</a>
                                <a href="supprimer.php?id=<?= $a['id'] ?>" class="action-btn action-delete" onclick="return confirm('Supprimer cet avis ?')">🗑️ Supprimer</a>
                            <?php else: ?>
                                <span class="action-disabled">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="empty-result">
                            <div class="empty-icon">📭</div>
                            <div>Vous n'avez pas encore donné d'avis</div>
                            <a href="ajouter.php" class="btn btn-primary" style="margin-top:16px;">⭐ Donner un avis</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
}
.page-header h1 {
    color: var(--accent);
    font-size: 28px;
}
.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 40px;
    font-size: 12px;
    font-weight: 500;
}
.badge-en_attente { background: #FFF2CC; color: #856404; }
.badge-publie { background: var(--pastel-vert); color: #2D6A4F; }
.badge-rejete { background: var(--pastel-peche); color: #E07A5F; }
.actions { display: flex; gap: 8px; }
.action-btn { padding: 6px 12px; border-radius: 20px; text-decoration: none; font-size: 12px; }
.action-edit { background: var(--pastel-bleu); color: #2c5a7a; }
.action-delete { background: var(--pastel-peche); color: #c05a3f; }
.action-disabled { color: var(--text-light); }
.table-container {
    background: white;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: var(--shadow);
}
.table-header-info {
    padding: 14px 24px;
    background: var(--bg-primary);
    font-size: 13px;
    color: var(--text-light);
    border-bottom: 1px solid var(--border);
}
table { width: 100%; border-collapse: collapse; }
th { text-align: left; padding: 14px 16px; background: #F8F9FA; font-weight: 600; color: var(--accent); font-size: 13px; }
td { padding: 12px 16px; border-bottom: 1px solid var(--border); font-size: 13px; }
tr:hover { background: #FDFBF7; }
.empty-result { text-align: center; padding: 40px; color: var(--text-light); }
.empty-icon { font-size: 48px; margin-bottom: 16px; opacity: 0.6; }
.btn-primary {
    background: var(--accent);
    color: white;
    padding: 10px 20px;
    border-radius: 40px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.btn-primary:hover {
    background: #6B7F91;
    transform: translateY(-2px);
}
@media (max-width: 768px) {
    .page-header { flex-direction: column; align-items: flex-start; }
    .table-container { overflow-x: auto; }
    table { min-width: 600px; }
}
</style>

<?php include '../../includes/footer.php'; ?>