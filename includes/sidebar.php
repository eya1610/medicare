<?php
// includes/sidebar.php
if(!isset($_SESSION['role'])) return;

$role = $_SESSION['role'];
?>

<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">🏥</div>
        <div class="brand-text">
            <span class="brand-name">MediCare</span>
            <span class="brand-sub">Gestion médicale</span>
        </div>
    </div>
    
    <div class="sidebar-divider"></div>
    
    <ul class="sidebar-menu">
        <?php if($role == 'admin'): ?>
            <li class="menu-label">Navigation</li>
            <li><a href="/clinic_rdv/admin/dashboard.php" class="menu-item">
                <span class="menu-icon">📊</span>
                <span class="menu-text">Tableau de bord</span>
            </a></li>
            <li><a href="/clinic_rdv/admin/patients/index.php" class="menu-item">
                <span class="menu-icon">👨‍👩‍👧</span>
                <span class="menu-text">Patients</span>
            </a></li>
            <li><a href="/clinic_rdv/admin/medecins/index.php" class="menu-item">
                <span class="menu-icon">👨‍⚕️</span>
                <span class="menu-text">Médecins</span>
            </a></li>
            <li><a href="/clinic_rdv/admin/rdv/index.php" class="menu-item">
                <span class="menu-icon">📅</span>
                <span class="menu-text">Rendez-vous</span>
            </a></li>
            <li class="menu-label">Analyses</li>
            <li><a href="/clinic_rdv/admin/stats.php" class="menu-item">
                <span class="menu-icon">📊</span>
                <span class="menu-text">Statistiques</span>
            </a></li>
            <li><a href="/clinic_rdv/admin/calendar.php" class="menu-item">
                <span class="menu-icon">📆</span>
                <span class="menu-text">Calendrier</span>
            </a></li>
            <li class="menu-label">Gestion</li>
            <li><a href="/clinic_rdv/admin/avis/index.php" class="menu-item">
                <span class="menu-icon">⭐</span>
                <span class="menu-text">Avis</span>
            </a></li>
            <li><a href="/clinic_rdv/admin/reclamations/index.php" class="menu-item">
                <span class="menu-icon">📩</span>
                <span class="menu-text">Réclamations</span>
            </a></li>
        
        <?php elseif($role == 'medecin'): ?>
            <li class="menu-label">Navigation</li>
            <li><a href="/clinic_rdv/medecin/dashboard.php" class="menu-item">
                <span class="menu-icon">📊</span>
                <span class="menu-text">Tableau de bord</span>
            </a></li>
            <li><a href="/clinic_rdv/medecin/rdv/index.php" class="menu-item">
                <span class="menu-icon">📅</span>
                <span class="menu-text">Mes rendez-vous</span>
            </a></li>
            <li class="menu-label">Compte</li>
            <li><a href="/clinic_rdv/medecin/compte/profil.php" class="menu-item">
                <span class="menu-icon">👤</span>
                <span class="menu-text">Mon profil</span>
            </a></li>
        
        <?php elseif($role == 'patient'): ?>
            <li class="menu-label">Navigation</li>
            <li><a href="/clinic_rdv/patient/dashboard.php" class="menu-item">
                <span class="menu-icon">📊</span>
                <span class="menu-text">Tableau de bord</span>
            </a></li>
            <li><a href="/clinic_rdv/patient/rdv/index.php" class="menu-item">
                <span class="menu-icon">📅</span>
                <span class="menu-text">Mes rendez-vous</span>
            </a></li>
            <li><a href="/clinic_rdv/patient/rdv/ajouter.php" class="menu-item">
                <span class="menu-icon">➕</span>
                <span class="menu-text">Prendre RDV</span>
            </a></li>
            <li class="menu-label">Mon espace</li>
            <li><a href="/clinic_rdv/patient/avis/index.php" class="menu-item">
                <span class="menu-icon">⭐</span>
                <span class="menu-text">Mes avis</span>
            </a></li>
            <li><a href="/clinic_rdv/patient/reclamations/index.php" class="menu-item">
                <span class="menu-icon">📩</span>
                <span class="menu-text">Mes réclamations</span>
            </a></li>
            <li><a href="/clinic_rdv/patient/compte/profil.php" class="menu-item">
                <span class="menu-icon">👤</span>
                <span class="menu-text">Mon profil</span>
            </a></li>
        <?php endif; ?>
        
        <li class="menu-divider"></li>
        <li><a href="/clinic_rdv/logout.php" class="menu-item logout">
            <span class="menu-icon">🚪</span>
            <span class="menu-text">Déconnexion</span>
        </a></li>
    </ul>
</aside>

<style>
/* === SIDEBAR ULTRA PRO === */
.sidebar {
    width: 280px;
    background: linear-gradient(180deg, #FFFFFF 0%, #F8F9FC 100%);
    min-height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    box-shadow: 2px 0 20px rgba(0,0,0,0.04);
    padding: 28px 20px;
    z-index: 100;
    display: flex;
    flex-direction: column;
    border-right: 1px solid rgba(123, 143, 161, 0.08);
    overflow-y: auto;
}

/* ===== BRAND / TITRE MODERNE ===== */
.sidebar-brand {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 0 4px 20px 4px;
    margin-bottom: 8px;
}
.brand-icon {
    font-size: 32px;
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #7B8FA1, #A8B9C8);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    box-shadow: 0 4px 12px rgba(123, 143, 161, 0.25);
    flex-shrink: 0;
}
.brand-text {
    display: flex;
    flex-direction: column;
    line-height: 1.2;
}
.brand-name {
    font-size: 20px;
    font-weight: 700;
    color: #4A4A4A;
    letter-spacing: -0.3px;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}
.brand-sub {
    font-size: 11px;
    font-weight: 500;
    color: #8A8A8A;
    letter-spacing: 0.3px;
    text-transform: uppercase;
}

/* ===== DIVIDER ===== */
.sidebar-divider {
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(123, 143, 161, 0.15), transparent);
    margin-bottom: 20px;
}

/* ===== MENU ===== */
.sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 0;
    flex: 1;
}
.sidebar-menu li {
    margin-bottom: 2px;
}
.sidebar-menu .menu-label {
    font-size: 10px;
    font-weight: 600;
    color: #B0B0B0;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    padding: 12px 12px 6px 12px;
}
.sidebar-menu .menu-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 10px 14px;
    border-radius: 12px;
    text-decoration: none;
    color: #5A5A5A;
    transition: all 0.2s ease;
    font-size: 14px;
    font-weight: 500;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    position: relative;
}
.sidebar-menu .menu-item:hover {
    background: rgba(123, 143, 161, 0.06);
    color: #4A4A4A;
}
.sidebar-menu .menu-item.active {
    background: rgba(123, 143, 161, 0.10);
    color: #7B8FA1;
}
.sidebar-menu .menu-item.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 24px;
    background: #7B8FA1;
    border-radius: 0 4px 4px 0;
}
.sidebar-menu .menu-icon {
    font-size: 18px;
    width: 28px;
    text-align: center;
    flex-shrink: 0;
    opacity: 0.8;
}
.sidebar-menu .menu-text {
    flex: 1;
}
.sidebar-menu .menu-divider {
    height: 1px;
    background: rgba(123, 143, 161, 0.08);
    margin: 12px 12px;
}
.sidebar-menu .logout {
    color: #E07A5F;
}
.sidebar-menu .logout:hover {
    background: rgba(224, 122, 95, 0.08);
    color: #c05a3f;
}
.sidebar-menu .logout .menu-icon {
    opacity: 1;
}

/* ===== SCROLLBAR ===== */
.sidebar::-webkit-scrollbar {
    width: 4px;
}
.sidebar::-webkit-scrollbar-track {
    background: transparent;
}
.sidebar::-webkit-scrollbar-thumb {
    background: rgba(123, 143, 161, 0.2);
    border-radius: 10px;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        width: 280px;
        position: fixed;
        z-index: 1000;
        transition: transform 0.3s ease;
    }
    .sidebar.open {
        transform: translateX(0);
    }
    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.3);
        z-index: 999;
    }
    .sidebar-overlay.active {
        display: block;
    }
}
</style>

<!-- Script pour mobile toggle -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Créer l'overlay pour mobile
    const overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    document.body.appendChild(overlay);
    
    // Bouton toggle (à ajouter dans header)
    const toggleBtn = document.createElement('button');
    toggleBtn.className = 'sidebar-toggle';
    toggleBtn.innerHTML = '☰';
    toggleBtn.style.cssText = 'display:none;position:fixed;top:16px;left:16px;z-index:1001;background:white;border:none;border-radius:12px;padding:10px 14px;font-size:20px;box-shadow:0 2px 12px rgba(0,0,0,0.08);cursor:pointer;';
    document.body.prepend(toggleBtn);
    
    if(window.innerWidth <= 768) {
        toggleBtn.style.display = 'block';
    }
    
    toggleBtn.addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('open');
        overlay.classList.toggle('active');
    });
    
    overlay.addEventListener('click', function() {
        document.querySelector('.sidebar').classList.remove('open');
        overlay.classList.remove('active');
    });
    
    window.addEventListener('resize', function() {
        if(window.innerWidth <= 768) {
            toggleBtn.style.display = 'block';
        } else {
            toggleBtn.style.display = 'none';
            document.querySelector('.sidebar').classList.remove('open');
            overlay.classList.remove('active');
        }
    });
});
</script>