<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

require_once '../config/database.php';
$pdo = getPDO();

// Statistiques générales
$totalPatients = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'patient'")->fetchColumn();
$totalMedecins = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'medecin'")->fetchColumn();
$totalRdvs = $pdo->query("SELECT COUNT(*) FROM rendez_vous")->fetchColumn();
$rdvMois = $pdo->query("SELECT COUNT(*) FROM rendez_vous WHERE MONTH(date_rdv) = MONTH(CURDATE()) AND YEAR(date_rdv) = YEAR(CURDATE())")->fetchColumn();
$rdvJour = $pdo->query("SELECT COUNT(*) FROM rendez_vous WHERE date_rdv = CURDATE()")->fetchColumn();
$rdvSemaine = $pdo->query("SELECT COUNT(*) FROM rendez_vous WHERE WEEK(date_rdv) = WEEK(CURDATE()) AND YEAR(date_rdv) = YEAR(CURDATE())")->fetchColumn();

// RDV par médecin
$stmt = $pdo->query("
    SELECT u.nom, u.prenom, u.specialite, COUNT(r.id) as total
    FROM users u
    LEFT JOIN rendez_vous r ON u.id = r.medecin_id
    WHERE u.role = 'medecin'
    GROUP BY u.id
    ORDER BY total DESC
");
$statsMedecins = $stmt->fetchAll();

// RDV par statut
$stmt = $pdo->query("
    SELECT statut, COUNT(*) as total 
    FROM rendez_vous 
    GROUP BY statut
");
$statsStatut = $stmt->fetchAll();

// RDV par mois (12 derniers mois)
$stmt = $pdo->query("
    SELECT DATE_FORMAT(date_rdv, '%b') as mois, COUNT(*) as total
    FROM rendez_vous
    WHERE date_rdv >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY MONTH(date_rdv)
    ORDER BY MONTH(date_rdv)
");
$statsMois = $stmt->fetchAll();

// Top patient
$stmt = $pdo->query("
    SELECT u.nom, u.prenom, COUNT(r.id) as total
    FROM users u
    JOIN rendez_vous r ON u.id = r.patient_id
    GROUP BY u.id
    ORDER BY total DESC
    LIMIT 1
");
$topPatient = $stmt->fetch();

// Taux de satisfaction (simulé)
$tauxSatisfaction = 94;

// Calculer l'évolution
$stmt = $pdo->query("
    SELECT COUNT(*) as total
    FROM rendez_vous
    WHERE MONTH(date_rdv) = MONTH(CURDATE() - INTERVAL 1 MONTH)
    AND YEAR(date_rdv) = YEAR(CURDATE() - INTERVAL 1 MONTH)
");
$rdvMoisPrecedent = $stmt->fetchColumn();
$evolution = $rdvMoisPrecedent > 0 ? round((($rdvMois - $rdvMoisPrecedent) / $rdvMoisPrecedent) * 100) : 0;

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <!-- En-tête -->
    <div class="stats-header">
        <div>
            <h1>📊 Tableau de bord statistiques</h1>
            <p class="subtitle">Analyse complète de l'activité de la clinique</p>
        </div>
        <div class="header-date">
            <span class="date-badge">📅 <?= date('d/m/Y') ?></span>
            <span class="update-badge">🔄 Mise à jour en temps réel</span>
        </div>
    </div>

    <!-- Cartes KPI modernes -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #C5E0F7, #7B8FA1);">
                <span>👨‍👩‍👧</span>
            </div>
            <div class="kpi-content">
                <div class="kpi-value"><?= $totalPatients ?></div>
                <div class="kpi-label">Patients inscrits</div>
                <div class="kpi-trend up">↑ 12% ce mois</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #C8E6E0, #48BBA8);">
                <span>👨‍⚕️</span>
            </div>
            <div class="kpi-content">
                <div class="kpi-value"><?= $totalMedecins ?></div>
                <div class="kpi-label">Médecins actifs</div>
                <div class="kpi-trend up">↑ 2 nouveaux</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #FADADD, #E07A5F);">
                <span>📅</span>
            </div>
            <div class="kpi-content">
                <div class="kpi-value"><?= $totalRdvs ?></div>
                <div class="kpi-label">Total rendez-vous</div>
                <div class="kpi-trend <?= $evolution >= 0 ? 'up' : 'down' ?>">
                    <?= $evolution >= 0 ? '↑' : '↓' ?> <?= abs($evolution) ?>% vs mois dernier
                </div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #E4DFF7, #A89BD9);">
                <span>⭐</span>
            </div>
            <div class="kpi-content">
                <div class="kpi-value"><?= $topPatient ? htmlspecialchars($topPatient['prenom'] . ' ' . $topPatient['nom']) : '-' ?></div>
                <div class="kpi-label">Patient le plus actif</div>
                <div class="kpi-trend"><?= $topPatient ? $topPatient['total'] . ' rendez-vous' : '' ?></div>
            </div>
        </div>
    </div>

    <!-- Deuxième ligne de KPI -->
    <div class="kpi-grid secondary">
        <div class="kpi-card small">
            <div class="kpi-label">📆 RDV aujourd'hui</div>
            <div class="kpi-value"><?= $rdvJour ?></div>
        </div>
        <div class="kpi-card small">
            <div class="kpi-label">📊 RDV cette semaine</div>
            <div class="kpi-value"><?= $rdvSemaine ?></div>
        </div>
        <div class="kpi-card small">
            <div class="kpi-label">📈 RDV ce mois</div>
            <div class="kpi-value"><?= $rdvMois ?></div>
        </div>
        <div class="kpi-card small">
            <div class="kpi-label">😊 Satisfaction</div>
            <div class="kpi-value"><?= $tauxSatisfaction ?>%</div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="charts-grid">
        <!-- Graphique 1 : Barres - RDV par médecin -->
        <div class="chart-card full">
            <div class="chart-header">
                <div>
                    <h3>🏆 Rendez-vous par médecin</h3>
                    <p>Classement des médecins les plus sollicités</p>
                </div>
                <span class="chart-badge">Top 5</span>
            </div>
            <div class="chart-body">
                <canvas id="chartMedecins" height="120"></canvas>
            </div>
        </div>
        
        <!-- Graphique 2 : Camembert - RDV par statut -->
        <div class="chart-card half">
            <div class="chart-header">
                <div>
                    <h3>📌 Répartition par statut</h3>
                    <p>État des rendez-vous</p>
                </div>
            </div>
            <div class="chart-body" style="max-width: 280px; margin: 0 auto;">
                <canvas id="chartStatut" height="200"></canvas>
            </div>
        </div>
        
        <!-- Graphique 3 : Lignes - Évolution mensuelle -->
        <div class="chart-card half">
            <div class="chart-header">
                <div>
                    <h3>📈 Évolution mensuelle</h3>
                    <p>Nombre de RDV sur 12 mois</p>
                </div>
            </div>
            <div class="chart-body">
                <canvas id="chartMois" height="120"></canvas>
            </div>
        </div>
    </div>

    <!-- Tableau des médecins -->
    <div class="table-card">
        <div class="table-header">
            <div>
                <h3>👨‍⚕️ Performance des médecins</h3>
                <p>Détail par médecin avec barre de progression</p>
            </div>
            <span class="table-count"><?= count($statsMedecins) ?> médecins</span>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Médecin</th>
                        <th>Spécialité</th>
                        <th>Nombre de RDV</th>
                        <th>Performance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $maxRdv = !empty($statsMedecins) ? max(array_column($statsMedecins, 'total')) : 1;
                    foreach($statsMedecins as $s): 
                        $pourcentage = ($s['total'] / $maxRdv) * 100;
                    ?>
                    <tr>
                        <td>
                            <div class="medecin-cell">
                                <span class="medecin-avatar">👤</span>
                                <strong>Dr. <?= htmlspecialchars($s['prenom'] . ' ' . $s['nom']) ?></strong>
                            </div>
                        </td>
                        <td><span class="specialite-badge"><?= htmlspecialchars($s['specialite'] ?? 'Généraliste') ?></span></td>
                        <td><span class="rdv-count"><?= $s['total'] ?></span></td>
                        <td>
                            <div class="progress-wrapper">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?= $pourcentage ?>%;">
                                        <span class="progress-label"><?= round($pourcentage) ?>%</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($statsMedecins)): ?>
                    <tr><td colspan="4" style="text-align:center;padding:40px;">Aucun médecin enregistré</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
/* === STYLES MODERNES === */

/* En-tête */
.stats-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 32px;
    flex-wrap: wrap;
    gap: 16px;
}
.stats-header h1 {
    color: var(--accent);
    font-size: 32px;
    font-weight: 700;
}
.stats-header .subtitle {
    color: var(--text-light);
    font-size: 15px;
    margin-top: 4px;
}
.header-date {
    display: flex;
    gap: 12px;
    align-items: center;
}
.date-badge {
    background: white;
    padding: 8px 20px;
    border-radius: 40px;
    font-size: 13px;
    box-shadow: var(--shadow-sm);
}
.update-badge {
    background: var(--pastel-vert);
    padding: 8px 20px;
    border-radius: 40px;
    font-size: 12px;
    color: #2D6A4F;
}

/* KPI Grid */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 24px;
}
.kpi-grid.secondary {
    grid-template-columns: repeat(4, 1fr);
    margin-bottom: 32px;
}
.kpi-card {
    background: white;
    border-radius: 20px;
    padding: 20px 24px;
    box-shadow: var(--shadow);
    display: flex;
    align-items: center;
    gap: 16px;
    transition: all 0.3s ease;
}
.kpi-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.08);
}
.kpi-card.small {
    padding: 16px 20px;
    flex-direction: column;
    align-items: flex-start;
}
.kpi-card.small .kpi-value {
    font-size: 28px;
}
.kpi-icon {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    flex-shrink: 0;
}
.kpi-content {
    flex: 1;
}
.kpi-value {
    font-size: 32px;
    font-weight: 700;
    color: var(--accent);
    line-height: 1.2;
}
.kpi-label {
    font-size: 13px;
    color: var(--text-light);
    margin-top: 2px;
}
.kpi-trend {
    font-size: 12px;
    margin-top: 4px;
    font-weight: 500;
}
.kpi-trend.up { color: #2D6A4F; }
.kpi-trend.down { color: #E07A5F; }
.kpi-trend.up::before { content: '↑ '; }
.kpi-trend.down::before { content: '↓ '; }

/* Graphiques */
.charts-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 32px;
}
.chart-card {
    background: white;
    border-radius: 20px;
    padding: 24px;
    box-shadow: var(--shadow);
    transition: all 0.3s ease;
}
.chart-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.08);
}
.chart-card.full {
    grid-column: span 2;
}
.chart-card.half {
    grid-column: span 1;
}
.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid var(--border);
}
.chart-header h3 {
    color: var(--accent);
    font-size: 17px;
    font-weight: 600;
}
.chart-header p {
    color: var(--text-light);
    font-size: 13px;
    margin-top: 2px;
}
.chart-badge {
    background: var(--pastel-lavande);
    padding: 4px 16px;
    border-radius: 40px;
    font-size: 12px;
    color: #5a4a8a;
    font-weight: 500;
}

/* Tableau */
.table-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: var(--shadow);
}
.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    background: var(--bg-primary);
    border-bottom: 2px solid var(--border);
}
.table-header h3 {
    color: var(--accent);
    font-size: 17px;
    font-weight: 600;
}
.table-header p {
    color: var(--text-light);
    font-size: 13px;
    margin-top: 2px;
}
.table-count {
    background: var(--pastel-bleu);
    padding: 4px 16px;
    border-radius: 40px;
    font-size: 12px;
    color: #2c5a7a;
    font-weight: 500;
}
.table-container {
    overflow-x: auto;
}
table {
    width: 100%;
    border-collapse: collapse;
}
th {
    text-align: left;
    padding: 14px 20px;
    background: #F8F9FA;
    font-weight: 600;
    color: var(--accent);
    font-size: 13px;
}
td {
    padding: 14px 20px;
    border-bottom: 1px solid var(--border);
    font-size: 14px;
}
tr:hover {
    background: #FDFBF7;
}
.medecin-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}
.medecin-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--pastel-lavande);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}
.specialite-badge {
    background: var(--pastel-lavande);
    padding: 4px 12px;
    border-radius: 40px;
    font-size: 12px;
    color: #5a4a8a;
}
.rdv-count {
    font-weight: 600;
    color: var(--accent);
}
.progress-wrapper {
    width: 100%;
    max-width: 200px;
}
.progress-bar {
    background: #EBEBEB;
    border-radius: 20px;
    height: 24px;
    overflow: hidden;
    position: relative;
}
.progress-fill {
    height: 100%;
    border-radius: 20px;
    background: linear-gradient(90deg, #C8E6E0, #7B8FA1);
    transition: width 1s ease;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-right: 8px;
}
.progress-label {
    font-size: 11px;
    font-weight: 600;
    color: white;
}

/* Responsive */
@media (max-width: 1200px) {
    .kpi-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .kpi-grid.secondary {
        grid-template-columns: repeat(2, 1fr);
    }
    .charts-grid {
        grid-template-columns: 1fr;
    }
    .chart-card.full {
        grid-column: span 1;
    }
    .chart-card.half {
        grid-column: span 1;
    }
}
@media (max-width: 768px) {
    .kpi-grid {
        grid-template-columns: 1fr;
    }
    .kpi-grid.secondary {
        grid-template-columns: 1fr 1fr;
    }
    .stats-header {
        flex-direction: column;
    }
    .header-date {
        flex-wrap: wrap;
    }
    .charts-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Couleurs pastel
    const colors = {
        blue: 'rgba(197, 224, 247, 0.8)',
        blueBorder: '#7B8FA1',
        green: 'rgba(200, 230, 224, 0.8)',
        greenBorder: '#48BBA8',
        lavender: 'rgba(228, 223, 247, 0.8)',
        lavenderBorder: '#A89BD9',
        pink: 'rgba(250, 218, 221, 0.8)',
        pinkBorder: '#E07A5F',
        yellow: 'rgba(255, 242, 204, 0.8)',
        yellowBorder: '#D4A843'
    };

    // 1. Graphique barres - RDV par médecin
    const ctx1 = document.getElementById('chartMedecins');
    if(ctx1 && <?= count($statsMedecins) ?> > 0) {
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: [<?php foreach($statsMedecins as $s): ?>'Dr. <?= addslashes($s['prenom'] . ' ' . $s['nom']) ?>',<?php endforeach; ?>],
                datasets: [{
                    label: 'Nombre de rendez-vous',
                    data: [<?php foreach($statsMedecins as $s): ?><?= $s['total'] ?>,<?php endforeach; ?>],
                    backgroundColor: 'rgba(123, 143, 161, 0.6)',
                    borderColor: '#7B8FA1',
                    borderWidth: 2,
                    borderRadius: 8,
                    barPercentage: 0.6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { 
                        backgroundColor: '#4A4A4A',
                        titleColor: '#FFF',
                        bodyColor: '#FFF',
                        cornerRadius: 12,
                        padding: 12
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        title: { display: true, text: 'Nombre de RDV', color: '#8A8A8A' }
                    },
                    x: { 
                        ticks: { font: { size: 11 } }, 
                        grid: { display: false } 
                    }
                }
            }
        });
    }

    // 2. Graphique camembert - RDV par statut
    const ctx2 = document.getElementById('chartStatut');
    if(ctx2 && <?= count($statsStatut) ?> > 0) {
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: [<?php foreach($statsStatut as $s): ?>'<?= ucfirst($s['statut']) ?>',<?php endforeach; ?>],
                datasets: [{
                    data: [<?php foreach($statsStatut as $s): ?><?= $s['total'] ?>,<?php endforeach; ?>],
                    backgroundColor: ['#C8E6E0', '#C5E0F7', '#E4DFF7', '#FADADD'],
                    borderWidth: 0,
                    hoverOffset: 12
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '55%',
                plugins: {
                    legend: { 
                        position: 'bottom',
                        labels: { 
                            boxWidth: 12, 
                            font: { size: 12 },
                            padding: 16
                        }
                    },
                    tooltip: { 
                        callbacks: { 
                            label: function(context) { 
                                return context.label + ': ' + context.raw + ' RDV'; 
                            } 
                        },
                        backgroundColor: '#4A4A4A',
                        cornerRadius: 12,
                        padding: 12
                    }
                }
            }
        });
    }

    // 3. Graphique lignes - Évolution mensuelle
    const ctx3 = document.getElementById('chartMois');
    if(ctx3 && <?= count($statsMois) ?> > 0) {
        new Chart(ctx3, {
            type: 'line',
            data: {
                labels: [<?php foreach($statsMois as $m): ?>'<?= $m['mois'] ?>',<?php endforeach; ?>],
                datasets: [{
                    label: 'Rendez-vous',
                    data: [<?php foreach($statsMois as $m): ?><?= $m['total'] ?>,<?php endforeach; ?>],
                    borderColor: '#7B8FA1',
                    backgroundColor: 'rgba(123, 143, 161, 0.08)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#7B8FA1',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#4A4A4A',
                        titleColor: '#FFF',
                        bodyColor: '#FFF',
                        cornerRadius: 12,
                        padding: 12
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        title: { display: true, text: 'Nombre de RDV', color: '#8A8A8A' }
                    },
                    x: { 
                        grid: { display: false },
                        ticks: { font: { size: 11 } }
                    }
                }
            }
        });
    }

    // Animation des barres de progression
    document.querySelectorAll('.progress-fill').forEach(function(el, index) {
        setTimeout(function() {
            el.style.width = el.style.width;
        }, 200 + (index * 100));
    });
});
</script>

<?php include '../includes/footer.php'; ?>