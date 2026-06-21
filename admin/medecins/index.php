<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../../login.php');
    exit();
}

require_once '../../config/database.php';
$pdo = getPDO();

// Récupérer le terme de recherche
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Requête avec recherche
if(!empty($search)) {
    $stmt = $pdo->prepare("
        SELECT * FROM users 
        WHERE role = 'medecin' 
        AND (nom LIKE ? OR prenom LIKE ? OR specialite LIKE ?)
        ORDER BY nom
    ");
    $stmt->execute(["%$search%", "%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM users WHERE role = 'medecin' ORDER BY nom");
}
$medecins = $stmt->fetchAll();

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <div>
            <h1>👨‍⚕️ Gestion des Médecins</h1>
        </div>
        <div class="header-actions">
            <a href="ajouter.php" class="btn btn-primary">➕ Nouveau médecin</a>
        </div>
    </div>
    
    <!-- Barre de recherche -->
    <div class="search-card">
        <form method="GET" class="search-form">
            <div class="search-input-wrapper">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="🔍 Rechercher par nom, prénom ou spécialité..." 
                    value="<?= htmlspecialchars($search) ?>"
                >
            </div>
            <div class="search-buttons">
                <button type="submit" class="btn btn-primary">
                    🔍 Rechercher
                </button>
                <?php if(!empty($search)): ?>
                    <a href="index.php" class="btn btn-secondary">
                        ↺ Réinitialiser
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <!-- Message résultat de recherche -->
    <?php if(!empty($search)): ?>
        <div class="search-info">
            <?php if(count($medecins) > 0): ?>
                ✅ <strong><?= count($medecins) ?></strong> médecin(s) trouvé(s) pour "<strong><?= htmlspecialchars($search) ?></strong>"
            <?php else: ?>
                ❌ Aucun médecin trouvé pour "<strong><?= htmlspecialchars($search) ?></strong>"
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Tableau des médecins -->
    <div class="table-container">
        <div class="table-header-info">
            📋 Liste des médecins (<?= count($medecins) ?>)
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Spécialité</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($medecins) > 0): ?>
                    <?php foreach($medecins as $m): ?>
                    <tr>
                        <td><?= $m['id'] ?></td>
                        <td><strong><?= htmlspecialchars($m['nom']) ?></strong></td>
                        <td><?= htmlspecialchars($m['prenom']) ?></td>
                        <td>
                            <span class="specialite-badge"><?= htmlspecialchars($m['specialite'] ?? 'Généraliste') ?></span>
                        </td>
                        <td><?= htmlspecialchars($m['email']) ?></td>
                        <td class="actions">
                            <a href="modifier.php?id=<?= $m['id'] ?>" class="action-btn action-edit" title="Modifier">
                                ✏️ Modifier
                            </a>
                            <a href="supprimer.php?id=<?= $m['id'] ?>" class="action-btn action-delete" title="Supprimer" onclick="return confirm('Supprimer définitivement ce médecin ?')">
                                🗑️ Supprimer
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="empty-result">
                            <div class="empty-icon">📭</div>
                            <div>Aucun médecin trouvé</div>
                            <a href="ajouter.php" class="btn btn-primary" style="margin-top: 16px;">➕ Ajouter un médecin</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
/* Styles spécifiques pour la page médecins */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 16px;
}
.page-header h1 {
    color: var(--accent);
    font-size: 28px;
    margin-bottom: 6px;
}
.header-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn-primary {
    background: var(--accent);
    color: white;
    padding: 12px 24px;
    border-radius: 40px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.btn-primary:hover {
    background: #6B7F91;
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}

.search-card {
    background: white;
    border-radius: 24px;
    padding: 20px 24px;
    margin-bottom: 24px;
    box-shadow: var(--shadow);
}
.search-form {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
}
.search-input-wrapper {
    flex: 3;
    min-width: 250px;
}
.search-input-wrapper input {
    width: 100%;
    padding: 14px 18px;
    border: 2px solid var(--border);
    border-radius: 20px;
    font-size: 14px;
    transition: all 0.3s;
    background: var(--bg-primary);
}
.search-input-wrapper input:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(123, 143, 161, 0.1);
}
.search-buttons {
    display: flex;
    gap: 10px;
}
.btn-secondary {
    background: #EBEBEB;
    color: var(--text);
}
.btn-secondary:hover {
    background: #DEDEDE;
}

.search-info {
    background: var(--pastel-bleu);
    padding: 12px 20px;
    border-radius: 16px;
    margin-bottom: 20px;
    font-size: 14px;
    color: #2c5a7a;
    border-left: 4px solid var(--accent);
}

.table-container {
    background: white;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: var(--shadow);
}
.table-header-info {
    padding: 14px 24px;
    background: var(--bg-primary);
    font-size: 13px;
    color: var(--text-light);
    border-bottom: 1px solid var(--border);
}
table {
    width: 100%;
    border-collapse: collapse;
}
th {
    text-align: left;
    padding: 16px 20px;
    background: #F8F9FA;
    font-weight: 600;
    color: var(--accent);
    font-size: 13px;
}
td {
    padding: 14px 20px;
    border-bottom: 1px solid var(--border);
    font-size: 14px;
}
tr:hover {
    background: #FDFBF7;
}
.specialite-badge {
    background: var(--pastel-lavande);
    padding: 4px 12px;
    border-radius: 40px;
    font-size: 12px;
    font-weight: 500;
    color: #5a4a8a;
    display: inline-block;
}
.actions {
    display: flex;
    gap: 10px;
}
.action-btn {
    padding: 6px 14px;
    border-radius: 30px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 500;
    transition: all 0.2s;
}
.action-edit {
    background: var(--pastel-bleu);
    color: #2c5a7a;
}
.action-edit:hover {
    background: #b0d0f0;
}
.action-delete {
    background: var(--pastel-peche);
    color: #c05a3f;
}
.action-delete:hover {
    background: #f0c0a8;
}
.empty-result {
    text-align: center;
    padding: 50px 20px !important;
}
.empty-icon {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.6;
}

@media (max-width: 768px) {
    .search-form {
        flex-direction: column;
    }
    .search-buttons {
        width: 100%;
    }
    .search-buttons button, .search-buttons a {
        flex: 1;
        text-align: center;
    }
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    .header-actions {
        width: 100%;
    }
    .header-actions a {
        flex: 1;
        text-align: center;
    }
    .table-container {
        overflow-x: auto;
    }
    table {
        min-width: 600px;
    }
}
</style>

<?php include '../../includes/footer.php'; ?>