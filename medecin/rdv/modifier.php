<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'medecin') {
    header('Location: ../../login.php');
    exit();
}

require_once '../../config/database.php';
require_once '../../includes/functions.php';
$pdo = getPDO();
$id = $_GET['id'] ?? 0;
$medecin_id = $_SESSION['user_id'];

// Récupérer le rendez-vous
$stmt = $pdo->prepare("
    SELECT r.*, 
           u.nom as patient_nom, u.prenom as patient_prenom
    FROM rendez_vous r
    JOIN users u ON r.patient_id = u.id
    WHERE r.id = ? AND r.medecin_id = ?
");
$stmt->execute([$id, $medecin_id]);
$rdv = $stmt->fetch();

if(!$rdv) {
    header('Location: index.php');
    exit();
}

// Traitement du formulaire
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date_rdv = $_POST['date_rdv'];
    $heure_rdv = $_POST['heure_rdv'];
    $motif = $_POST['motif'];
    $statut = $_POST['statut'];
    $notes = trim($_POST['notes'] ?? '');
    
    $sql = "UPDATE rendez_vous SET date_rdv = :date_rdv, heure_rdv = :heure_rdv, motif = :motif, statut = :statut, notes = :notes WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':date_rdv' => $date_rdv,
        ':heure_rdv' => $heure_rdv,
        ':motif' => $motif,
        ':statut' => $statut,
        ':notes' => $notes,
        ':id' => $id
    ]);
    
    $_SESSION['message'] = "✅ Rendez-vous modifié avec succès !";
    header('Location: index.php');
    exit();
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <h1>✏️ Modifier le rendez-vous</h1>
    
    <div class="form-card">
        <form method="POST" id="rdvForm">
            <div class="form-row">
                <div class="form-group">
                    <label>👤 Patient</label>
                    <input type="text" value="<?= htmlspecialchars($rdv['patient_prenom'] . ' ' . $rdv['patient_nom']) ?>" disabled>
                </div>
                <div class="form-group">
                    <label>📅 Date</label>
                    <input type="date" name="date_rdv" value="<?= $rdv['date_rdv'] ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>⏰ Heure</label>
                    <input type="time" name="heure_rdv" value="<?= $rdv['heure_rdv'] ?>" required>
                </div>
                <div class="form-group">
                    <label>📌 Statut</label>
                    <select name="statut">
                        <option value="en_attente" <?= $rdv['statut'] == 'en_attente' ? 'selected' : '' ?>>En attente</option>
                        <option value="confirme" <?= $rdv['statut'] == 'confirme' ? 'selected' : '' ?>>Confirmé</option>
                        <option value="termine" <?= $rdv['statut'] == 'termine' ? 'selected' : '' ?>>Terminé</option>
                        <option value="annule" <?= $rdv['statut'] == 'annule' ? 'selected' : '' ?>>Annulé</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>📝 Motif</label>
                <input type="text" name="motif" value="<?= htmlspecialchars($rdv['motif']) ?>" required>
            </div>
            
            <!-- SECTION NOTES -->
            <div class="notes-section">
                <div class="notes-header">
                    <h3>📋 Notes médicales</h3>
                    <button type="button" onclick="ajouterNoteModifier()" class="btn btn-sm" style="background:var(--pastel-vert);color:#2D6A4F;">
                        ➕ Ajouter une note
                    </button>
                </div>
                
                <!-- Liste des notes existantes -->
                <div class="notes-list" id="notesListModifier">
                    <?php 
                    $notesArray = [];
                    if(!empty($rdv['notes'])) {
                        $notesArray = explode("\n", $rdv['notes']);
                        $notesArray = array_filter($notesArray, function($n) { return trim($n) !== ''; });
                        $notesArray = array_values($notesArray);
                    }
                    ?>
                    
                    <?php if(count($notesArray) > 0): ?>
                        <?php foreach($notesArray as $index => $note): ?>
                            <?php 
                            $noteContent = $note;
                            $noteDate = '';
                            if(preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}) - (.*)/', $note, $dateMatch)) {
                                $noteDate = $dateMatch[1];
                                $noteContent = $dateMatch[2];
                            }
                            ?>
                            <div class="note-item-edit" data-index="<?= $index ?>">
                                <div class="note-content">
                                    <?php if($noteDate): ?>
                                        <span class="note-date-badge">📅 <?= $noteDate ?></span>
                                    <?php endif; ?>
                                    <span class="note-text"><?= htmlspecialchars($noteContent) ?></span>
                                </div>
                                <div class="note-actions">
                                    <button type="button" onclick="editNoteModifier(<?= $index ?>)" class="btn-sm" style="background:var(--pastel-bleu);color:#2c5a7a;">
                                        ✏️ Modifier
                                    </button>
                                    <button type="button" onclick="deleteNoteModifier(<?= $index ?>)" class="btn-sm" style="background:var(--pastel-peche);color:#E07A5F;">
                                        🗑️ Supprimer
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-notes" id="noNotesMessageModifier">Aucune note médicale enregistrée</div>
                    <?php endif; ?>
                </div>
                
                <!-- Champ caché -->
                <input type="hidden" name="notes" id="notesHiddenModifier" value="<?= htmlspecialchars($rdv['notes'] ?? '') ?>">
                
                <!-- Modal d'ajout de note -->
                <div id="addNoteModalModifier" class="modal" style="display:none;">
                    <div class="modal-content" style="max-width:500px;">
                        <div class="modal-header" style="background:var(--pastel-vert);">
                            <h3>➕ Ajouter une note</h3>
                            <span class="modal-close" onclick="closeAddNoteModalModifier()">&times;</span>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Nouvelle note médicale</label>
                                <textarea id="newNoteTextModifier" rows="4" style="width:100%;padding:12px;border:2px solid var(--border);border-radius:16px;font-size:14px;font-family:inherit;resize:vertical;min-height:80px;"></textarea>
                            </div>
                            <button type="button" onclick="saveNewNoteModifier()" class="btn btn-primary" style="padding:10px 24px;border-radius:40px;border:none;background:var(--accent);color:white;font-weight:500;cursor:pointer;">
                                💾 Enregistrer
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Modal de modification de note -->
                <div id="editNoteModalModifier" class="modal" style="display:none;">
                    <div class="modal-content" style="max-width:500px;">
                        <div class="modal-header" style="background:var(--pastel-bleu);">
                            <h3>✏️ Modifier la note</h3>
                            <span class="modal-close" onclick="closeEditNoteModalModifier()">&times;</span>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="editNoteIndexModifier">
                            <div class="form-group">
                                <label>Note actuelle</label>
                                <div id="editNoteOldModifier" style="background:var(--bg-primary);padding:12px;border-radius:12px;font-size:14px;word-wrap:break-word;white-space:pre-wrap;"></div>
                            </div>
                            <div class="form-group">
                                <label>Nouvelle note</label>
                                <textarea id="editNoteNewModifier" rows="4" style="width:100%;padding:12px;border:2px solid var(--border);border-radius:16px;font-size:14px;font-family:inherit;resize:vertical;min-height:80px;"></textarea>
                            </div>
                            <button type="button" onclick="saveEditedNoteModifier()" class="btn btn-primary" style="padding:10px 24px;border-radius:40px;border:none;background:var(--accent);color:white;font-weight:500;cursor:pointer;">
                                💾 Enregistrer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
                <a href="index.php" class="btn btn-secondary">Retour</a>
            </div>
        </form>
    </div>
</div>

<style>
.form-card {
    background: white;
    border-radius: 24px;
    padding: 24px;
    box-shadow: var(--shadow);
    max-width: 700px;
}
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.form-group {
    margin-bottom: 16px;
}
.form-group label {
    display: block;
    font-weight: 500;
    margin-bottom: 6px;
    font-size: 13px;
    color: var(--text-light);
}
.form-group input, .form-group select {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--border);
    border-radius: 16px;
    font-size: 14px;
}
.form-group input:focus, .form-group select:focus {
    outline: none;
    border-color: var(--accent);
}
.form-group input[disabled] {
    background: var(--bg-primary);
    color: var(--text);
}
.notes-section {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px solid var(--border);
}
.notes-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}
.notes-header h3 {
    color: var(--accent);
    font-size: 16px;
}
.btn-sm {
    padding: 6px 14px;
    border-radius: 30px;
    border: none;
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
}

/* === CORRECTION : RETOUR A LA LIGNE POUR LES NOTES === */
.notes-list {
    max-height: 300px;
    overflow-y: auto;
    background: var(--bg-primary);
    border-radius: 16px;
    padding: 12px;
    margin-bottom: 12px;
}
.notes-list::-webkit-scrollbar {
    width: 6px;
}
.notes-list::-webkit-scrollbar-track {
    background: var(--bg-primary);
    border-radius: 10px;
}
.notes-list::-webkit-scrollbar-thumb {
    background: var(--accent);
    border-radius: 10px;
}

.note-item-edit {
    background: white;
    padding: 12px 16px;
    border-radius: 12px;
    margin-bottom: 8px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 8px;
    border-left: 3px solid var(--accent);
}
.note-item-edit .note-content {
    flex: 1;
    min-width: 0;
    word-wrap: break-word;
    white-space: normal;
    overflow-wrap: break-word;
    word-break: break-word;
    max-width: 100%;
}
.note-item-edit .note-date-badge {
    font-size: 11px;
    color: var(--text-light);
    background: var(--bg-primary);
    padding: 2px 10px;
    border-radius: 20px;
    display: inline-block;
    margin-right: 8px;
}
.note-item-edit .note-text {
    font-size: 14px;
    color: var(--text);
    word-wrap: break-word;
    white-space: normal;
    overflow-wrap: break-word;
    word-break: break-word;
    display: inline;
}
.note-item-edit .note-actions {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
    align-items: center;
}
.note-item-edit .note-actions .btn-sm {
    padding: 4px 12px;
    font-size: 11px;
}
/* === FIN CORRECTION === */

.no-notes {
    text-align: center;
    padding: 20px;
    color: var(--text-light);
    font-size: 13px;
}
.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 20px;
}
.btn-secondary {
    background: #EBEBEB;
    padding: 12px 24px;
    border-radius: 40px;
    border: none;
    cursor: pointer;
    font-weight: 500;
    text-decoration: none;
    color: var(--text);
}
.btn-primary {
    padding: 12px 24px;
    border-radius: 40px;
    border: none;
    background: var(--accent);
    color: white;
    font-weight: 500;
    cursor: pointer;
}
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}
.modal-content {
    background: white;
    border-radius: 28px;
    max-width: 500px;
    width: 90%;
}
.modal-header {
    padding: 16px 24px;
    border-radius: 28px 28px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.modal-header h3 {
    color: var(--accent);
}
.modal-close {
    font-size: 28px;
    cursor: pointer;
    color: var(--accent);
}
.modal-body {
    padding: 20px 24px;
}
@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    .note-item-edit {
        flex-direction: column;
        align-items: flex-start;
    }
    .note-item-edit .note-actions {
        width: 100%;
        justify-content: flex-end;
    }
}
</style>

<script>
// Stockage des notes pour la page modifier
let notesArrayModifier = <?php 
$currentNotes = [];
if(!empty($rdv['notes'])) {
    $currentNotes = explode("\n", $rdv['notes']);
    $currentNotes = array_filter($currentNotes, function($n) { return trim($n) !== ''; });
    $currentNotes = array_values($currentNotes);
}
echo json_encode($currentNotes);
?>;

function updateNotesHiddenModifier() {
    document.getElementById('notesHiddenModifier').value = notesArrayModifier.join('\n');
    renderNotesListModifier();
}

function renderNotesListModifier() {
    const container = document.getElementById('notesListModifier');
    if(notesArrayModifier.length === 0) {
        container.innerHTML = '<div class="no-notes" id="noNotesMessageModifier">Aucune note médicale enregistrée</div>';
        return;
    }
    
    let html = '';
    notesArrayModifier.forEach((note, index) => {
        let noteContent = note;
        let noteDate = '';
        const dateMatch = note.match(/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}) - (.*)/);
        if(dateMatch) {
            noteDate = dateMatch[1];
            noteContent = dateMatch[2];
        }
        html += `
            <div class="note-item-edit" data-index="${index}">
                <div class="note-content">
                    ${noteDate ? `<span class="note-date-badge">📅 ${noteDate}</span>` : ''}
                    <span class="note-text">${noteContent.replace(/</g,'&lt;').replace(/>/g,'&gt;')}</span>
                </div>
                <div class="note-actions">
                    <button type="button" onclick="editNoteModifier(${index})" class="btn-sm" style="background:var(--pastel-bleu);color:#2c5a7a;">✏️ Modifier</button>
                    <button type="button" onclick="deleteNoteModifier(${index})" class="btn-sm" style="background:var(--pastel-peche);color:#E07A5F;">🗑️ Supprimer</button>
                </div>
            </div>
        `;
    });
    container.innerHTML = html;
}

function ajouterNoteModifier() {
    document.getElementById('addNoteModalModifier').style.display = 'flex';
}

function closeAddNoteModalModifier() {
    document.getElementById('addNoteModalModifier').style.display = 'none';
    document.getElementById('newNoteTextModifier').value = '';
}

function saveNewNoteModifier() {
    const noteText = document.getElementById('newNoteTextModifier').value.trim();
    if(!noteText) {
        alert('Veuillez saisir une note');
        return;
    }
    
    const now = new Date();
    const dateStr = now.getFullYear() + '-' + 
                   String(now.getMonth()+1).padStart(2,'0') + '-' + 
                   String(now.getDate()).padStart(2,'0') + ' ' + 
                   String(now.getHours()).padStart(2,'0') + ':' + 
                   String(now.getMinutes()).padStart(2,'0');
    
    notesArrayModifier.push(dateStr + ' - ' + noteText);
    updateNotesHiddenModifier();
    closeAddNoteModalModifier();
    alert('✅ Note ajoutée ! N\'oubliez pas d\'enregistrer le rendez-vous.');
}

function editNoteModifier(index) {
    const note = notesArrayModifier[index];
    let noteContent = note;
    const dateMatch = note.match(/^\d{4}-\d{2}-\d{2} \d{2}:\d{2} - (.*)/);
    if(dateMatch) {
        noteContent = dateMatch[1];
    }
    document.getElementById('editNoteIndexModifier').value = index;
    document.getElementById('editNoteOldModifier').textContent = noteContent;
    document.getElementById('editNoteNewModifier').value = noteContent;
    document.getElementById('editNoteModalModifier').style.display = 'flex';
}

function closeEditNoteModalModifier() {
    document.getElementById('editNoteModalModifier').style.display = 'none';
}

function saveEditedNoteModifier() {
    const index = parseInt(document.getElementById('editNoteIndexModifier').value);
    const newNote = document.getElementById('editNoteNewModifier').value.trim();
    if(!newNote) {
        alert('Veuillez saisir une note');
        return;
    }
    
    const now = new Date();
    const dateStr = now.getFullYear() + '-' + 
                   String(now.getMonth()+1).padStart(2,'0') + '-' + 
                   String(now.getDate()).padStart(2,'0') + ' ' + 
                   String(now.getHours()).padStart(2,'0') + ':' + 
                   String(now.getMinutes()).padStart(2,'0');
    
    notesArrayModifier[index] = dateStr + ' - ' + newNote;
    updateNotesHiddenModifier();
    closeEditNoteModalModifier();
    alert('✅ Note modifiée ! N\'oubliez pas d\'enregistrer le rendez-vous.');
}

function deleteNoteModifier(index) {
    if(!confirm('Supprimer cette note ?')) return;
    notesArrayModifier.splice(index, 1);
    updateNotesHiddenModifier();
}

// Fermer les modaux
window.onclick = function(event) {
    const addModal = document.getElementById('addNoteModalModifier');
    const editModal = document.getElementById('editNoteModalModifier');
    if (event.target === addModal) { closeAddNoteModalModifier(); }
    if (event.target === editModal) { closeEditNoteModalModifier(); }
}

// Mettre à jour le champ caché avant l'envoi
document.getElementById('rdvForm').addEventListener('submit', function() {
    document.getElementById('notesHiddenModifier').value = notesArrayModifier.join('\n');
});

renderNotesListModifier();
</script>

<?php include '../../includes/footer.php'; ?>