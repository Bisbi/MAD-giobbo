<?php
/**
 * 🚫 NO CACHE HEADERS - Forza Ricarica Pagine
 *
 * Include questo file all'inizio di config.php per applicare
 * header no-cache a tutte le pagine PHP dell'applicazione
 */

// Forza no-cache su tutte le risposte HTTP
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Header di sicurezza aggiuntivi
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");

// Versioning helper per assets
if (!function_exists('asset_version')) {
    function asset_version($file_path) {
        // La variabile ROOT_PATH DEVE essere definita prima di includere questo file
        // Attenzione: usiamo un fallback se non è ancora definita per evitare un errore fatale
        $root_path = defined('ROOT_PATH') ? ROOT_PATH : ''; 
        $full_path = $root_path . $file_path;
        if (file_exists($full_path)) {
            return filemtime($full_path);
        }
        return time();
    }
}

// Helper per includere CSS/JS con versioning automatico
if (!function_exists('asset')) {
    function asset($path) {
        // La variabile BASE_PATH DEVE essere definita prima di includere questo file
        // Attenzione: usiamo un fallback se non è ancora definita per evitare un errore fatale
        $base_path = defined('BASE_PATH') ? BASE_PATH : ''; 
        $version = asset_version($path);
        return $base_path . $path . '?v=' . $version;
    }
}
