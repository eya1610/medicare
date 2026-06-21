<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../../login.php');
    exit();
}

require_once '../../config/database.php';
$pdo = getPDO();

$stmt = $pdo->query("
    SELECT r.date_rdv, r.heure_rdv, 
           p.nom as patient_nom, p.prenom as patient_prenom,
           m.nom as medecin_nom, m.prenom as medecin_prenom,
           r.motif, r.statut
    FROM rendez_vous r
    JOIN users p ON r.patient_id = p.id
    JOIN users m ON r.medecin_id = m.id
    ORDER BY r.date_rdv DESC
");

// En-têtes CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="rendez_vous_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Date', 'Heure', 'Patient', 'Médecin', 'Motif', 'Statut']);

while($row = $stmt->fetch()) {
    fputcsv($output, [
        $row['date_rdv'],
        $row['heure_rdv'],
        $row['patient_prenom'] . ' ' . $row['patient_nom'],
        'Dr. ' . $row['medecin_prenom'] . ' ' . $row['medecin_nom'],
        $row['motif'],
        $row['statut']
    ]);
}

fclose($output);
exit();
?>