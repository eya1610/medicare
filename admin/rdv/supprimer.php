<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../../login.php');
    exit();
}

require_once '../../config/database.php';
$pdo = getPDO();
$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("DELETE FROM rendez_vous WHERE id = ?");
$stmt->execute([$id]);

header('Location: index.php?supprime=1');
exit();
?>