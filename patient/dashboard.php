<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header('Location: ../login.php');
    exit();
}

require_once '../config/database.php';
$pdo = getPDO();
$patient_id = $_SESSION['user_id'];

// Cartes KPI
$stmt = $pdo->prepare("SELECT COUNT(*) FROM rendez_vous WHERE patient_id = ? AND date_rdv = CURDATE()");
$stmt->execute([$patient_id]);
$rdvAujourdhui = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM rendez_vous WHERE patient_id = ? AND date_rdv > CURDATE()");
$stmt->execute([$patient_id]);
$rdvAVenir = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM rendez_vous WHERE patient_id = ?");
$stmt->execute([$patient_id]);
$totalRdvs = $stmt->fetchColumn();

// Statistiques avis et réclamations
$stmt = $pdo->prepare("SELECT COUNT(*) FROM avis WHERE patient_id = ?");
$stmt->execute([$patient_id]);
$totalAvis = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM reclamations WHERE patient_id = ?");
$stmt->execute([$patient_id]);
$totalReclamations = $stmt->fetchColumn();

// Prochains rendez-vous
$stmt = $pdo->prepare("
    SELECT r.*, u.nom as medecin_nom, u.prenom as medecin_prenom, u.specialite
    FROM rendez_vous r
    JOIN users u ON r.medecin_id = u.id
    WHERE r.patient_id = ? AND r.date_rdv >= CURDATE() AND r.statut != 'annule'
    ORDER BY r.date_rdv ASC, r.heure_rdv ASC
");
$stmt->execute([$patient_id]);
$prochainsRdvs = $stmt->fetchAll();

// Dernier avis du patient
$stmt = $pdo->prepare("
    SELECT * FROM avis WHERE patient_id = ? ORDER BY date_creation DESC LIMIT 1
");
$stmt->execute([$patient_id]);
$dernierAvis = $stmt->fetch();

// Dernière réclamation
$stmt = $pdo->prepare("
    SELECT * FROM reclamations WHERE patient_id = ? ORDER BY date_creation DESC LIMIT 1
");
$stmt->execute([$patient_id]);
$derniereReclamation = $stmt->fetch();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <!-- Bandeau de bienvenue -->
    <div class="welcome-banner">
        <div>
            <h1>👋 Bonjour, <?= htmlspecialchars($_SESSION['prenom']) ?> <?= htmlspecialchars($_SESSION['nom']) ?></h1>
            <p class="banner-sub">Bienvenue sur votre espace patient</p>
        </div>
        <div class="banner-stats">
            <div class="banner-stat">
                <span class="banner-number"><?= $rdvAujourdhui ?></span>
                <span class="banner-label">Aujourd'hui</span>
            </div>
            <div class="banner-stat">
                <span class="banner-number"><?= $rdvAVenir ?></span>
                <span class="banner-label">À venir</span>
            </div>
            <div class="banner-stat">
                <span class="banner-number"><?= $totalRdvs ?></span>
                <span class="banner-label">Total RDV</span>
            </div>
        </div>
    </div>

    <!-- Widgets - AGGRANDIS -->
    <div class="widgets-row">
        <div class="widget-card">
            <span class="widget-icon">📅</span>
            <div>
                <span class="widget-value"><?= $rdvAujourdhui ?></span>
                <span class="widget-label">RDV aujourd'hui</span>
            </div>
        </div>
        <div class="widget-card">
            <span class="widget-icon">📆</span>
            <div>
                <span class="widget-value"><?= $rdvAVenir ?></span>
                <span class="widget-label">RDV à venir</span>
            </div>
        </div>
        <div class="widget-card">
            <span class="widget-icon">⭐</span>
            <div>
                <span class="widget-value"><?= $totalAvis ?></span>
                <span class="widget-label">Avis donnés</span>
            </div>
        </div>
        <div class="widget-card">
            <span class="widget-icon">📩</span>
            <div>
                <span class="widget-value"><?= $totalReclamations ?></span>
                <span class="widget-label">Réclamations</span>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="quick-actions-card">
        <h3>⚡ Actions rapides</h3>
        <div class="quick-grid">
            <a href="rdv/ajouter.php" class="quick-action">
                <span class="qa-icon">📅</span>
                <span class="qa-label">Prendre RDV</span>
            </a>
            <a href="avis/ajouter.php" class="quick-action">
                <span class="qa-icon">⭐</span>
                <span class="qa-label">Donner un avis</span>
            </a>
            <a href="reclamations/ajouter.php" class="quick-action">
                <span class="qa-icon">📩</span>
                <span class="qa-label">Réclamation</span>
            </a>
        </div>
    </div>

    <!-- Deux colonnes -->
    <div class="two-col-grid">
        <div class="mini-card">
            <div class="mini-header">
                <h4>📅 Prochains rendez-vous</h4>
                <a href="rdv/index.php" class="mini-link">Voir →</a>
            </div>
            <div class="mini-body">
                <?php if(count($prochainsRdvs) > 0): ?>
                    <?php foreach($prochainsRdvs as $rdv): ?>
                    <div class="mini-item">
                        <span class="mini-date"><?= date('d/m', strtotime($rdv['date_rdv'])) ?></span>
                        <span class="mini-time"><?= substr($rdv['heure_rdv'], 0, 5) ?></span>
                        <span class="mini-text">Dr. <?= htmlspecialchars($rdv['medecin_nom']) ?></span>
                        <span class="mini-badge <?= $rdv['statut'] ?>"><?= $rdv['statut'] ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="mini-empty">📭 Aucun rendez-vous à venir</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mini-card">
            <div class="mini-header">
                <h4>📋 Mes retours</h4>
                <span>Dernières activités</span>
            </div>
            <div class="mini-body">
                <?php if($dernierAvis): ?>
                <div class="mini-item">
                    <span class="mini-icon">⭐</span>
                    <span class="mini-text">
                        Avis du <?= date('d/m/Y', strtotime($dernierAvis['date_creation'])) ?>
                        <span class="mini-badge <?= $dernierAvis['statut'] ?>"><?= $dernierAvis['statut'] ?></span>
                    </span>
                </div>
                <?php endif; ?>
                
                <?php if($derniereReclamation): ?>
                <div class="mini-item">
                    <span class="mini-icon">📩</span>
                    <span class="mini-text">
                        Réclamation du <?= date('d/m/Y', strtotime($derniereReclamation['date_creation'])) ?>
                        <span class="mini-badge <?= $derniereReclamation['statut'] ?>"><?= $derniereReclamation['statut'] ?></span>
                    </span>
                </div>
                <?php endif; ?>
                
                <?php if(!$dernierAvis && !$derniereReclamation): ?>
                <div class="mini-empty">📭 Aucun avis ou réclamation</div>
                <?php endif; ?>
                
                <div style="display:flex; gap:8px; margin-top:12px; padding-top:8px; border-top:1px solid var(--border);">
                    <a href="avis/index.php" style="flex:1; text-align:center; background:var(--bg-primary); padding:8px; border-radius:8px; text-decoration:none; font-size:12px; color:var(--accent);">⭐ Mes avis</a>
                    <a href="reclamations/index.php" style="flex:1; text-align:center; background:var(--bg-primary); padding:8px; border-radius:8px; text-decoration:none; font-size:12px; color:var(--accent);">📩 Mes réclamations</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Liens rapides -->
    <div class="quick-links">
        <a href="avis/ajouter.php" class="quick-link avis">
            <span>⭐</span>
            <div>
                <div class="quick-link-title">Donner un avis</div>
                <div class="quick-link-desc">Partagez votre expérience</div>
            </div>
            <span class="quick-link-arrow">→</span>
        </a>
        <a href="reclamations/ajouter.php" class="quick-link reclamation">
            <span>📩</span>
            <div>
                <div class="quick-link-title">Faire une réclamation</div>
                <div class="quick-link-desc">Signaler un problème</div>
            </div>
            <span class="quick-link-arrow">→</span>
        </a>
    </div>
</div>

<style>
/* === STYLES MODERNES === */

/* Bandeau de bienvenue */
.welcome-banner {
    background: linear-gradient(135deg, var(--pastel-lavande), var(--pastel-bleu));
    border-radius: 16px;
    padding: 20px 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
}
.welcome-banner h1 {
    color: var(--accent);
    font-size: 24px;
    font-weight: 700;
}
.banner-sub {
    color: var(--text-light);
    font-size: 13px;
    margin-top: 2px;
}
.banner-stats {
    display: flex;
    gap: 24px;
}
.banner-stat {
    text-align: center;
}
.banner-number {
    display: block;
    font-size: 20px;
    font-weight: 700;
    color: var(--accent);
}
.banner-label {
    font-size: 11px;
    color: var(--text-light);
}

/* Widgets - AGGRANDIS comme admin */
.widgets-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 28px;
}
.widget-card {
    background: white;
    border-radius: 16px;
    padding: 20px 24px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: var(--shadow);
    transition: all 0.3s;
    min-height: 80px;
}
.widget-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.08);
}
.widget-icon {
    font-size: 32px;
    flex-shrink: 0;
}
.widget-value {
    display: block;
    font-size: 28px;
    font-weight: 700;
    color: var(--accent);
    line-height: 1.2;
}
.widget-label {
    font-size: 13px;
    color: var(--text-light);
}

/* Actions rapides */
.quick-actions-card {
    background: white;
    border-radius: 12px;
    padding: 16px 20px;
    box-shadow: var(--shadow);
    margin-bottom: 24px;
}
.quick-actions-card h3 {
    color: var(--accent);
    font-size: 14px;
    margin-bottom: 12px;
}
.quick-grid {
    display: flex;
    gap: 12px;
}
.quick-action {
    flex: 1;
    background: var(--bg-primary);
    border-radius: 10px;
    padding: 12px 8px;
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
    font-size: 22px;
    margin-bottom: 4px;
}
.qa-label {
    font-size: 12px;
    font-weight: 500;
}

/* Two columns */
.two-col-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
    margin-bottom: 24px;
}
.mini-card {
    background: white;
    border-radius: 12px;
    padding: 14px 18px;
    box-shadow: var(--shadow);
}
.mini-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    padding-bottom: 8px;
    border-bottom: 1px solid var(--border);
}
.mini-header h4 {
    color: var(--accent);
    font-size: 13px;
    font-weight: 600;
}
.mini-header span {
    font-size: 11px;
    color: var(--text-light);
}
.mini-link {
    font-size: 11px;
    color: var(--accent);
    text-decoration: none;
}
.mini-link:hover {
    text-decoration: underline;
}
.mini-body {
    max-height: 140px;
    overflow-y: auto;
}
.mini-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 5px 0;
    border-bottom: 1px solid var(--border);
}
.mini-item:last-child {
    border-bottom: none;
}
.mini-time {
    font-weight: 600;
    color: var(--accent);
    font-size: 12px;
    min-width: 35px;
}
.mini-date {
    font-size: 11px;
    color: var(--text-light);
    min-width: 40px;
}
.mini-text {
    font-size: 12px;
    flex: 1;
}
.mini-icon {
    font-size: 16px;
}
.mini-badge {
    padding: 1px 8px;
    border-radius: 40px;
    font-size: 9px;
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
    padding: 12px 0;
    color: var(--text-light);
    font-size: 12px;
}

/* Quick Links */
.quick-links {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-top: 8px;
}
.quick-link {
    background: white;
    border-radius: 16px;
    padding: 20px 24px;
    box-shadow: var(--shadow);
    display: flex;
    align-items: center;
    gap: 16px;
    text-decoration: none;
    color: var(--text);
    transition: all 0.3s;
    border: 2px solid transparent;
    position: relative;
    cursor: pointer;
}
.quick-link:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.08);
}
.quick-link span:first-child {
    font-size: 32px;
    flex-shrink: 0;
}
.quick-link-title {
    font-weight: 600;
    color: var(--accent);
    font-size: 16px;
}
.quick-link-desc {
    font-size: 13px;
    color: var(--text-light);
    margin-top: 2px;
}
.quick-link-arrow {
    margin-left: auto;
    font-size: 20px;
    color: var(--accent);
    opacity: 0.5;
    transition: all 0.3s;
}
.quick-link:hover .quick-link-arrow {
    opacity: 1;
    transform: translateX(4px);
}
.quick-link.avis {
    background: linear-gradient(135deg, #FFF8E7, #FFEED6);
    border-color: #FFD166;
}
.quick-link.avis:hover {
    border-color: #F5A623;
    box-shadow: 0 12px 40px rgba(245, 166, 35, 0.15);
}
.quick-link.reclamation {
    background: linear-gradient(135deg, #FDF0EE, #FADADD);
    border-color: #E07A5F;
}
.quick-link.reclamation:hover {
    border-color: #c05a3f;
    box-shadow: 0 12px 40px rgba(224, 122, 95, 0.15);
}

/* Responsive */
@media (max-width: 992px) {
    .widgets-row {
        grid-template-columns: repeat(2, 1fr);
    }
    .two-col-grid {
        grid-template-columns: 1fr;
    }
    .quick-links {
        grid-template-columns: 1fr;
    }
}
@media (max-width: 576px) {
    .widgets-row {
        grid-template-columns: 1fr;
    }
    .quick-grid {
        flex-direction: column;
    }
    .welcome-banner {
        flex-direction: column;
        text-align: center;
    }
    .banner-stats {
        gap: 12px;
    }
    .quick-link {
        padding: 16px;
    }
}
</style>

<?php include '../includes/footer.php'; ?>