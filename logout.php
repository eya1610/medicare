<?php
session_start();

// Supprimer le nom de fenêtre
echo "<script>window.name = '';</script>";

// Détruire la session
session_destroy();

// Rediriger vers login
header('Location: login.php');
exit();
?>