// Funzione immediata per prevenire il FOUC (Flash of Unstyled Content)
(function() {
    // 1. Legge il tema salvato o usa 'light' come default
    const savedTheme = localStorage.getItem('theme') || 'light';
    
    // 2. Applica immediatamente l'attributo data-theme al tag html
    document.documentElement.setAttribute('data-theme', savedTheme);

    // 3. Funzione per aggiornare l'icona (questa sarà richiamata di nuovo in app.js)
    // Non è strettamente necessario qui, ma se l'icona fosse nel <head> potrebbe servire
    /*
    function updateIcon() {
        const icon = document.getElementById('themeIcon');
        if (icon) {
            icon.className = savedTheme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
        }
    }
    updateIcon();
    */
})();
