<?php
// install.php - Réinitialisation complète

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'clinic_rdv';

try {
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Supprimer et recréer la base
    $pdo->exec("DROP DATABASE IF EXISTS $dbname");
    $pdo->exec("CREATE DATABASE $dbname");
    $pdo->exec("USE $dbname");
    
    // Table users
    $pdo->exec("CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'medecin', 'patient') NOT NULL,
        nom VARCHAR(50) NOT NULL,
        prenom VARCHAR(50) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        telephone VARCHAR(20) NULL,
        specialite VARCHAR(100) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Table rendez_vous
    $pdo->exec("CREATE TABLE rendez_vous (
        id INT AUTO_INCREMENT PRIMARY KEY,
        patient_id INT NOT NULL,
        medecin_id INT NOT NULL,
        date_rdv DATE NOT NULL,
        heure_rdv TIME NOT NULL,
        motif VARCHAR(255) NOT NULL,
        statut ENUM('en_attente', 'confirme', 'annule', 'termine') DEFAULT 'en_attente',
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (medecin_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    
    // Insertion des utilisateurs avec mot de passe "123456"
    $passwordHash = password_hash('123456', PASSWORD_DEFAULT);
    
    // Admin
    $pdo->prepare("INSERT INTO users (username, password, role, nom, prenom, email, telephone) VALUES (?, ?, 'admin', 'Administrateur', 'Système', 'admin@clinique.com', '0612345678')")
        ->execute(['admin', $passwordHash]);
    
    // Médecins
    $pdo->prepare("INSERT INTO users (username, password, role, nom, prenom, email, telephone, specialite) VALUES (?, ?, 'medecin', 'Dupont', 'Jean', 'jean.dupont@clinique.com', '0623456789', 'Cardiologue')")
        ->execute(['dupont', $passwordHash]);
    $pdo->prepare("INSERT INTO users (username, password, role, nom, prenom, email, telephone, specialite) VALUES (?, ?, 'medecin', 'Martin', 'Sophie', 'sophie.martin@clinique.com', '0634567890', 'Médecine générale')")
        ->execute(['martin', $passwordHash]);
    $pdo->prepare("INSERT INTO users (username, password, role, nom, prenom, email, telephone, specialite) VALUES (?, ?, 'medecin', 'Bernard', 'Pierre', 'pierre.bernard@clinique.com', '0645678901', 'Pédiatre')")
        ->execute(['bernard', $passwordHash]);
    
    // Patients
    $pdo->prepare("INSERT INTO users (username, password, role, nom, prenom, email, telephone) VALUES (?, ?, 'patient', 'Lambert', 'Sophie', 'sophie.lambert@email.com', '0656789012')")
        ->execute(['lambert', $passwordHash]);
    $pdo->prepare("INSERT INTO users (username, password, role, nom, prenom, email, telephone) VALUES (?, ?, 'patient', 'Dubois', 'Thomas', 'thomas.dubois@email.com', '0667890123')")
        ->execute(['dubois', $passwordHash]);
    $pdo->prepare("INSERT INTO users (username, password, role, nom, prenom, email, telephone) VALUES (?, ?, 'patient', 'Petit', 'Marie', 'marie.petit@email.com', '0678901234')")
        ->execute(['petit', $passwordHash]);
    
    // Rendez-vous de test
    $pdo->prepare("INSERT INTO rendez_vous (patient_id, medecin_id, date_rdv, heure_rdv, motif, statut) VALUES (4, 2, CURDATE(), '09:30:00', 'Consultation cardiologie', 'confirme')")->execute();
    $pdo->prepare("INSERT INTO rendez_vous (patient_id, medecin_id, date_rdv, heure_rdv, motif, statut) VALUES (5, 3, CURDATE(), '11:00:00', 'Vaccination', 'confirme')")->execute();
    $pdo->prepare("INSERT INTO rendez_vous (patient_id, medecin_id, date_rdv, heure_rdv, motif, statut) VALUES (6, 2, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '10:00:00', 'Contrôle annuel', 'en_attente')")->execute();
    
    echo "<h1 style='color:green'>✅ Installation réussie !</h1>";
    echo "<h2>🔑 Identifiants de connexion :</h2>";
    echo "<ul>";
    echo "<li><strong>admin</strong> / 123456 (Admin)</li>";
    echo "<li><strong>dupont</strong> / 123456 (Médecin)</li>";
    echo "<li><strong>martin</strong> / 123456 (Médecin)</li>";
    echo "<li><strong>bernard</strong> / 123456 (Médecin)</li>";
    echo "<li><strong>lambert</strong> / 123456 (Patient)</li>";
    echo "<li><strong>dubois</strong> / 123456 (Patient)</li>";
    echo "<li><strong>petit</strong> / 123456 (Patient)</li>";
    echo "</ul>";
    echo "<p><a href='login.php' style='background:#7B8FA1; color:white; padding:10px 20px; border-radius:25px; text-decoration:none;'>🔐 Aller à la connexion</a></p>";
    
} catch(PDOException $e) {
    echo "<p style='color:red'>❌ Erreur : " . $e->getMessage() . "</p>";
}
?>