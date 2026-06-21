<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'medecin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

require_once '../../config/database.php';
$pdo = getPDO();

$patient_id = isset($_POST['patient_id']) ? (int)$_POST['patient_id'] : 0;
$notes = trim($_POST['notes'] ?? '');

$response = ['success' => false, 'message' => '', 'rdv_id' => 0, 'notes' => '', 'date' => '', 'heure' => '', 'nouvelle_note' => ''];

if(!empty($notes) && $patient_id > 0) {
    try {
        $stmt = $pdo->prepare("
            SELECT id, notes, date_rdv, heure_rdv FROM rendez_vous 
            WHERE patient_id = ? AND statut != 'annule'
            ORDER BY date_rdv DESC, heure_rdv DESC 
            LIMIT 1
        ");
        $stmt->execute([$patient_id]);
        $rdv = $stmt->fetch();
        
        $date_rdv = date('Y-m-d');
        $heure_rdv = date('H:i');
        $nouvelleNote = date('Y-m-d H:i') . ' - ' . $notes;
        
        if($rdv) {
            $date_rdv = $rdv['date_rdv'];
            $heure_rdv = substr($rdv['heure_rdv'], 0, 5);
            $notesExistantes = $rdv['notes'] ?? '';
            $notesFinales = $notesExistantes . ($notesExistantes ? "\n" : '') . $nouvelleNote;
            
            $stmt = $pdo->prepare("UPDATE rendez_vous SET notes = ? WHERE id = ?");
            $stmt->execute([$notesFinales, $rdv['id']]);
            
            $response['success'] = true;
            $response['rdv_id'] = $rdv['id'];
            $response['notes'] = $notesFinales;
            $response['date'] = $date_rdv;
            $response['heure'] = $heure_rdv;
            $response['nouvelle_note'] = $nouvelleNote;
        } else {
            $medecin_id = $_SESSION['user_id'];
            $stmt = $pdo->prepare("
                INSERT INTO rendez_vous (patient_id, medecin_id, date_rdv, heure_rdv, motif, statut, notes) 
                VALUES (?, ?, CURDATE(), '00:00:00', 'Note médicale', 'termine', ?)
            ");
            $stmt->execute([$patient_id, $medecin_id, $nouvelleNote]);
            
            $response['success'] = true;
            $response['rdv_id'] = $pdo->lastInsertId();
            $response['notes'] = $nouvelleNote;
            $response['date'] = date('Y-m-d');
            $response['heure'] = date('H:i');
            $response['nouvelle_note'] = $nouvelleNote;
        }
    } catch(PDOException $e) {
        $response['success'] = false;
        $response['message'] = 'Erreur SQL: ' . $e->getMessage();
    }
} else {
    $response['success'] = false;
    $response['message'] = 'Veuillez saisir une note';
}

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>