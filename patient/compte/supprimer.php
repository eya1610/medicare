<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header('Location: ../../login.php');
    exit();
}

require_once '../../config/database.php';
$pdo = getPDO();
$user_id = $_SESSION['user_id'];

// Supprimer les rendez-vous liés
$pdo->prepare("DELETE FROM rendez_vous WHERE patient_id = ?")->execute([$user_id]);
// Supprimer le compte
$pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);

session_destroy();
header('Location: ../../login.php?compte_supprime=1');
exit();
?>