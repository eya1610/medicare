<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../../login.php');
    exit();
}

require_once '../../config/database.php';
$pdo = getPDO();

// Filtres
$medecin_id = $_GET['medecin'] ?? '';
$statut = $_GET['statut'] ?? '';
$date_filter = $_GET['date'] ?? '';

$sql = "SELECT r.*, 
        p.nom as patient_nom, p.prenom as patient_prenom,
        m.nom as medecin_nom, m.prenom as medecin_prenom, m.specialite
        FROM rendez_vous r
        JOIN users p ON r.patient_id = p.id
        JOIN users m ON r.medecin_id = m.id
        WHERE 1=1";

$params = [];
if($medecin_id) {
    $sql .= " AND r.medecin_id = ?";
    $params[] = $medecin_id;
}
if($statut) {
    $sql .= " AND r.statut = ?";
    $params[] = $statut;
}
if($date_filter) {
    $sql .= " AND r.date_rdv = ?";
    $params[] = $date_filter;
}

$sql .= " ORDER BY r.date_rdv DESC, r.heure_rdv ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rdvs = $stmt->fetchAll();

$medecins = $pdo->query("SELECT id, nom, prenom FROM users WHERE role = 'medecin' ORDER BY nom")->fetchAll();

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <div>
            <h1>📅 Gestion des rendez-vous</h1>
        </div>
        <div class="header-actions">
            <a href="../export/export_csv.php<?= (!empty($_GET)) ? '?' . http_build_query($_GET) : '' ?>" class="btn btn-csv" target="_blank">
                📊 Export CSV
            </a>
            <a href="export_pdf.php<?= (!empty($_GET)) ? '?' . http_build_query($_GET) : '' ?>" class="btn btn-export" target="_blank">
                📄 Export PDF
            </a>
        </div>
    </div>
    
    <!-- Nouveau rendez-vous EN DESSOUS -->
    <div style="display:flex; justify-content:flex-start; margin-bottom:24px;">
        <a href="ajouter.php" class="btn btn-primary">➕ Nouveau rendez-vous</a>
    </div>
    
    <!-- Filtres -->
    <form method="GET" class="filters">
        <div class="filter-group">
            <label>👨‍⚕️ Médecin</label>
            <select name="medecin" onchange="this.form.submit()">
                <option value="">Tous</option>
                <?php foreach($medecins as $m): ?>
                <option value="<?= $m['id'] ?>" <?= $medecin_id == $m['id'] ? 'selected' : '' ?>>
                    Dr. <?= htmlspecialchars($m['prenom'] . ' ' . $m['nom']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label>📌 Statut</label>
            <select name="statut" onchange="this.form.submit()">
                <option value="">Tous</option>
                <option value="en_attente" <?= $statut == 'en_attente' ? 'selected' : '' ?>>En attente</option>
                <option value="confirme" <?= $statut == 'confirme' ? 'selected' : '' ?>>Confirmé</option>
                <option value="termine" <?= $statut == 'termine' ? 'selected' : '' ?>>Terminé</option>
                <option value="annule" <?= $statut == 'annule' ? 'selected' : '' ?>>Annulé</option>
            </select>
        </div>
        <div class="filter-group">
            <label>📅 Date</label>
            <input type="date" name="date" value="<?= $date_filter ?>" onchange="this.form.submit()">
        </div>
        <div class="filter-group">
            <label>&nbsp;</label>
            <a href="index.php" class="btn btn-sm" style="background:#EBEBEB;">Réinitialiser</a>
        </div>
    </form>
    
    <div class="table-container">
        <div class="table-header-info">
            📋 <?= count($rdvs) ?> rendez-vous trouvé(s)
        </div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Patient</th>
                    <th>Médecin</th>
                    <th>Spécialité</th>
                    <th>Motif</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($rdvs) > 0): ?>
                    <?php foreach($rdvs as $r): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($r['date_rdv'])) ?></td>
                        <td><?= substr($r['heure_rdv'], 0, 5) ?></td>
                        <td><?= htmlspecialchars($r['patient_prenom'] . ' ' . $r['patient_nom']) ?></td>
                        <td>Dr. <?= htmlspecialchars($r['medecin_nom']) ?></td>
                        <td><?= htmlspecialchars($r['specialite']) ?></td>
                        <td><?= htmlspecialchars($r['motif']) ?></td>
                        <td><span class="badge badge-<?= $r['statut'] ?>"><?= $r['statut'] ?></span></td>
                        <td class="actions">
                            <a href="modifier.php?id=<?= $r['id'] ?>" class="action-btn action-edit">✏️</a>
                            <a href="supprimer.php?id=<?= $r['id'] ?>" class="action-btn action-delete" onclick="return confirm('Annuler ce rendez-vous ?')">🗑️</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align:center;padding:40px;color:var(--text-light);">
                            📭 Aucun rendez-vous trouvé
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
    margin-bottom: 6px;
}
.header-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

/* Bouton Nouveau rendez-vous (en dessous) */
.btn-primary {
    background: var(--accent);
    color: white;
    padding: 10px 24px;
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
    box-shadow: var(--shadow-sm);
}

/* Bouton CSV */
.btn-csv {
    background: #C5E0F7;
    color: #2c5a7a;
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
.btn-csv:hover {
    background: #a8c8e8;
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}

/* Bouton Export PDF */
.btn-export {
    background: #E07A5F;
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
.btn-export:hover {
    background: #c05a3f;
    color: white;
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}

.filters {
    background: white;
    padding: 20px;
    border-radius: 24px;
    margin-bottom: 24px;
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    box-shadow: var(--shadow);
}
.filter-group {
    flex: 1;
    min-width: 150px;
}
.filter-group label {
    display: block;
    margin-bottom: 8px;
    font-size: 12px;
    color: var(--text-light);
}
.filter-group select, .filter-group input {
    width: 100%;
    padding: 10px 14px;
    border: 2px solid var(--border);
    border-radius: 16px;
    font-size: 14px;
    background: var(--bg-primary);
}
.filter-group select:focus, .filter-group input:focus {
    outline: none;
    border-color: var(--accent);
}
.btn-sm {
    padding: 10px 20px;
    border-radius: 40px;
    border: none;
    cursor: pointer;
    font-weight: 500;
    text-decoration: none;
    display: inline-block;
}

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
table {
    width: 100%;
    border-collapse: collapse;
}
th {
    text-align: left;
    padding: 14px 16px;
    background: #F8F9FA;
    font-weight: 600;
    color: var(--accent);
    font-size: 13px;
}
td {
    padding: 12px 16px;
    border-bottom: 1px solid var(--border);
    font-size: 13px;
}
tr:hover {
    background: #FDFBF7;
}
.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 40px;
    font-size: 12px;
    font-weight: 500;
}
.badge-confirme {
    background: var(--pastel-vert);
    color: #2D6A4F;
}
.badge-en_attente {
    background: #FFF2CC;
    color: #856404;
}
.badge-termine {
    background: var(--pastel-bleu);
    color: #004085;
}
.badge-annule {
    background: var(--pastel-peche);
    color: #E07A5F;
}
.actions {
    display: flex;
    gap: 8px;
}
.action-btn {
    padding: 6px 12px;
    border-radius: 20px;
    text-decoration: none;
    font-size: 12px;
}
.action-edit {
    background: var(--pastel-bleu);
    color: #2c5a7a;
}
.action-delete {
    background: var(--pastel-peche);
    color: #c05a3f;
}

@media (max-width: 768px) {
    .filters {
        flex-direction: column;
    }
    .filter-group {
        min-width: 100%;
    }
    .header-actions {
        width: 100%;
    }
    .header-actions a {
        flex: 1;
        text-align: center;
        justify-content: center;
    }
    .table-container {
        overflow-x: auto;
    }
    table {
        min-width: 800px;
    }
}
</style>

<?php include '../../includes/footer.php'; ?>