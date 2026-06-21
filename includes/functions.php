<?php
/**
 * Génère un champ input time avec les heures limitées de 8h à 20h
 * @param string $name Nom du champ
 * @param string $value Valeur sélectionnée
 * @param string $label Label du champ
 * @param bool $required Champ obligatoire ou non
 * @return string HTML du champ
 */
function heureInput($name, $value = '', $label = 'Heure', $required = true) {
    $requiredAttr = $required ? 'required' : '';
    $html = '
    <div class="form-group">
        <label>⏰ ' . htmlspecialchars($label) . ' ' . ($required ? '*' : '') . '</label>
        <input type="time" 
               name="' . htmlspecialchars($name) . '" 
               value="' . htmlspecialchars($value) . '" 
               min="08:00" 
               max="20:00" 
               step="900" 
               ' . $requiredAttr . '
               style="width:100%;padding:12px 16px;border:2px solid var(--border);border-radius:16px;font-size:14px;background:var(--bg-primary);">
        <small style="color:var(--text-light);font-size:12px;display:block;margin-top:4px;">
            🕐 De 08:00 à 20:00 (par tranches de 15 min)
        </small>
    </div>';
    return $html;
}
?>