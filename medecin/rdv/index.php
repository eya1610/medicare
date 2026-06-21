<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'medecin') {
    header('Location: ../../login.php');
    exit();
}

// Afficher les messages de session
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['message']);
unset($_SESSION['error']);

require_once '../../config/database.php';
$pdo = getPDO();
$medecin_id = $_SESSION['user_id'];

// Récupérer tous les rendez-vous du médecin avec infos patient
$stmt = $pdo->prepare("
    SELECT r.*, 
           u.nom as patient_nom, u.prenom as patient_prenom, 
           u.telephone, u.email, u.date_naissance, u.adresse,
           DATE_FORMAT(r.date_rdv, '%Y-%m-%d') as date_key
    FROM rendez_vous r
    JOIN users u ON r.patient_id = u.id
    WHERE r.medecin_id = ? AND r.statut != 'annule'
    ORDER BY r.date_rdv ASC, r.heure_rdv ASC
");
$stmt->execute([$medecin_id]);
$rdvs = $stmt->fetchAll();

// Organiser les RDV par date
$rdvParDate = [];
$patients = [];
foreach($rdvs as $rdv) {
    $date = $rdv['date_key'];
    if(!isset($rdvParDate[$date])) {
        $rdvParDate[$date] = [];
    }
    $rdvParDate[$date][] = $rdv;
    
    // Stocker les infos patients
    if(!isset($patients[$rdv['patient_id']])) {
        $patients[$rdv['patient_id']] = [
            'id' => $rdv['patient_id'],
            'nom' => $rdv['patient_nom'],
            'prenom' => $rdv['patient_prenom'],
            'email' => $rdv['email'],
            'telephone' => $rdv['telephone'],
            'date_naissance' => $rdv['date_naissance'],
            'adresse' => $rdv['adresse']
        ];
    }
}

// Mois et année
$mois = isset($_GET['mois']) ? (int)$_GET['mois'] : date('n');
$annee = isset($_GET['annee']) ? (int)$_GET['annee'] : date('Y');

if($mois < 1) { $mois = 12; $annee--; }
if($mois > 12) { $mois = 1; $annee++; }

$dateActuelle = strtotime("$annee-$mois-01");
$premierJourMois = date('N', $dateActuelle);
$nbJoursMois = date('t', $dateActuelle);
$aujourdhui = date('Y-m-d');

// Récupérer les paramètres pour l'ouverture automatique
$rdv_ajoute = isset($_GET['rdv_ajoute']) ? (int)$_GET['rdv_ajoute'] : 0;
$focus_patient = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;
$focus_date = isset($_GET['date']) ? $_GET['date'] : '';

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <!-- Messages -->
    <?php if($message): ?>
        <div class="alert alert-success" style="background:var(--pastel-vert);padding:12px 20px;border-radius:16px;margin-bottom:20px;border-left:4px solid #2D6A4F;">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert alert-error" style="background:var(--pastel-peche);padding:12px 20px;border-radius:16px;margin-bottom:20px;border-left:4px solid #E07A5F;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- En-tête -->
    <div class="calendar-header">
        <div>
            <h1>📅 Mes rendez-vous</h1>
            <p>Visualisation de votre agenda médical</p>
        </div>
        <div class="calendar-stats">
            <span class="stat-badge">📅 Total : <?= count($rdvs) ?> RDV</span>
        </div>
    </div>

    <!-- Navigation mois -->
    <div class="calendar-nav">
        <div class="nav-buttons">
            <a href="?mois=<?= $mois-1 ?>&annee=<?= $annee ?>" class="nav-btn">←</a>
            <h2><?= date('F Y', $dateActuelle) ?></h2>
            <a href="?mois=<?= $mois+1 ?>&annee=<?= $annee ?>" class="nav-btn">→</a>
        </div>
        <a href="?mois=<?= date('n') ?>&annee=<?= date('Y') ?>" class="today-btn">📆 Aujourd'hui</a>
    </div>

    <!-- Légende -->
    <div class="legend">
        <span class="legend-item"><span class="legend-dot past"></span> Passé</span>
        <span class="legend-item"><span class="legend-dot today"></span> Aujourd'hui</span>
        <span class="legend-item"><span class="legend-dot future"></span> Futur</span>
        <span class="legend-item"><span class="legend-dot has-rdv"></span> Avec RDV</span>
    </div>

    <!-- Grille calendrier -->
    <div class="calendar-grid-wrapper">
        <div class="weekdays">
            <div>Lun</div><div>Mar</div><div>Mer</div><div>Jeu</div><div>Ven</div><div>Sam</div><div>Dim</div>
        </div>
        <div class="calendar-grid">
            <?php
            $emptyDays = ($premierJourMois == 7) ? 0 : $premierJourMois;
            for($i = 0; $i < $emptyDays; $i++) {
                echo '<div class="day-cell empty"></div>';
            }
            
            for($jour = 1; $jour <= $nbJoursMois; $jour++) {
                $dateKey = sprintf("%04d-%02d-%02d", $annee, $mois, $jour);
                $estAujourdhui = ($dateKey == $aujourdhui);
                $estPasse = ($dateKey < $aujourdhui);
                $aDesRdvs = isset($rdvParDate[$dateKey]);
                $nbRdvs = $aDesRdvs ? count($rdvParDate[$dateKey]) : 0;
                
                $classes = 'day-cell';
                if($estAujourdhui) $classes .= ' today';
                if($estPasse && !$estAujourdhui) $classes .= ' past';
                if($aDesRdvs) $classes .= ' has-rdv';
            ?>
                <div class="<?= $classes ?>" data-date="<?= $dateKey ?>">
                    <span class="day-number"><?= $jour ?></span>
                    <?php if($aDesRdvs): ?>
                        <span class="rdv-badge" onclick="event.stopPropagation(); openDayModal('<?= $dateKey ?>', <?= $nbRdvs ?>)"><?= $nbRdvs ?></span>
                    <?php endif; ?>
                    <button class="add-rdv-btn" onclick="event.stopPropagation(); ajouterRdvDate('<?= $dateKey ?>')" title="Ajouter un rendez-vous ce jour">+</button>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<!-- Modal Rendez-vous du jour -->
<div id="dayModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>📅 Rendez-vous du <span id="modalDate"></span></h3>
            <span class="modal-close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body" id="modalRdvs"></div>
        <div class="modal-footer">
            <button onclick="ajouterRdvDate(currentDate)" class="btn btn-primary" style="background:var(--pastel-vert);color:#2D6A4F;">
                ➕ Ajouter un rendez-vous ce jour
            </button>
            <button onclick="closeModal()" class="btn btn-secondary">Fermer</button>
        </div>
    </div>
</div>

<!-- Modal Fiche Patient -->
<div id="patientModal" class="modal">
    <div class="modal-content patient-modal">
        <div class="modal-header" style="background:var(--pastel-lavande);">
            <h3>👤 Fiche Patient</h3>
            <span class="modal-close" onclick="closePatientModal()">&times;</span>
        </div>
        <div class="modal-body" id="modalPatient"></div>
        <div class="modal-footer">
            <button onclick="closePatientModal()" class="btn btn-secondary">Fermer</button>
        </div>
    </div>
</div>

<style>
/* === STYLES === */
.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
}
.calendar-header h1 {
    color: var(--accent);
    font-size: 28px;
}
.calendar-header p {
    color: var(--text-light);
}
.stat-badge {
    background: white;
    padding: 8px 20px;
    border-radius: 40px;
    box-shadow: var(--shadow-sm);
    font-weight: 500;
}

.calendar-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
}
.nav-buttons {
    display: flex;
    align-items: center;
    gap: 20px;
}
.nav-btn {
    background: var(--pastel-lavande);
    padding: 8px 20px;
    border-radius: 40px;
    text-decoration: none;
    color: var(--accent);
    font-weight: 600;
    transition: all 0.3s;
}
.nav-btn:hover {
    background: var(--accent);
    color: white;
}
.nav-buttons h2 {
    color: var(--accent);
    font-size: 22px;
    min-width: 180px;
    text-align: center;
}
.today-btn {
    background: var(--pastel-vert);
    padding: 8px 24px;
    border-radius: 40px;
    text-decoration: none;
    color: #2D6A4F;
    font-weight: 500;
}

.legend {
    display: flex;
    gap: 24px;
    margin-bottom: 24px;
    flex-wrap: wrap;
    padding: 12px 20px;
    background: white;
    border-radius: 20px;
    box-shadow: var(--shadow-sm);
}
.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: var(--text);
}
.legend-dot {
    width: 16px;
    height: 16px;
    border-radius: 8px;
}
.legend-dot.past { background: #E4DFF7; }
.legend-dot.today { background: #FADADD; border: 2px solid #E07A5F; }
.legend-dot.future { background: #C8E6E0; }
.legend-dot.has-rdv { background: var(--accent); }

/* Grille calendrier */
.calendar-grid-wrapper {
    background: white;
    border-radius: 24px;
    padding: 20px;
    box-shadow: var(--shadow);
}
.weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 8px;
    margin-bottom: 12px;
}
.weekdays div {
    text-align: center;
    padding: 10px;
    font-weight: 600;
    color: var(--accent);
    background: var(--bg-primary);
    border-radius: 12px;
    font-size: 13px;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 8px;
}
.day-cell {
    position: relative;
    aspect-ratio: 1;
    background: var(--bg-primary);
    border-radius: 16px;
    padding: 10px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    min-height: 80px;
}
.day-cell:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow);
    border-color: var(--accent);
}
.day-cell.empty {
    background: transparent;
    cursor: default;
    box-shadow: none;
}
.day-cell.empty:hover {
    transform: none;
    border-color: transparent;
}
.day-cell.past {
    background: #F8F6FC;
}
.day-cell.today {
    border-color: #E07A5F;
    background: #FDF0EE;
}
.day-cell.today .day-number {
    color: #E07A5F;
    font-weight: 700;
}
.day-cell.has-rdv {
    background: #C8E6E0;
}
.day-cell.has-rdv.today {
    background: #FADADD;
}
.day-number {
    font-size: 16px;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 4px;
}
.rdv-badge {
    background: var(--accent);
    color: white;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 10px;
    border-radius: 40px;
    margin-top: 4px;
    cursor: pointer;
    z-index: 2;
    position: relative;
}
.rdv-badge:hover {
    transform: scale(1.1);
}

/* === BOUTON + POUR AJOUTER UN RDV === */
.add-rdv-btn {
    position: absolute;
    bottom: 6px;
    right: 6px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: none;
    background: var(--accent);
    color: white;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    opacity: 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    z-index: 2;
}
.day-cell:hover .add-rdv-btn {
    opacity: 1;
}
.add-rdv-btn:hover {
    transform: scale(1.15);
    background: #5a7a8a;
}
.day-cell.past .add-rdv-btn {
    display: none;
}
.day-cell.empty .add-rdv-btn {
    display: none;
}

/* Modals */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}
.modal-content {
    background: white;
    border-radius: 28px;
    width: 90%;
    max-width: 550px;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: var(--shadow-lg);
}
.modal-content.patient-modal {
    max-width: 650px;
}
.modal-header {
    padding: 20px 24px;
    border-radius: 28px 28px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.modal-header h3 {
    color: var(--accent);
}
.modal-close {
    font-size: 28px;
    cursor: pointer;
    color: var(--accent);
}
.modal-body {
    padding: 20px 24px;
}
.modal-footer {
    padding: 16px 24px;
    display: flex;
    gap: 12px;
    border-top: 1px solid var(--border);
}
.btn-secondary {
    background: #EBEBEB;
    padding: 12px 24px;
    border-radius: 40px;
    border: none;
    cursor: pointer;
    font-weight: 500;
}
.btn-primary {
    padding: 12px 24px;
    border-radius: 40px;
    border: none;
    cursor: pointer;
    font-weight: 500;
}

/* RDV Card */
.rdv-card {
    background: var(--bg-primary);
    border-radius: 16px;
    padding: 16px;
    margin-bottom: 12px;
    border-left: 4px solid var(--accent);
    transition: all 0.2s;
}
.rdv-card:hover {
    transform: translateX(5px);
}
.rdv-card .rdv-time {
    font-weight: 700;
    color: var(--accent);
    font-size: 16px;
}
.rdv-card .rdv-patient {
    font-weight: 600;
    font-size: 16px;
}
.rdv-card .rdv-detail {
    font-size: 13px;
    color: var(--text-light);
    margin-top: 4px;
}
.rdv-card .rdv-actions {
    margin-top: 12px;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.rdv-card .rdv-actions .btn-sm {
    padding: 6px 16px;
    border-radius: 30px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 500;
    border: none;
    cursor: pointer;
}
.rdv-card .badge {
    float: right;
}

/* AFFICHER TOUTES LES NOTES DANS LA CARTE RDV */
.rdv-card .notes-full {
    margin-top: 8px;
    background: rgba(123, 143, 161, 0.08);
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 13px;
    color: var(--text-light);
    max-height: 150px;
    overflow-y: auto;
    word-wrap: break-word;
    white-space: pre-wrap;
}
.rdv-card .notes-full::-webkit-scrollbar {
    width: 4px;
}
.rdv-card .notes-full::-webkit-scrollbar-track {
    background: transparent;
}
.rdv-card .notes-full::-webkit-scrollbar-thumb {
    background: var(--accent);
    border-radius: 10px;
}
.rdv-card .notes-full .note-line {
    padding: 4px 0;
    border-bottom: 1px solid rgba(123, 143, 161, 0.1);
}
.rdv-card .notes-full .note-line:last-child {
    border-bottom: none;
}

/* Fiche Patient */
.patient-profile {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.patient-header-info {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 16px 20px;
    background: var(--bg-primary);
    border-radius: 20px;
}
.patient-avatar {
    font-size: 56px;
    background: var(--pastel-lavande);
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.patient-name-title {
    font-size: 22px;
    font-weight: 700;
    color: var(--accent);
}
.patient-subtitle {
    color: var(--text-light);
    font-size: 14px;
}

.patient-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
.info-item {
    background: var(--bg-primary);
    padding: 12px 16px;
    border-radius: 16px;
}
.info-item .label {
    font-size: 11px;
    color: var(--text-light);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.info-item .value {
    font-size: 15px;
    font-weight: 500;
    color: var(--text);
    margin-top: 4px;
}
.info-item .value.phone {
    color: #2D6A4F;
}
.info-item .value.full {
    grid-column: span 2;
}

.patient-section {
    margin-top: 8px;
}
.patient-section-title {
    font-weight: 600;
    color: var(--accent);
    font-size: 15px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.patient-section-title::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border);
}

.rdv-history-item {
    background: var(--bg-primary);
    padding: 12px 16px;
    border-radius: 16px;
    margin-bottom: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}
.rdv-history-item .date {
    font-weight: 600;
    color: var(--accent);
}
.rdv-history-item .info {
    font-size: 13px;
    color: var(--text-light);
}
.rdv-history-item .status {
    font-size: 12px;
}

/* Notes médicales dans la fiche patient */
.notes-history {
    max-height: 250px;
    overflow-y: auto;
    background: var(--bg-primary);
    border-radius: 16px;
    padding: 12px;
}
.notes-history::-webkit-scrollbar {
    width: 6px;
}
.notes-history::-webkit-scrollbar-track {
    background: var(--bg-primary);
    border-radius: 10px;
}
.notes-history::-webkit-scrollbar-thumb {
    background: var(--accent);
    border-radius: 10px;
}
.note-item {
    background: white;
    padding: 10px 14px;
    border-radius: 12px;
    margin-bottom: 8px;
    border-left: 3px solid var(--accent);
    word-wrap: break-word;
    white-space: normal;
    overflow-wrap: break-word;
}
.note-item .note-date {
    font-size: 11px;
    color: var(--text-light);
    font-weight: 500;
}
.note-item .note-content {
    font-size: 14px;
    color: var(--text);
    margin-top: 4px;
    word-wrap: break-word;
    white-space: normal;
    overflow-wrap: break-word;
}
.note-item .note-actions {
    margin-top: 8px;
    display: flex;
    gap: 8px;
}
.note-item .note-actions .btn-sm {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 500;
    border: none;
    cursor: pointer;
}

@media (max-width: 768px) {
    .calendar-grid { gap: 4px; }
    .day-cell { min-height: 60px; padding: 6px; }
    .day-number { font-size: 13px; }
    .rdv-badge { font-size: 9px; padding: 1px 8px; }
    .add-rdv-btn { width: 20px; height: 20px; font-size: 12px; bottom: 4px; right: 4px; }
    .nav-buttons h2 { font-size: 16px; min-width: 120px; }
    .calendar-nav { flex-direction: column; }
    .patient-info-grid { grid-template-columns: 1fr; }
    .modal-content.patient-modal { max-width: 95%; }
}
</style>

<script>
// Données des rendez-vous
const rdvData = <?php
$data = [];
foreach($rdvs as $rdv) {
    $data[$rdv['date_key']][] = [
        'id' => $rdv['id'],
        'heure' => substr($rdv['heure_rdv'], 0, 5),
        'patient' => $rdv['patient_prenom'] . ' ' . $rdv['patient_nom'],
        'patient_id' => $rdv['patient_id'],
        'telephone' => $rdv['telephone'],
        'email' => $rdv['email'],
        'motif' => $rdv['motif'],
        'statut' => $rdv['statut'],
        'notes' => $rdv['notes'] ?? ''
    ];
}
echo json_encode($data);
?>;

// Données complètes des patients
const patientsData = <?php
$data = [];
foreach($patients as $id => $p) {
    $data[$id] = $p;
}
echo json_encode($data);
?>;

let currentDate = null;

// === AJOUTER UN RENDEZ-VOUS POUR UNE DATE ===
function ajouterRdvDate(date) {
    if(!date) {
        alert('Aucune date sélectionnée');
        return;
    }
    window.location.href = 'ajouter.php?date=' + date;
}

// === OUVERTURE DU MODAL RDV DU JOUR ===
function openDayModal(date, nbRdvs) {
    const modal = document.getElementById('dayModal');
    const modalDate = document.getElementById('modalDate');
    const modalBody = document.getElementById('modalRdvs');
    currentDate = date;
    
    const [year, month, day] = date.split('-');
    const dateObj = new Date(year, month-1, day);
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    modalDate.textContent = dateObj.toLocaleDateString('fr-FR', options);
    
    if(nbRdvs > 0 && rdvData[date]) {
        let html = '';
        rdvData[date].forEach(rdv => {
            let statusClass = rdv.statut === 'confirme' ? 'badge-confirme' : 
                             (rdv.statut === 'en_attente' ? 'badge-en_attente' : 
                             (rdv.statut === 'termine' ? 'badge-termine' : 'badge-annule'));
            
            let notesHtml = '';
            if(rdv.notes && rdv.notes.trim() !== '') {
                const noteLines = rdv.notes.split('\n').filter(n => n.trim() !== '');
                if(noteLines.length > 0) {
                    notesHtml = '<div class="notes-full">';
                    noteLines.forEach(line => {
                        notesHtml += `<div class="note-line">📝 ${line.replace(/</g,'&lt;').replace(/>/g,'&gt;')}</div>`;
                    });
                    notesHtml += '</div>';
                }
            }
            
            html += `
                <div class="rdv-card">
                    <div>
                        <span class="rdv-time">⏰ ${rdv.heure}</span>
                        <span class="badge ${statusClass}">${rdv.statut}</span>
                    </div>
                    <div class="rdv-patient">👤 ${rdv.patient}</div>
                    <div class="rdv-detail">📞 ${rdv.telephone || 'Non renseigné'}</div>
                    <div class="rdv-detail">📝 ${rdv.motif}</div>
                    ${notesHtml}
                    <div class="rdv-actions">
                        <a href="modifier.php?id=${rdv.id}" class="btn-sm" style="background:var(--pastel-bleu);color:#2c5a7a;">✏️ Modifier RDV</a>
                        <button onclick="showPatientFiche(${rdv.patient_id})" class="btn-sm" style="background:var(--pastel-lavande);color:#5a4a8a;">👤 Fiche patient</button>
                    </div>
                </div>
            `;
        });
        modalBody.innerHTML = html;
    } else {
        modalBody.innerHTML = '<div style="text-align:center;padding:40px;color:var(--text-light);">📭 Aucun rendez-vous pour cette date</div>';
    }
    
    modal.style.display = 'flex';
}

function closeModal() {
    document.getElementById('dayModal').style.display = 'none';
}

// === AFFICHAGE DE LA FICHE PATIENT ===
function showPatientFiche(patientId) {
    const modal = document.getElementById('patientModal');
    const modalBody = document.getElementById('modalPatient');
    
    const patient = patientsData[patientId];
    if(!patient) {
        modalBody.innerHTML = '<div style="text-align:center;padding:40px;">Patient non trouvé</div>';
        modal.style.display = 'flex';
        return;
    }
    
    // Calculer l'âge
    let age = '';
    if(patient.date_naissance) {
        const birth = new Date(patient.date_naissance);
        const today = new Date();
        let ageCalc = today.getFullYear() - birth.getFullYear();
        const m = today.getMonth() - birth.getMonth();
        if(m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
            ageCalc--;
        }
        age = ageCalc + ' ans';
    }
    
    // Historique des rendez-vous
    const historique = [];
    const notesHistorique = [];
    for(const date in rdvData) {
        rdvData[date].forEach(rdv => {
            if(rdv.patient_id == patientId) {
                historique.push({
                    date: date,
                    heure: rdv.heure,
                    motif: rdv.motif,
                    statut: rdv.statut
                });
                if(rdv.notes && rdv.notes.trim() !== '') {
                    const noteLines = rdv.notes.split('\n').filter(n => n.trim() !== '');
                    noteLines.forEach((note) => {
                        notesHistorique.push({
                            date: date,
                            heure: rdv.heure,
                            note: note.trim()
                        });
                    });
                }
            }
        });
    }
    historique.sort((a, b) => a.date.localeCompare(b.date));
    notesHistorique.sort((a, b) => a.date.localeCompare(b.date));
    
    let historiqueHtml = '';
    if(historique.length > 0) {
        historique.forEach(h => {
            let statusClass = h.statut === 'confirme' ? 'badge-confirme' : 
                             (h.statut === 'en_attente' ? 'badge-en_attente' : 
                             (h.statut === 'termine' ? 'badge-termine' : 'badge-annule'));
            historiqueHtml += `
                <div class="rdv-history-item">
                    <span class="date">📅 ${h.date} ${h.heure}</span>
                    <span class="info">${h.motif}</span>
                    <span class="badge ${statusClass}">${h.statut}</span>
                </div>
            `;
        });
    } else {
        historiqueHtml = '<div style="color:var(--text-light);font-size:13px;">Aucun historique</div>';
    }
    
    // Notes existantes
    let notesHtml = '';
    if(notesHistorique.length > 0) {
        notesHistorique.forEach(n => {
            let noteContent = n.note;
            let noteDate = n.date + ' ' + n.heure;
            const dateMatch = n.note.match(/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}) - (.*)/);
            if(dateMatch) {
                noteDate = dateMatch[1];
                noteContent = dateMatch[2];
            }
            notesHtml += `
                <div class="note-item">
                    <div class="note-date">📅 ${noteDate}</div>
                    <div class="note-content">${noteContent.replace(/</g,'&lt;').replace(/>/g,'&gt;')}</div>
                </div>
            `;
        });
    } else {
        notesHtml = '<div style="color:var(--text-light);font-size:13px;">Aucune note médicale enregistrée</div>';
    }
    
    modalBody.innerHTML = `
        <div class="patient-profile">
            <div class="patient-header-info">
                <div class="patient-avatar">👤</div>
                <div>
                    <div class="patient-name-title">${patient.prenom} ${patient.nom}</div>
                    <div class="patient-subtitle">🆔 ID: #${patient.id} ${age ? '· ' + age : ''}</div>
                </div>
            </div>
            
            <div class="patient-section">
                <div class="patient-section-title">📋 Coordonnées</div>
                <div class="patient-info-grid">
                    <div class="info-item">
                        <div class="label">📧 Email</div>
                        <div class="value">${patient.email || 'Non renseigné'}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">📞 Téléphone</div>
                        <div class="value phone">${patient.telephone || 'Non renseigné'}</div>
                    </div>
                    <div class="info-item" style="grid-column: span 2;">
                        <div class="label">📍 Adresse</div>
                        <div class="value">${patient.adresse || 'Non renseignée'}</div>
                    </div>
                    ${patient.date_naissance ? `
                    <div class="info-item">
                        <div class="label">🎂 Date de naissance</div>
                        <div class="value">${patient.date_naissance}</div>
                    </div>
                    ` : ''}
                </div>
            </div>
            
            <div class="patient-section">
                <div class="patient-section-title">📅 Historique des rendez-vous</div>
                ${historiqueHtml}
            </div>
            
            <div class="patient-section">
                <div class="patient-section-title">📋 Notes médicales</div>
                <div class="notes-history">
                    ${notesHtml}
                </div>
                <div style="margin-top:12px;font-size:13px;color:var(--text-light);padding:12px;background:var(--pastel-bleu);border-radius:12px;">
                    💡 Pour ajouter ou modifier une note, utilisez la page <strong>"Modifier RDV"</strong>
                </div>
            </div>
        </div>
    `;
    
    modal.style.display = 'flex';
}

function closePatientModal() {
    document.getElementById('patientModal').style.display = 'none';
}

// === OUVERTURE AUTOMATIQUE APRÈS AJOUT DE RDV ===
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const rdvAjoute = urlParams.get('rdv_ajoute');
    const patientId = urlParams.get('patient_id');
    const date = urlParams.get('date');
    
    if(rdvAjoute && patientId && date) {
        const nbRdvs = rdvData[date] ? rdvData[date].length : 0;
        openDayModal(date, nbRdvs);
        setTimeout(function() {
            showPatientFiche(parseInt(patientId));
        }, 800);
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
});

// === FERMETURE DES MODAUX ===
window.onclick = function(event) {
    const modal = document.getElementById('dayModal');
    const patientModal = document.getElementById('patientModal');
    if (event.target === modal) { modal.style.display = 'none'; }
    if (event.target === patientModal) { patientModal.style.display = 'none'; }
}
</script>

<?php include '../../includes/footer.php'; ?>