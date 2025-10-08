// 🎲 CARTE D&D V2 - App JavaScript

// Esponi la funzione showToast globalmente per uso in altre parti dell'app
window.showToast = function(message, type = 'info') {
    // Questa sarà definita dopo il DOMContentLoaded, ma la esponiamo subito
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            showToastInternal(message, type);
        });
    } else {
        showToastInternal(message, type);
    }
};

function showToastInternal(message, type) {
    const toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) return;
    
    // Configura colori e icone per tipo
    const config = {
        success: {
            bgClass: 'bg-success',
            icon: 'bi-check-circle-fill',
            title: 'Successo'
        },
        error: {
            bgClass: 'bg-danger',
            icon: 'bi-exclamation-triangle-fill',
            title: 'Errore'
        },
        info: {
            bgClass: 'bg-info',
            icon: 'bi-info-circle-fill',
            title: 'Informazione'
        },
        warning: {
            bgClass: 'bg-warning',
            icon: 'bi-exclamation-circle-fill',
            title: 'Attenzione'
        }
    };
    
    const toastConfig = config[type] || config.info;
    const toastId = 'toast-' + Date.now();
    
    // Crea il toast
    const toastHTML = `
        <div id="${toastId}" class="toast align-items-center text-white ${toastConfig.bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi ${toastConfig.icon} me-2"></i>
                    <strong>${toastConfig.title}:</strong> ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Chiudi"></button>
            </div>
        </div>
    `;
    
    // Aggiungi al container
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    // Inizializza e mostra il toast
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: 5000
    });
    
    toast.show();
    
    // Rimuovi dal DOM dopo la chiusura
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}

// ==================== DARK MODE ====================
document.addEventListener('DOMContentLoaded', function() {
    // Carica tema salvato
    //const savedTheme = localStorage.getItem('theme') || 'light';
    //document.documentElement.setAttribute('data-theme', savedTheme);
    //updateThemeIcon(savedTheme);
    
    // Toggle dark mode
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });
    }
});

function updateThemeIcon(theme) {
    const icon = document.getElementById('themeIcon');
    if (icon) {
        icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
    }
}

// ==================== TOAST NOTIFICATIONS ====================
function showToast(message, type = 'info') {
    const toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) return;
    
    // Configura colori e icone per tipo
    const config = {
        success: {
            bgClass: 'bg-success',
            icon: 'bi-check-circle-fill',
            title: 'Successo'
        },
        error: {
            bgClass: 'bg-danger',
            icon: 'bi-exclamation-triangle-fill',
            title: 'Errore'
        },
        info: {
            bgClass: 'bg-info',
            icon: 'bi-info-circle-fill',
            title: 'Informazione'
        },
        warning: {
            bgClass: 'bg-warning',
            icon: 'bi-exclamation-circle-fill',
            title: 'Attenzione'
        }
    };
    
    const toastConfig = config[type] || config.info;
    const toastId = 'toast-' + Date.now();
    
    // Crea il toast
    const toastHTML = `
        <div id="${toastId}" class="toast align-items-center text-white ${toastConfig.bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi ${toastConfig.icon} me-2"></i>
                    <strong>${toastConfig.title}:</strong> ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Chiudi"></button>
            </div>
        </div>
    `;
    
    // Aggiungi al container
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    // Inizializza e mostra il toast
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: 5000
    });
    
    toast.show();
    
    // Rimuovi dal DOM dopo la chiusura
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}

// Mostra toast di sistema all'avvio
document.addEventListener('DOMContentLoaded', function() {
    if (window.systemMessages && Array.isArray(window.systemMessages)) {
        window.systemMessages.forEach(msg => {
            showToast(msg.message, msg.type);
        });
    }
});

// ==================== AUTO DISMISS ALERTS (LEGACY - Mantenuto per compatibilità) ====================
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});

// ==================== FORM VALIDATION ====================
(function() {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();
