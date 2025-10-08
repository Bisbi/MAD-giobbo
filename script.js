/**
 * 🎲 Sistema Carte D&D - JavaScript Unificato
 * Gestisce tutte le interazioni lato client
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Inizializzazione
    initModals();
    initForms();
    initFilters();
    initUpload();
    initMessages();
    
    console.log('🎲 Sistema Carte D&D caricato!');
});

/**
 * Gestione Modal
 */
function initModals() {
    // Chiudi modal cliccando fuori
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            closeModal(e.target.id);
        }
    });
    
    // Chiudi modal con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal[style*="block"]');
            if (openModal) {
                closeModal(openModal.id);
            }
        }
    });
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        // Focus primo input se presente
        const firstInput = modal.querySelector('input, textarea, select');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        
        // Reset form se presente
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
        }
    }
}

/**
 * Gestione Form Avanzata
 */
function initForms() {
    // Submit form con AJAX se ha classe ajax-form
    document.addEventListener('submit', function(e) {
        if (e.target.classList.contains('ajax-form')) {
            e.preventDefault();
            submitForm(e.target);
        }
    });
    
    // Auto-resize textarea
    document.querySelectorAll('textarea').forEach(textarea => {
        textarea.addEventListener('input', autoResize);
        autoResize.call(textarea);
    });
    
    // Conferma prima di eliminare
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('confirm-delete')) {
            if (!confirm('Sei sicuro di voler eliminare questo elemento?')) {
                e.preventDefault();
            }
        }
    });
}

function autoResize() {
    this.style.height = 'auto';
    this.style.height = this.scrollHeight + 'px';
}

async function submitForm(form) {
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Disabilita bottone durante invio
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Invio...';
    }
    
    try {
        const response = await fetch(form.action || window.location.href, {
            method: 'POST',
            body: formData
        });
        
        if (response.ok) {
            const result = await response.text();
            showMessage('Operazione completata con successo!', 'success');
            
            // Chiudi modal se siamo in una
            const modal = form.closest('.modal');
            if (modal) {
                closeModal(modal.id);
            }
            
            // Ricarica pagina o aggiorna contenuto
            setTimeout(() => window.location.reload(), 1000);
        } else {
            throw new Error('Errore nel server');
        }
        
    } catch (error) {
        console.error('Errore submit form:', error);
        showMessage('Errore durante l\'operazione', 'error');
        
    } finally {
        // Riabilita bottone
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Salva';
        }
    }
}

/**
 * Gestione Filtri
 */
function initFilters() {
    const filterInputs = document.querySelectorAll('.filter-input');
    
    filterInputs.forEach(input => {
        input.addEventListener('input', debounce(applyFilters, 300));
        input.addEventListener('change', applyFilters);
    });
}

function applyFilters() {
    const filters = {};
    
    // Raccogli tutti i filtri attivi
    document.querySelectorAll('.filter-input').forEach(input => {
        if (input.value.trim()) {
            filters[input.name] = input.value.trim();
        }
    });
    
    // Applica filtri agli elementi
    filterElements(filters);
    
    // Aggiorna URL per condivisione
    updateUrlParams(filters);
}

function filterElements(filters) {
    const items = document.querySelectorAll('.filterable-item');
    let visibleCount = 0;
    
    items.forEach(item => {
        let visible = true;
        
        // Controlla ogni filtro
        for (const [key, value] of Object.entries(filters)) {
            const itemValue = item.dataset[key] || item.textContent.toLowerCase();
            
            if (key === 'search') {
                // Ricerca testuale
                if (!itemValue.toLowerCase().includes(value.toLowerCase())) {
                    visible = false;
                    break;
                }
            } else {
                // Filtro esatto
                if (itemValue.toLowerCase() !== value.toLowerCase()) {
                    visible = false;
                    break;
                }
            }
        }
        
        // Mostra/nascondi elemento
        if (visible) {
            item.style.display = '';
            item.classList.add('fade-in');
            visibleCount++;
        } else {
            item.style.display = 'none';
            item.classList.remove('fade-in');
        }
    });
    
    // Mostra contatore risultati
    updateResultsCount(visibleCount);
}

function updateResultsCount(count) {
    const counter = document.getElementById('results-count');
    if (counter) {
        counter.textContent = `${count} risultat${count !== 1 ? 'i' : 'o'}`;
    }
}

function updateUrlParams(params) {
    const url = new URL(window.location);
    
    // Rimuovi parametri esistenti
    for (const key of url.searchParams.keys()) {
        if (key.startsWith('filter_')) {
            url.searchParams.delete(key);
        }
    }
    
    // Aggiungi nuovi parametri
    for (const [key, value] of Object.entries(params)) {
        url.searchParams.set('filter_' + key, value);
    }
    
    // Aggiorna URL senza reload
    history.replaceState(null, '', url);
}

/**
 * Gestione Upload File
 */
function initUpload() {
    const uploadAreas = document.querySelectorAll('.upload-area');
    
    uploadAreas.forEach(area => {
        const fileInput = area.querySelector('input[type="file"]');
        if (!fileInput) return;
        
        // Click per aprire file dialog
        area.addEventListener('click', () => fileInput.click());
        
        // Drag & Drop
        area.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });
        
        area.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });
        
        area.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleFileSelect(fileInput);
            }
        });
        
        // File selezionato normalmente
        fileInput.addEventListener('change', function() {
            handleFileSelect(this);
        });
    });
}

function handleFileSelect(input) {
    const file = input.files[0];
    if (!file) return;
    
    const uploadArea = input.closest('.upload-area');
    const fileName = uploadArea.querySelector('.file-name');
    
    if (fileName) {
        fileName.textContent = `File selezionato: ${file.name}`;
    }
    
    // Validazione tipo file
    const allowedTypes = input.accept ? input.accept.split(',').map(t => t.trim()) : [];
    if (allowedTypes.length > 0) {
        const fileType = '.' + file.name.split('.').pop().toLowerCase();
        const mimeType = file.type;
        
        if (!allowedTypes.some(type => type === fileType || type === mimeType)) {
            showMessage('Tipo di file non supportato', 'error');
            input.value = '';
            return;
        }
    }
    
    // Validazione dimensione (5MB default)
    const maxSize = parseInt(input.dataset.maxSize) || 5242880;
    if (file.size > maxSize) {
        showMessage('File troppo grande (max ' + formatBytes(maxSize) + ')', 'error');
        input.value = '';
        return;
    }
    
    // Auto-submit se form ha classe auto-submit
    const form = input.closest('form');
    if (form && form.classList.contains('auto-submit')) {
        setTimeout(() => form.submit(), 500);
    }
}

/**
 * Gestione Messaggi
 */
function initMessages() {
    // Auto-hide messaggi dopo 5 secondi
    document.querySelectorAll('.message').forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => message.remove(), 300);
        }, 5000);
    });
}

function showMessage(text, type = 'info') {
    const message = document.createElement('div');
    message.className = `message ${type} fade-in`;
    message.innerHTML = (type === 'success' ? '✅ ' : type === 'error' ? '❌ ' : 'ℹ️ ') + text;
    
    // Inserisci in cima alla pagina
    const container = document.querySelector('.container') || document.body;
    container.insertBefore(message, container.firstChild);
    
    // Auto-hide
    setTimeout(() => {
        message.style.opacity = '0';
        setTimeout(() => message.remove(), 300);
    }, 5000);
}

/**
 * Utility Functions
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('it-IT', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showMessage('Copiato negli appunti!', 'success');
    }).catch(() => {
        showMessage('Errore copia', 'error');
    });
}

/**
 * API Helper Functions
 */
async function apiCall(endpoint, method = 'GET', data = null) {
    const options = {
        method,
        headers: {
            'Content-Type': 'application/json',
        }
    };
    
    if (data) {
        options.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(`api.php?endpoint=${endpoint}`, options);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        return await response.json();
        
    } catch (error) {
        console.error('API Error:', error);
        showMessage('Errore di connessione', 'error');
        throw error;
    }
}

/**
 * Carte D&D specifiche
 */
function printCards() {
    // Nascondi elementi non stampabili
    document.body.classList.add('print-mode');
    
    // Attiva stampa
    window.print();
    
    // Ripristina visualizzazione normale dopo stampa
    setTimeout(() => {
        document.body.classList.remove('print-mode');
    }, 1000);
}

function toggleCardPreview(cardId) {
    const card = document.getElementById('card-' + cardId);
    if (card) {
        card.classList.toggle('preview-mode');
    }
}

/**
 * Admin specifiche
 */
function confirmBulkAction(action, count) {
    return confirm(`Eseguire ${action} su ${count} element${count > 1 ? 'i' : 'o'}?`);
}

function exportData(type) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'admin.php';
    
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'export';
    
    const typeInput = document.createElement('input');
    typeInput.type = 'hidden';
    typeInput.name = 'type';
    typeInput.value = type;
    
    form.appendChild(actionInput);
    form.appendChild(typeInput);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

/**
 * Feature Detection & Fallbacks
 */
function supportsFeature(feature) {
    switch (feature) {
        case 'clipboard':
            return navigator.clipboard && window.isSecureContext;
        case 'serviceworker':
            return 'serviceWorker' in navigator;
        case 'notification':
            return 'Notification' in window;
        default:
            return false;
    }
}

// Polyfill per browser meno recenti
if (!Element.prototype.closest) {
    Element.prototype.closest = function(s) {
        var el = this;
        do {
            if (el.matches(s)) return el;
            el = el.parentElement || el.parentNode;
        } while (el !== null && el.nodeType === 1);
        return null;
    };
}

if (!Element.prototype.matches) {
    Element.prototype.matches = Element.prototype.matchesSelector ||
        Element.prototype.webkitMatchesSelector ||
        Element.prototype.mozMatchesSelector ||
        Element.prototype.msMatchesSelector;
}

/**
 * Global Event Handlers
 */
window.openModal = openModal;
window.closeModal = closeModal;
window.showMessage = showMessage;
window.printCards = printCards;
window.exportData = exportData;
window.apiCall = apiCall;
