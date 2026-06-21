<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header('Location: ../../login.php');
    exit();
}

require_once '../../config/database.php';
$pdo = getPDO();
$id = $_GET['id'] ?? 0;
$patient_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("UPDATE rendez_vous SET statut = 'annule' WHERE id = ? AND patient_id = ?");
$stmt->execute([$id, $patient_id]);

header('Location: index.php?annule=1');
exit();
?>