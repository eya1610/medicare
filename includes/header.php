<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCare - Gestion des RDV</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/clinic_rdv/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<script>
// === BLOCAGE DES RACCOURCIS CLAVIER ===
document.addEventListener('keydown', function(e) {
    // F12
    if(e.key === 'F12') {
        e.preventDefault();
        return false;
    }
    // Ctrl+Shift+I (Inspecteur)
    if(e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'i')) {
        e.preventDefault();
        return false;
    }
    // Ctrl+U (Code source)
    if(e.ctrlKey && (e.key === 'u' || e.key === 'U')) {
        e.preventDefault();
        return false;
    }
    // Ctrl+Shift+J (Console)
    if(e.ctrlKey && e.shiftKey && (e.key === 'J' || e.key === 'j')) {
        e.preventDefault();
        return false;
    }
});

// Empêcher le clic droit
document.addEventListener('contextmenu', function(e) {
    e.preventDefault();
});

// Empêcher le drag & drop
document.addEventListener('dragstart', function(e) {
    e.preventDefault();
});
</script>