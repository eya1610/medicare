<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../../login.php');
    exit();
}

require_once '../../config/database.php';
require_once '../../assets/libs/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$pdo = getPDO();

// Récupérer les rendez-vous (avec filtres)
$medecin_id = isset($_GET['medecin']) ? $_GET['medecin'] : '';
$statut = isset($_GET['statut']) ? $_GET['statut'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "
    SELECT r.*, 
           p.nom as patient_nom, p.prenom as patient_prenom, p.telephone as patient_telephone,
           m.nom as medecin_nom, m.prenom as medecin_prenom, m.specialite
    FROM rendez_vous r
    JOIN users p ON r.patient_id = p.id
    JOIN users m ON r.medecin_id = m.id
    WHERE 1=1
";

$params = [];

if($search) {
    $sql .= " AND (p.nom LIKE ? OR p.prenom LIKE ? OR m.nom LIKE ? OR m.prenom LIKE ? OR r.motif LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
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

// Statistiques
$totalRdvs = count($rdvs);
$stmt = $pdo->query("SELECT COUNT(*) FROM rendez_vous");
$totalAll = $stmt->fetchColumn();

// Générer le HTML
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Liste des rendez-vous</title>
    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #7B8FA1;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #7B8FA1;
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header p {
            color: #888;
            font-size: 14px;
            margin: 0;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 25px;
            padding: 15px;
            background: #F8F9FA;
            border-radius: 10px;
        }
        .stats div {
            text-align: center;
        }
        .stats .number {
            font-size: 22px;
            font-weight: bold;
            color: #7B8FA1;
        }
        .stats .label {
            font-size: 12px;
            color: #888;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th {
            background: #7B8FA1;
            color: white;
            padding: 10px 12px;
            text-align: left;
            font-size: 12px;
        }
        td {
            padding: 8px 12px;
            border-bottom: 1px solid #EBEBEB;
            font-size: 12px;
        }
        tr:nth-child(even) {
            background: #F8F9FA;
        }
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
        }
        .badge-confirme {
            background: #C8E6E0;
            color: #2D6A4F;
        }
        .badge-en_attente {
            background: #FFF2CC;
            color: #856404;
        }
        .badge-termine {
            background: #C5E0F7;
            color: #004085;
        }
        .badge-annule {
            background: #FADADD;
            color: #E07A5F;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #EBEBEB;
            font-size: 12px;
            color: #888;
        }
        .footer strong {
            color: #7B8FA1;
        }
        .filters-info {
            font-size: 12px;
            color: #888;
            margin-bottom: 15px;
            padding: 10px;
            background: #F8F9FA;
            border-radius: 8px;
        }
        .filters-info .tag {
            display: inline-block;
            background: #7B8FA1;
            color: white;
            padding: 2px 10px;
            border-radius: 20px;
            margin: 2px;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📋 Liste des rendez-vous</h1>
        <p>Clinique Plus - Gestion des rendez-vous médicaux</p>
        <p>Généré le ' . date('d/m/Y à H:i') . '</p>
    </div>
    
    <div class="stats">
        <div>
            <div class="number">' . $totalAll . '</div>
            <div class="label">Total RDV</div>
        </div>
        <div>
            <div class="number">' . $totalRdvs . '</div>
            <div class="label">RDV affichés</div>
        </div>
        <div>
            <div class="number">' . date('d/m/Y') . '</div>
            <div class="label">Date</div>
        </div>
    </div>
    
    <div class="filters-info">
        <span>📌 Filtres appliqués :</span>';

if($search) {
    $html .= '<span class="tag">🔍 ' . htmlspecialchars($search) . '</span>';
}
if($medecin_id) {
    $stmt = $pdo->prepare("SELECT nom, prenom FROM users WHERE id = ?");
    $stmt->execute([$medecin_id]);
    $m = $stmt->fetch();
    $html .= '<span class="tag">👨‍⚕️ Dr. ' . htmlspecialchars($m['prenom'] . ' ' . $m['nom']) . '</span>';
}
if($statut) {
    $statutLabel = $statut == 'en_attente' ? 'En attente' : ($statut == 'confirme' ? 'Confirmé' : ($statut == 'termine' ? 'Terminé' : 'Annulé'));
    $html .= '<span class="tag">📌 ' . $statutLabel . '</span>';
}
if($date_filter) {
    $html .= '<span class="tag">📅 ' . date('d/m/Y', strtotime($date_filter)) . '</span>';
}
if(!$search && !$medecin_id && !$statut && !$date_filter) {
    $html .= '<span class="tag">📋 Tous les rendez-vous</span>';
}

$html .= '
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Heure</th>
                <th>Patient</th>
                <th>Téléphone</th>
                <th>Médecin</th>
                <th>Spécialité</th>
                <th>Motif</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>';

if(count($rdvs) > 0) {
    foreach($rdvs as $rdv) {
        $statutClass = 'badge-' . $rdv['statut'];
        $statutLabel = $rdv['statut'] == 'en_attente' ? 'En attente' : 
                       ($rdv['statut'] == 'confirme' ? 'Confirmé' : 
                       ($rdv['statut'] == 'termine' ? 'Terminé' : 'Annulé'));
        
        $html .= '
            <tr>
                <td>' . date('d/m/Y', strtotime($rdv['date_rdv'])) . '</td>
                <td>' . substr($rdv['heure_rdv'], 0, 5) . '</td>
                <td>' . htmlspecialchars($rdv['patient_prenom'] . ' ' . $rdv['patient_nom']) . '</td>
                <td>' . htmlspecialchars($rdv['patient_telephone']) . '</td>
                <td>Dr. ' . htmlspecialchars($rdv['medecin_nom']) . '</td>
                <td>' . htmlspecialchars($rdv['specialite']) . '</td>
                <td>' . htmlspecialchars($rdv['motif']) . '</td>
                <td><span class="badge ' . $statutClass . '">' . $statutLabel . '</span></td>
            </tr>';
    }
} else {
    $html .= '
            <tr>
                <td colspan="8" style="text-align:center;padding:30px;color:#888;">
                    Aucun rendez-vous trouvé
                </td>
            </tr>';
}

$html .= '
        </tbody>
    </table>
    
    <div class="footer">
        <p>📋 Document généré par <strong>Clinique Plus</strong> - Système de gestion des rendez-vous</p>
    </div>
</body>
</html>';

// Configuration de Dompdf
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// Télécharger le PDF
$nomFichier = 'tous_les_rendez_vous_' . date('Y-m-d') . '.pdf';
$dompdf->stream($nomFichier, array('Attachment' => true));
exit();
?>