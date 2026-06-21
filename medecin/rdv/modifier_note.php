<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'medecin') {
    header('Location: ../../login.php');
    exit();
}

require_once '../../config/database.php';
$pdo = getPDO();

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rdv_id = isset($_POST['rdv_id']) ? (int)$_POST['rdv_id'] : 0;
    $note_index = isset($_POST['note_index']) ? (int)$_POST['note_index'] : 0;
    $nouvelle_note = trim($_POST['nouvelle_note'] ?? '');
    
    if(!empty($nouvelle_note) && $rdv_id > 0) {
        try {
            $stmt = $pdo->prepare("SELECT notes FROM rendez_vous WHERE id = ?");
            $stmt->execute([$rdv_id]);
            $rdv = $stmt->fetch();
            
            if($rdv && $rdv['notes']) {
                $notesArray = explode("\n", $rdv['notes']);
                $notesArray = array_filter($notesArray, function($n) { return trim($n) !== ''; });
                $notesArray = array_values($notesArray);
                
                if(isset($notesArray[$note_index])) {
                    $notesArray[$note_index] = date('Y-m-d H:i') . ' - ' . $nouvelle_note;
                    $nouvellesNotes = "\n" . implode("\n", $notesArray);
                    
                    $stmt = $pdo->prepare("UPDATE rendez_vous SET notes = ? WHERE id = ?");
                    $stmt->execute([$nouvellesNotes, $rdv_id]);
                    
                    $_SESSION['message'] = "✅ Note modifiée avec succès !";
                } else {
                    $_SESSION['error'] = "❌ Note introuvable";
                }
            } else {
                $_SESSION['error'] = "❌ Aucune note trouvée";
            }
        } catch(PDOException $e) {
            $_SESSION['error'] = "❌ Erreur : " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "❌ Veuillez saisir une note valide";
    }
    
    header('Location: index.php');
    exit();
} else {
    header('Location: index.php');
    exit();
}
?>