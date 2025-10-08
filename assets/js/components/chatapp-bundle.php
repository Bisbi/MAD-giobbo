<?php
// PHP Script Wrapper per servire il file JavaScript
// File name: chatapp-wrapper.php

/**
 * Questo script funge da wrapper per servire il file JavaScript compilato
 * forzando il corretto Content-Type: application/javascript.
 *
 * Istruzioni:
 * 1. Rinomina il tuo file compilato: chatapp-bundle.js -> chatapp-bundle.txt
 * 2. Aggiorna il tag <script> nel tuo HTML per puntare a questo file wrapper.
 */

// 1. Pulizia del buffer di output e disattivazione (rimossa la seconda chiamata per sicurezza)
ob_clean();
ob_end_clean();

// 2. Definisci il percorso del file JavaScript compilato (rinominato .txt)
$file_path = __DIR__ . '/chatapp-bundle.txt';

// 3. Controlla se il file esiste
if (!file_exists($file_path)) {
    // Invia un errore 404 e termina lo script
    http_response_code(404);
    die('// ERROR: Compiled JS file (chatapp-bundle.txt) not found on server.');
}

// 4. Imposta le intestazioni HTTP CORRETTE

// Cruciale: Forza il browser a trattarlo come codice JavaScript/modulo ES6.
header('Content-Type: application/javascript; charset=utf-8');

// Aggiunta per il caching: indica che la risorsa non è statica e deve essere ricaricata
// Questo è un buon punto di partenza, ma potresti volerlo regolare (vedi sezione Consigli).
header('Cache-Control: public, max-age=0, must-revalidate');

// Imposta l'intestazione Content-Length
header('Content-Length: ' . filesize($file_path));

// 5. Prevenzione dello sniffing del tipo (aiuta in hosting molto restrittivi)
header('X-Content-Type-Options: nosniff');

// 6. Stampa il contenuto del file JavaScript
readfile($file_path);
exit;

?>