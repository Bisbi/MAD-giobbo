<?php
/**
 * 🎲 CARTE D&D V2 - Router Principale
 * Punto di ingresso unico per l'applicazione.
 */

// Includi questo blocco per forzare la visualizzazione degli errori durante lo sviluppo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

// Ottieni la pagina richiesta dall'URL, con 'home' come predefinita
$page = $_GET['page'] ?? 'home';

// Mappa delle rotte valide: associa un nome di pagina al suo file corrispondente
$routes = [
    'home'      => 'pages/home.php',
    'spells'    => 'pages/spells.php',
    'creatures' => 'pages/creatures.php',
    'books'     => 'pages/books.php',
    'book_detail' => 'pages/book_detail.php',
    'book_edit' => 'pages/book_edit.php',
    'admin'     => 'pages/admin.php',
    'login'     => 'pages/login.php',
    'register'  => 'pages/register.php',
    'logout'    => 'pages/logout.php',
    'print'     => 'pages/print.php',
    'profile'   => 'pages/profile.php',
    'print_token' => 'pages/print_token.php',
    'my_avatars' => 'pages/my_avatars.php',
    'about'     => 'pages/about.php',
];

// Se la pagina richiesta non è valida, reindirizza alla home
if (!isset($routes[$page])) {
    $page = 'home';
}

$page_file = $routes[$page];

// Gestione speciale per le pagine di stampa, che non hanno header/footer
if ($page === 'print' || $page === 'print_token') {
    if (file_exists($page_file)) {
        require_once $page_file;
    } else {
        die('Errore: Pagina di stampa non trovata.');
    }
    // Interrompi l'esecuzione per non caricare il resto del layout
    exit;
}

// Per tutte le altre pagine, carica il layout standard
require_once 'includes/header.php';
require_once 'includes/navbar.php';

// Carica il contenuto della pagina richiesta
if (file_exists($page_file)) {
    require_once $page_file;
} else {
    // Se il file non esiste, mostra un errore
    echo '<div class="container mt-5"><div class="alert alert-danger">Pagina non trovata!</div></div>';
}

// Carica il footer
require_once 'includes/footer.php';
?>

