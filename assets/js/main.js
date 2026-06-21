// Confirmation de suppression
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', (e) => {
            if(!confirm('Êtes-vous sûr de vouloir supprimer ?')) {
                e.preventDefault();
            }
        });
    });
});

// Disparition automatique des alertes après 5 secondes
setTimeout(() => {
    document.querySelectorAll('.alert-success, .alert-error').forEach(el => {
        el.style.transition = 'opacity 0.5s';
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 500);
    });
}, 5000);