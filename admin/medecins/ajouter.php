<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../../login.php');
    exit();
}

require_once '../../config/database.php';
$pdo = getPDO();
$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $specialite = trim($_POST['specialite']);
    $password = password_hash('123456', PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role, nom, prenom, email, telephone, specialite) VALUES (?, ?, 'medecin', ?, ?, ?, ?, ?)");
        $stmt->execute([$username, $password, $nom, $prenom, $email, $telephone, $specialite]);
        $success = "Médecin ajouté ! Mot de passe : 123456";
    } catch(PDOException $e) {
        $error = "Erreur : " . $e->getMessage();
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <h1>➕ Ajouter un médecin</h1>
    
    <?php if($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
    <?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    
    <form method="POST" class="table-container" style="padding: 24px;">
        <div class="form-group">
            <label>Nom d'utilisateur *</label>
            <input type="text" name="username" required>
        </div>
        <div class="form-group">
            <label>Nom *</label>
            <input type="text" name="nom" required>
        </div>
        <div class="form-group">
            <label>Prénom *</label>
            <input type="text" name="prenom" required>
        </div>
        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Téléphone</label>
            <input type="text" name="telephone">
        </div>
        <div class="form-group">
            <label>Spécialité</label>
            <input type="text" name="specialite" placeholder="Ex: Cardiologue, Pédiatre...">
        </div>
        <button type="submit" class="btn btn-primary">✅ Créer le médecin</button>
        <a href="index.php" class="btn" style="background:#EBEBEB;">Retour</a>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>