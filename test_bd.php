<?php
// test_bd.php - Test de connexion PDO

require_once 'config/database.php';

echo "<h1>🧪 Test de connexion à la base</h1>";

try {
    $pdo = getPDO();
    echo "<p style='color:green'>✅ Connexion PDO réussie !</p>";
    
    $stmt = $pdo->query("SELECT role, COUNT(*) as total FROM users GROUP BY role");
    echo "<h2>📊 Utilisateurs par rôle :</h2>";
    echo "<ul>";
    while($row = $stmt->fetch()) {
        echo "<li><strong>" . $row['role'] . "</strong> : " . $row['total'] . " compte(s)</li>";
    }
    echo "</ul>";
    
    echo "<hr>";
    echo "<p>🔑 <strong>Comptes de test :</strong></p>";
    echo "<ul>";
    echo "<li>admin / 123456</li>";
    echo "<li>dupont / 123456</li>";
    echo "<li>martin / 123456</li>";
    echo "<li>bernard / 123456</li>";
    echo "<li>lambert / 123456</li>";
    echo "<li>dubois / 123456</li>";
    echo "<li>petit / 123456</li>";
    echo "</ul>";
    
    echo "<p><a href='login.php' style='display:inline-block; background:#7B8FA1; color:white; padding:10px 20px; border-radius:25px; text-decoration:none;'>🔐 Aller à la connexion</a></p>";
    
} catch(Exception $e) {
    echo "<p style='color:red'>❌ Erreur : " . $e->getMessage() . "</p>";
}
?>