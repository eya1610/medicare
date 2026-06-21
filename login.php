<?php
session_start();

// Si déjà connecté, rediriger vers le dashboard correspondant
if(isset($_SESSION['user_id'])) {
    switch($_SESSION['role']) {
        case 'admin': header('Location: admin/dashboard.php'); break;
        case 'medecin': header('Location: medecin/dashboard.php'); break;
        case 'patient': header('Location: patient/dashboard.php'); break;
    }
    exit();
}

require_once 'config/database.php';
$pdo = getPDO();
$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if(!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['prenom'] = $user['prenom'];
            
            switch($user['role']) {
                case 'admin': header('Location: admin/dashboard.php'); break;
                case 'medecin': header('Location: medecin/dashboard.php'); break;
                case 'patient': header('Location: patient/dashboard.php'); break;
            }
            exit();
        } else {
            $error = "Identifiants incorrects";
        }
    } else {
        $error = "Veuillez remplir tous les champs";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - MediCare</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">🏥</div>
                <h1>MediCare</h1>
                <p>Gestion des rendez-vous médicaux</p>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Nom d'utilisateur</label>
                    <input type="text" name="username" placeholder="admin / dupont / lambert" required>
                </div>
                <div class="form-group">
                    <label>Mot de passe</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-primary">🔐 Se connecter</button>
            </form>
            
            <div class="login-footer">
                <p>Comptes de test :</p>
                <small>admin / 123456 | dupont / 123456 | lambert / 123456</small>
            </div>
        </div>
    </div>

    <style>
    .login-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #E4DFF7 0%, #C5E0F7 100%);
        padding: 20px;
    }
    .login-card {
        background: white;
        padding: 48px 40px;
        border-radius: 32px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.08);
        width: 100%;
        max-width: 420px;
        transition: transform 0.3s ease;
    }
    .login-card:hover { transform: translateY(-4px); }
    .login-header { text-align: center; margin-bottom: 32px; }
    .login-logo { font-size: 48px; margin-bottom: 8px; }
    .login-header h1 {
        color: #4A4A4A;
        font-size: 28px;
        font-weight: 700;
        letter-spacing: -0.5px;
    }
    .login-header p { color: #8A8A8A; font-size: 14px; margin-top: 4px; }
    .form-group { margin-bottom: 20px; }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        font-size: 14px;
        color: #4A4A4A;
    }
    .form-group input {
        width: 100%;
        padding: 14px 16px;
        border: 2px solid #EBEBEB;
        border-radius: 16px;
        font-size: 15px;
        transition: all 0.3s ease;
        background: #FDFBF7;
    }
    .form-group input:focus {
        outline: none;
        border-color: #7B8FA1;
        box-shadow: 0 0 0 4px rgba(123, 143, 161, 0.1);
    }
    .btn-primary {
        width: 100%;
        padding: 14px;
        border-radius: 40px;
        border: none;
        background: #7B8FA1;
        color: white;
        font-weight: 600;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .btn-primary:hover {
        background: #6B7F91;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(123, 143, 161, 0.25);
    }
    .login-footer {
        text-align: center;
        margin-top: 24px;
        font-size: 12px;
        color: #8A8A8A;
    }
    .login-footer p { margin-bottom: 4px; font-weight: 500; }
    .alert {
        padding: 12px 16px;
        border-radius: 16px;
        margin-bottom: 20px;
        font-size: 14px;
    }
    .alert-error {
        background: #FADADD;
        color: #E07A5F;
        border-left: 4px solid #E07A5F;
    }
    </style>
</body>
</html>