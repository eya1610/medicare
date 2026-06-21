<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'medecin') {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit();
}

require_once '../../config/database.php';
$pdo = getPDO();
$medecin_id = $_SESSION['user_id'];

// Récupérer tous les rendez-vous
$stmt = $pdo->prepare("
    SELECT r.*, 
           u.nom as patient_nom, u.prenom as patient_prenom, 
           u.telephone, u.email,
           DATE_FORMAT(r.date_rdv, '%Y-%m-%d') as date_key
    FROM rendez_vous r
    JOIN users u ON r.patient_id = u.id
    WHERE r.medecin_id = ? AND r.statut != 'annule'
    ORDER BY r.date_rdv ASC, r.heure_rdv ASC
");
$stmt->execute([$medecin_id]);
$rdvs = $stmt->fetchAll();

$data = [];
foreach($rdvs as $rdv) {
    $date = $rdv['date_key'];
    if(!isset($data[$date])) {
        $data[$date] = [];
    }
    $data[$date][] = [
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

header('Content-Type: application/json');
echo json_encode($data);
exit();
?>