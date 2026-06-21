<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header('Location: ../login.php');
    exit();
}

require_once '../../config/database.php';
$pdo = getPDO();
$patient_id = $_SESSION['user_id'];
$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("DELETE FROM avis WHERE id = ? AND patient_id = ? AND statut = 'en_attente'");
$stmt->execute([$id, $patient_id]);

header('Location: index.php?supprime=1');
exit();
?>