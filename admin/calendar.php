<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

require_once '../config/database.php';
$pdo = getPDO();

// Récupérer tous les rendez-vous
$rdvs = $pdo->query("
    SELECT r.*, 
           p.nom as patient_nom, p.prenom as patient_prenom,
           m.nom as medecin_nom, m.prenom as medecin_prenom
    FROM rendez_vous r
    JOIN users p ON r.patient_id = p.id
    JOIN users m ON r.medecin_id = m.id
    WHERE r.statut != 'annule'
    ORDER BY r.date_rdv ASC, r.heure_rdv ASC
")->fetchAll();

// Organiser les RDV par date
$rdvParDate = [];
foreach($rdvs as $rdv) {
    $date = $rdv['date_rdv'];
    if(!isset($rdvParDate[$date])) {
        $rdvParDate[$date] = [];
    }
    $rdvParDate[$date][] = $rdv;
}

// Mois et année actuels
$mois = isset($_GET['mois']) ? (int)$_GET['mois'] : date('n');
$annee = isset($_GET['annee']) ? (int)$_GET['annee'] : date('Y');

if($mois < 1) { $mois = 12; $annee--; }
if($mois > 12) { $mois = 1; $annee++; }

$dateActuelle = strtotime("$annee-$mois-01");
$premierJourMois = date('N', $dateActuelle);
$nbJoursMois = date('t', $dateActuelle);

// Statistiques pour la légende
$stats = $pdo->query("
    SELECT 
        SUM(CASE WHEN date_rdv < CURDATE() THEN 1 ELSE 0 END) as passes,
        SUM(CASE WHEN date_rdv = CURDATE() THEN 1 ELSE 0 END) as aujourdhui,
        SUM(CASE WHEN date_rdv > CURDATE() THEN 1 ELSE 0 END) as futurs
    FROM rendez_vous WHERE statut != 'annule'
")->fetch();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <!-- En-tête du calendrier -->
    <div class="calendar-wrapper">
        <div class="calendar-header-modern">
            <div class="calendar-nav">
                <a href="?mois=<?= $mois-1 ?>&annee=<?= $annee ?>" class="nav-btn">← Décembre</a>
                <h2><?= date('F Y', $dateActuelle) ?></h2>
                <a href="?mois=<?= $mois+1 ?>&annee=<?= $annee ?>" class="nav-btn">Février →</a>
            </div>
            <div class="calendar-actions">
                <a href="?mois=<?= date('n') ?>&annee=<?= date('Y') ?>" class="today-btn">📅 Aujourd'hui</a>
                <a href="rdv/ajouter.php" class="add-rdv-btn">➕ Nouveau rendez-vous</a>
            </div>
        </div>

        <!-- Légende des couleurs -->
        <div class="calendar-legend">
            <div class="legend-title">📖 Légende :</div>
            <div class="legend-items">
                <div class="legend-item">
                    <div class="legend-color past"></div>
                    <span>Rendez-vous passés (<?= $stats['passes'] ?>)</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color today"></div>
                    <span>Aujourd'hui (<?= $stats['aujourdhui'] ?>)</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color future"></div>
                    <span>Rendez-vous futurs (<?= $stats['futurs'] ?>)</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color empty"></div>
                    <span>Aucun rendez-vous</span>
                </div>
            </div>
        </div>

        <!-- Jours de la semaine -->
        <div class="calendar-weekdays">
            <div class="weekday">Lundi</div>
            <div class="weekday">Mardi</div>
            <div class="weekday">Mercredi</div>
            <div class="weekday">Jeudi</div>
            <div class="weekday">Vendredi</div>
            <div class="weekday">Samedi</div>
            <div class="weekday">Dimanche</div>
        </div>

        <!-- Grille du calendrier -->
        <div class="calendar-grid">
            <?php
            // Cases vides avant le premier jour
            $emptyDays = ($premierJourMois == 7) ? 0 : $premierJourMois;
            for($i = 0; $i < $emptyDays; $i++) {
                echo '<div class="calendar-day empty"></div>';
            }
            
            // Jours du mois
            $aujourdhui = date('Y-m-d');
            for($jour = 1; $jour <= $nbJoursMois; $jour++) {
                $dateKey = sprintf("%04d-%02d-%02d", $annee, $mois, $jour);
                $estAujourdhui = ($dateKey == $aujourdhui);
                $aDesRdvs = isset($rdvParDate[$dateKey]);
                
                // Déterminer la classe de couleur
                $colorClass = '';
                if($aDesRdvs) {
                    if($dateKey < $aujourdhui) $colorClass = 'has-rdv-past';
                    elseif($dateKey == $aujourdhui) $colorClass = 'has-rdv-today';
                    else $colorClass = 'has-rdv-future';
                }
                
                $nbRdvs = $aDesRdvs ? count($rdvParDate[$dateKey]) : 0;
            ?>
                <div class="calendar-day <?= $colorClass ?> <?= $estAujourdhui ? 'today' : '' ?>" 
                     data-date="<?= $dateKey ?>"
                     onclick="showRdvs('<?= $dateKey ?>', <?= $nbRdvs ?>)">
                    <span class="day-number"><?= $jour ?></span>
                    <?php if($aDesRdvs): ?>
                        <span class="rdv-count"><?= $nbRdvs ?> RDV</span>
                    <?php endif; ?>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- Modal pour afficher les détails des rendez-vous -->
    <div id="rdvModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>📅 Rendez-vous du <span id="modalDate"></span></h3>
                <span class="modal-close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Les RDV seront injectés ici -->
            </div>
            <div class="modal-footer">
                <a href="rdv/ajouter.php" id="addRdvLink" class="btn btn-primary">➕ Ajouter un rendez-vous</a>
                <button onclick="closeModal()" class="btn" style="background:#EBEBEB;">Fermer</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles du calendrier moderne */
.calendar-wrapper {
    background: white;
    border-radius: 28px;
    padding: 24px;
    box-shadow: var(--shadow);
    margin-bottom: 24px;
}

.calendar-header-modern {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 16px;
}

.calendar-nav {
    display: flex;
    align-items: center;
    gap: 24px;
}

.nav-btn {
    background: var(--pastel-lavande);
    padding: 8px 20px;
    border-radius: 40px;
    text-decoration: none;
    color: var(--accent);
    font-weight: 500;
    transition: all 0.3s;
}

.nav-btn:hover {
    background: var(--accent);
    color: white;
}

.calendar-header-modern h2 {
    color: var(--accent);
    font-size: 24px;
    min-width: 200px;
    text-align: center;
}

.calendar-actions {
    display: flex;
    gap: 12px;
}

.today-btn {
    background: var(--pastel-vert);
    padding: 8px 20px;
    border-radius: 40px;
    text-decoration: none;
    color: #2D6A4F;
    font-weight: 500;
}

.add-rdv-btn {
    background: var(--accent);
    padding: 8px 20px;
    border-radius: 40px;
    text-decoration: none;
    color: white;
    font-weight: 500;
}

/* Légende */
.calendar-legend {
    background: var(--bg-primary);
    padding: 16px 20px;
    border-radius: 20px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 32px;
    flex-wrap: wrap;
}

.legend-title {
    font-weight: 600;
    color: var(--accent);
}

.legend-items {
    display: flex;
    gap: 24px;
    flex-wrap: wrap;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
}

.legend-color {
    width: 20px;
    height: 20px;
    border-radius: 8px;
}

.legend-color.past { background: #E4DFF7; }
.legend-color.today { background: #FADADD; }
.legend-color.future { background: #C8E6E0; }
.legend-color.empty { background: #FDFBF7; border: 2px solid #EBEBEB; }

/* Grille calendrier */
.calendar-weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 8px;
    margin-bottom: 12px;
}

.weekday {
    text-align: center;
    padding: 12px;
    font-weight: 600;
    color: var(--accent);
    background: var(--bg-primary);
    border-radius: 16px;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 8px;
}

.calendar-day {
    aspect-ratio: 1;
    background: #FDFBF7;
    border-radius: 16px;
    padding: 12px;
    display: flex;
    flex-direction: column;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    position: relative;
}

.calendar-day:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow);
    border-color: var(--accent);
}

.calendar-day.empty {
    background: transparent;
    cursor: default;
    box-shadow: none;
}

.calendar-day.empty:hover {
    transform: none;
    border-color: transparent;
}

.day-number {
    font-size: 16px;
    font-weight: 600;
    color: var(--text);
}

.rdv-count {
    font-size: 11px;
    margin-top: 8px;
    padding: 4px 8px;
    border-radius: 20px;
    text-align: center;
    font-weight: 500;
}

/* Couleurs des jours selon RDV */
.has-rdv-past {
    background: #E4DFF7;
}
.has-rdv-past .rdv-count {
    background: rgba(123, 143, 161, 0.2);
    color: #5a4a8a;
}

.has-rdv-today {
    background: #FADADD;
    border: 2px solid #E07A5F;
}
.has-rdv-today .rdv-count {
    background: rgba(224, 122, 95, 0.2);
    color: #E07A5F;
}

.has-rdv-future {
    background: #C8E6E0;
}
.has-rdv-future .rdv-count {
    background: rgba(72, 187, 120, 0.2);
    color: #2D6A4F;
}

.calendar-day.today .day-number {
    color: #E07A5F;
    font-weight: 700;
}

/* Modal */
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
    max-width: 500px;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: var(--shadow-lg);
}

.modal-header {
    padding: 20px 24px;
    background: var(--pastel-lavande);
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

.rdv-item-modal {
    background: var(--bg-primary);
    border-radius: 16px;
    padding: 12px 16px;
    margin-bottom: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

.rdv-time {
    font-weight: 700;
    color: var(--accent);
    background: white;
    padding: 4px 12px;
    border-radius: 20px;
}

.rdv-details {
    flex: 1;
}

.rdv-patient {
    font-weight: 600;
}

.rdv-medecin {
    font-size: 13px;
    color: var(--text-light);
}

.rdv-motif {
    font-size: 12px;
}

.rdv-status {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 500;
}

.no-rdv {
    text-align: center;
    padding: 40px;
    color: var(--text-light);
}

@media (max-width: 768px) {
    .calendar-wrapper { padding: 16px; }
    .calendar-grid { gap: 4px; }
    .calendar-day { padding: 6px; }
    .day-number { font-size: 12px; }
    .rdv-count { font-size: 9px; }
    .legend-items { gap: 12px; }
    .legend-item span { font-size: 10px; }
}
</style>

<script>
// Données des rendez-vous côté client
const rdvData = <?php
$data = [];
foreach($rdvs as $rdv) {
    $data[$rdv['date_rdv']][] = [
        'heure' => substr($rdv['heure_rdv'], 0, 5),
        'patient' => $rdv['patient_prenom'] . ' ' . $rdv['patient_nom'],
        'medecin' => 'Dr. ' . $rdv['medecin_nom'],
        'motif' => $rdv['motif'],
        'statut' => $rdv['statut']
    ];
}
echo json_encode($data);
?>;

function showRdvs(date, nbRdvs) {
    const modal = document.getElementById('rdvModal');
    const modalDate = document.getElementById('modalDate');
    const modalBody = document.getElementById('modalBody');
    const addRdvLink = document.getElementById('addRdvLink');
    
    // Formater la date
    const [year, month, day] = date.split('-');
    const dateObj = new Date(year, month-1, day);
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    modalDate.textContent = dateObj.toLocaleDateString('fr-FR', options);
    
    // Ajouter la date dans le lien d'ajout
    addRdvLink.href = `rdv/ajouter.php?date=${date}`;
    
    if(nbRdvs > 0 && rdvData[date]) {
        let html = '';
        rdvData[date].forEach(rdv => {
            let statusClass = '';
            if(rdv.statut === 'confirme') statusClass = 'badge-confirme';
            else if(rdv.statut === 'en_attente') statusClass = 'badge-en_attente';
            else if(rdv.statut === 'termine') statusClass = 'badge-termine';
            else statusClass = 'badge-annule';
            
            html += `
                <div class="rdv-item-modal">
                    <div class="rdv-time">⏰ ${rdv.heure}</div>
                    <div class="rdv-details">
                        <div class="rdv-patient">👤 ${rdv.patient}</div>
                        <div class="rdv-medecin">👨‍⚕️ ${rdv.medecin}</div>
                        <div class="rdv-motif">📝 ${rdv.motif}</div>
                    </div>
                    <div class="rdv-status ${statusClass}">${rdv.statut}</div>
                </div>
            `;
        });
        modalBody.innerHTML = html;
    } else {
        modalBody.innerHTML = '<div class="no-rdv">📭 Aucun rendez-vous programmé pour cette date</div>';
    }
    
    modal.style.display = 'flex';
}

function closeModal() {
    document.getElementById('rdvModal').style.display = 'none';
}

// Fermer le modal en cliquant en dehors
window.onclick = function(event) {
    const modal = document.getElementById('rdvModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}
</script>

<?php include '../includes/footer.php'; ?>