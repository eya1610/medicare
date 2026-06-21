<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

require_once '../config/database.php';
$pdo = getPDO();

// Statistiques
$totalPatients = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'patient'")->fetchColumn();
$totalMedecins = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'medecin'")->fetchColumn();
$totalRdvs = $pdo->query("SELECT COUNT(*) FROM rendez_vous")->fetchColumn();
$rdvAujourdhui = $pdo->query("SELECT COUNT(*) FROM rendez_vous WHERE date_rdv = CURDATE()")->fetchColumn();
$rdvMois = $pdo->query("SELECT COUNT(*) FROM rendez_vous WHERE MONTH(date_rdv) = MONTH(CURDATE()) AND YEAR(date_rdv) = YEAR(CURDATE())")->fetchColumn();
$rdvEnAttente = $pdo->query("SELECT COUNT(*) FROM rendez_vous WHERE statut = 'en_attente'")->fetchColumn();

// Statistiques Avis et Réclamations
$totalAvis = $pdo->query("SELECT COUNT(*) FROM avis")->fetchColumn();
$totalReclamations = $pdo->query("SELECT COUNT(*) FROM reclamations")->fetchColumn();
$avisEnAttente = $pdo->query("SELECT COUNT(*) FROM avis WHERE statut = 'en_attente'")->fetchColumn();
$reclamationsEnAttente = $pdo->query("SELECT COUNT(*) FROM reclamations WHERE statut = 'en_attente'")->fetchColumn();

// RDV du jour
$stmt = $pdo->prepare("
    SELECT r.*, 
           p.nom as patient_nom, p.prenom as patient_prenom,
           m.nom as medecin_nom, m.prenom as medecin_prenom
    FROM rendez_vous r
    JOIN users p ON r.patient_id = p.id
    JOIN users m ON r.medecin_id = m.id
    WHERE r.date_rdv = CURDATE() AND r.statut != 'annule'
    ORDER BY r.heure_rdv
");
$stmt->execute();
$rdvDuJour = $stmt->fetchAll();

// Prochains RDV
$stmt = $pdo->prepare("
    SELECT r.*, 
           p.nom as patient_nom, p.prenom as patient_prenom,
           m.nom as medecin_nom, m.prenom as medecin_prenom
    FROM rendez_vous r
    JOIN users p ON r.patient_id = p.id
    JOIN users m ON r.medecin_id = m.id
    WHERE r.date_rdv >= CURDATE() AND r.statut != 'annule'
    ORDER BY r.date_rdv ASC, r.heure_rdv ASC
    LIMIT 5
");
$stmt->execute();
$prochainsRdvs = $stmt->fetchAll();

// Derniers avis
$stmt = $pdo->query("
    SELECT a.*, u.nom as patient_nom, u.prenom as patient_prenom
    FROM avis a
    JOIN users u ON a.patient_id = u.id
    WHERE a.statut = 'en_attente'
    ORDER BY a.date_creation DESC
    LIMIT 3
");
$derniersAvis = $stmt->fetchAll();

// Dernières réclamations
$stmt = $pdo->query("
    SELECT r.*, u.nom as patient_nom, u.prenom as patient_prenom
    FROM reclamations r
    JOIN users u ON r.patient_id = u.id
    WHERE r.statut = 'en_attente'
    ORDER BY r.date_creation DESC
    LIMIT 3
");
$dernieresReclamations = $stmt->fetchAll();

// Notifications système
$notifications = [
    ['icon' => '✅', 'message' => '3 nouveaux patients ce mois-ci', 'time' => 'Il y a 2h'],
    ['icon' => '📅', 'message' => '5 rendez-vous à confirmer', 'time' => 'Il y a 4h'],
    ['icon' => '👨‍⚕️', 'message' => 'Dr. Dupont a rejoint la clinique', 'time' => 'Hier'],
    ['icon' => '📊', 'message' => 'Le taux de satisfaction est à 94%', 'time' => 'Hier'],
];

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <!-- Bandeau de bienvenue -->
    <div class="welcome-banner">
        <div>
            <h1>👋 Bonjour, <?= htmlspecialchars($_SESSION['prenom']) ?></h1>
            <p class="banner-sub">Voici votre résumé quotidien</p>
        </div>
        <div class="banner-stats">
            <div class="banner-stat">
                <span class="banner-number"><?= $rdvAujourdhui ?></span>
                <span class="banner-label">RDV aujourd'hui</span>
            </div>
            <div class="banner-stat">
                <span class="banner-number"><?= $rdvEnAttente ?></span>
                <span class="banner-label">En attente</span>
            </div>
            <div class="banner-stat">
                <span class="banner-number"><?= $totalPatients ?></span>
                <span class="banner-label">Patients</span>
            </div>
        </div>
    </div>

    <!-- Widgets météo / indicateurs -->
    <div class="widgets-row">
        <div class="widget-card weather">
            <div class="widget-icon">🌤️</div>
            <div class="widget-content">
                <span class="widget-value">24°</span>
                <span class="widget-label">Tunis, Ensoleillé</span>
            </div>
        </div>
        <div class="widget-card satisfaction">
            <div class="widget-icon">😊</div>
            <div class="widget-content">
                <span class="widget-value">94%</span>
                <span class="widget-label">Satisfaction</span>
            </div>
        </div>
        <div class="widget-card occupancy">
            <div class="widget-icon">📊</div>
            <div class="widget-content">
                <span class="widget-value"><?= $totalRdvs > 0 ? round(($rdvAujourdhui / $totalRdvs) * 100) : 0 ?>%</span>
                <span class="widget-label">Taux d'occupation</span>
            </div>
        </div>
        <div class="widget-card trend">
            <div class="widget-icon">📈</div>
            <div class="widget-content">
                <span class="widget-value">+15%</span>
                <span class="widget-label">Activité du mois</span>
            </div>
        </div>
    </div>

    <!-- Section principale : Actions rapides + Notifications -->
    <div class="main-grid">
        <!-- Actions rapides -->
        <div class="quick-actions-card">
            <h3>⚡ Actions rapides</h3>
            <div class="quick-grid">
                <a href="patients/ajouter.php" class="quick-action">
                    <span class="qa-icon">➕</span>
                    <span class="qa-label">Nouveau patient</span>
                </a>
                <a href="medecins/ajouter.php" class="quick-action">
                    <span class="qa-icon">👨‍⚕️</span>
                    <span class="qa-label">Nouveau médecin</span>
                </a>
                <a href="rdv/ajouter.php" class="quick-action">
                    <span class="qa-icon">📅</span>
                    <span class="qa-label">Nouveau RDV</span>
                </a>
                <a href="rdv/index.php?statut=en_attente" class="quick-action">
                    <span class="qa-icon">⏳</span>
                    <span class="qa-label">RDV en attente</span>
                    <span class="qa-badge"><?= $rdvEnAttente ?></span>
                </a>
                <a href="avis/index.php" class="quick-action">
                    <span class="qa-icon">⭐</span>
                    <span class="qa-label">Avis</span>
                    <span class="qa-badge" style="background:#FFD166;color:#856404;"><?= $avisEnAttente ?> en attente</span>
                </a>
                <a href="reclamations/index.php" class="quick-action">
                    <span class="qa-icon">📩</span>
                    <span class="qa-label">Réclamations</span>
                    <span class="qa-badge" style="background:#FFD166;color:#856404;"><?= $reclamationsEnAttente ?> en attente</span>
                </a>
            </div>
        </div>

        <!-- Notifications -->
        <div class="notifications-card">
            <div class="notif-header">
                <h3>🔔 Notifications</h3>
                <span class="notif-badge"><?= count($notifications) ?></span>
            </div>
            <div class="notif-list">
                <?php foreach($notifications as $n): ?>
                <div class="notif-item">
                    <span class="notif-icon"><?= $n['icon'] ?></span>
                    <div class="notif-content">
                        <span class="notif-message"><?= $n['message'] ?></span>
                        <span class="notif-time"><?= $n['time'] ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Trois colonnes : Agenda / Avis & Réclamations / Prochains RDV -->
    <div class="three-col-grid">
        <!-- Agenda du jour -->
        <div class="mini-card">
            <div class="mini-header">
                <h4>📋 Agenda</h4>
                <span>Aujourd'hui</span>
            </div>
            <div class="mini-body">
                <?php if(count($rdvDuJour) > 0): ?>
                    <?php foreach($rdvDuJour as $rdv): ?>
                    <div class="mini-item">
                        <span class="mini-time"><?= substr($rdv['heure_rdv'], 0, 5) ?></span>
                        <span class="mini-text"><?= htmlspecialchars($rdv['patient_prenom'] . ' ' . $rdv['patient_nom']) ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="mini-empty">📭 Aucun RDV aujourd'hui</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Avis & Réclamations -->
        <div class="mini-card">
            <div class="mini-header">
                <h4>⭐ Avis & Réclamations</h4>
                <a href="avis/index.php" class="mini-link">Voir →</a>
            </div>
            <div class="mini-body">
                <?php if(count($derniersAvis) > 0): ?>
                    <?php foreach($derniersAvis as $a): ?>
                    <div class="mini-item">
                        <span class="mini-icon">⭐</span>
                        <span class="mini-text">
                            <?= htmlspecialchars($a['patient_prenom'] . ' ' . $a['patient_nom']) ?>
                            <span class="mini-badge en_attente">en attente</span>
                        </span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <?php if(count($dernieresReclamations) > 0): ?>
                    <?php foreach($dernieresReclamations as $r): ?>
                    <div class="mini-item">
                        <span class="mini-icon">📩</span>
                        <span class="mini-text">
                            <?= htmlspecialchars($r['patient_prenom'] . ' ' . $r['patient_nom']) ?>
                            <span class="mini-badge en_attente">en attente</span>
                        </span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <?php if(empty($derniersAvis) && empty($dernieresReclamations)): ?>
                <div class="mini-empty">✅ Aucun avis ou réclamation en attente</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Prochains RDV -->
        <div class="mini-card">
            <div class="mini-header">
                <h4>📅 À venir</h4>
                <a href="rdv/index.php" class="mini-link">Voir →</a>
            </div>
            <div class="mini-body">
                <?php foreach($prochainsRdvs as $rdv): ?>
                <div class="mini-item">
                    <span class="mini-date"><?= date('d/m', strtotime($rdv['date_rdv'])) ?></span>
                    <span class="mini-text"><?= htmlspecialchars($rdv['patient_prenom'] . ' ' . $rdv['patient_nom']) ?></span>
                    <span class="mini-badge <?= $rdv['statut'] ?>"><?= $rdv['statut'] ?></span>
                </div>
                <?php endforeach; ?>
                <?php if(empty($prochainsRdvs)): ?>
                <div class="mini-empty">Aucun RDV à venir</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* === STYLES === */

/* Bandeau de bienvenue */
.welcome-banner {
    background: linear-gradient(135deg, var(--pastel-lavande), var(--pastel-bleu));
    border-radius: 20px;
    padding: 24px 32px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 20px;
}
.welcome-banner h1 {
    color: var(--accent);
    font-size: 26px;
    font-weight: 700;
}
.banner-sub {
    color: var(--text-light);
    font-size: 14px;
    margin-top: 2px;
}
.banner-stats {
    display: flex;
    gap: 32px;
}
.banner-stat {
    text-align: center;
}
.banner-number {
    display: block;
    font-size: 24px;
    font-weight: 700;
    color: var(--accent);
}
.banner-label {
    font-size: 12px;
    color: var(--text-light);
}

/* Widgets */
.widgets-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 28px;
}
.widget-card {
    background: white;
    border-radius: 16px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: var(--shadow);
    transition: all 0.3s;
}
.widget-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.08);
}
.widget-icon {
    font-size: 28px;
    flex-shrink: 0;
}
.widget-content {
    flex: 1;
}
.widget-value {
    display: block;
    font-size: 22px;
    font-weight: 700;
    color: var(--accent);
}
.widget-label {
    font-size: 12px;
    color: var(--text-light);
}
.widget-card.weather .widget-value { color: #F5A623; }
.widget-card.satisfaction .widget-value { color: #2D6A4F; }
.widget-card.occupancy .widget-value { color: #7B8FA1; }
.widget-card.trend .widget-value { color: #E07A5F; }

/* Main Grid */
.main-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 28px;
}

/* ===== ACTIONS RAPIDES ===== */
.quick-actions-card {
    background: white;
    border-radius: 16px;
    padding: 20px 24px;
    box-shadow: var(--shadow);
}
.quick-actions-card h3 {
    color: var(--accent);
    font-size: 16px;
    margin-bottom: 16px;
}
.quick-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}
.quick-action {
    background: var(--bg-primary);
    border-radius: 12px;
    padding: 14px 8px;
    text-align: center;
    text-decoration: none;
    color: var(--text);
    transition: all 0.3s;
    position: relative;
}
.quick-action:hover {
    background: var(--pastel-lavande);
    transform: translateY(-2px);
}
.qa-icon {
    display: block;
    font-size: 24px;
    margin-bottom: 4px;
}
.qa-label {
    font-size: 12px;
    font-weight: 500;
}
.qa-badge {
    display: inline-block;
    background: #E07A5F;
    color: white;
    font-size: 10px;
    font-weight: 600;
    padding: 1px 10px;
    border-radius: 20px;
    margin-top: 4px;
}
/* ===== FIN ACTIONS RAPIDES ===== */

/* Notifications */
.notifications-card {
    background: white;
    border-radius: 16px;
    padding: 20px 24px;
    box-shadow: var(--shadow);
}
.notif-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}
.notif-header h3 {
    color: var(--accent);
    font-size: 16px;
}
.notif-badge {
    background: var(--pastel-rose);
    padding: 2px 12px;
    border-radius: 40px;
    font-size: 12px;
    font-weight: 600;
    color: #8A4A4A;
}
.notif-list {
    max-height: 200px;
    overflow-y: auto;
}
.notif-item {
    display: flex;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid var(--border);
}
.notif-item:last-child {
    border-bottom: none;
}
.notif-icon {
    font-size: 18px;
    flex-shrink: 0;
}
.notif-content {
    flex: 1;
}
.notif-message {
    display: block;
    font-size: 13px;
}
.notif-time {
    font-size: 11px;
    color: var(--text-light);
}

/* Three Columns */
.three-col-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 20px;
}
.mini-card {
    background: white;
    border-radius: 16px;
    padding: 16px 20px;
    box-shadow: var(--shadow);
}
.mini-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 2px solid var(--border);
}
.mini-header h4 {
    color: var(--accent);
    font-size: 14px;
    font-weight: 600;
}
.mini-header span {
    font-size: 12px;
    color: var(--text-light);
}
.mini-link {
    font-size: 12px;
    color: var(--accent);
    text-decoration: none;
}
.mini-link:hover {
    text-decoration: underline;
}
.mini-body {
    max-height: 180px;
    overflow-y: auto;
}
.mini-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid var(--border);
}
.mini-item:last-child {
    border-bottom: none;
}
.mini-time {
    font-weight: 600;
    color: var(--accent);
    font-size: 13px;
    min-width: 45px;
}
.mini-text {
    font-size: 13px;
    flex: 1;
}
.mini-icon {
    font-size: 16px;
}
.mini-date {
    font-size: 11px;
    color: var(--text-light);
}
.mini-badge {
    padding: 2px 10px;
    border-radius: 40px;
    font-size: 10px;
    font-weight: 500;
}
.mini-badge.confirme { background: var(--pastel-vert); color: #2D6A4F; }
.mini-badge.en_attente { background: #FFF2CC; color: #856404; }
.mini-badge.termine { background: var(--pastel-bleu); color: #004085; }
.mini-badge.annule { background: var(--pastel-peche); color: #E07A5F; }
.mini-badge.publie { background: var(--pastel-vert); color: #2D6A4F; }
.mini-badge.traite { background: var(--pastel-vert); color: #2D6A4F; }
.mini-badge.rejete { background: var(--pastel-peche); color: #E07A5F; }
.mini-empty {
    text-align: center;
    padding: 20px 0;
    color: var(--text-light);
    font-size: 13px;
}

/* Responsive */
@media (max-width: 1200px) {
    .widgets-row {
        grid-template-columns: repeat(2, 1fr);
    }
    .three-col-grid {
        grid-template-columns: 1fr 1fr;
    }
    .main-grid {
        grid-template-columns: 1fr;
    }
}
@media (max-width: 768px) {
    .widgets-row {
        grid-template-columns: 1fr;
    }
    .three-col-grid {
        grid-template-columns: 1fr;
    }
    .banner-stats {
        gap: 16px;
    }
    .welcome-banner {
        flex-direction: column;
        text-align: center;
    }
    .quick-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<?php include '../includes/footer.php'; ?>