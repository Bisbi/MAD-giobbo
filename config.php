<?php
/**
 * 🎲 CARTE D&D V2 - Configurazione
 * Sistema pulito con routing centralizzato
 */

// ⚡ SOLUZIONE: ABILITA OUTPUT BUFFERING
// Questo cattura ogni output HTML/spazio involontario 
// e lo ritarda fino alla fine dello script, garantendo che gli header 
// (sessione, no-cache) vengano inviati per primi.
ob_start();

// ⚡ FORZA NO-CACHE (Include header anti-cache)
require_once __DIR__ . '/no-cache.php';

// Avvio sessione
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // <-- Questa linea ora è protetta dall'Output Buffer.
}

// ========================================
// CONFIGURAZIONE BASE
// ========================================

define('BASE_PATH', '/carte');
define('ROOT_PATH', __DIR__);

// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'gbibbo_'); // Uso gbibbo_ come placeholder
define('DB_USER', 'gbibbo'); // Uso gbibbo come placeholder
define('DB_PASS', 'oMp1m$74Ttc6@4w4'); // Uso la password fornita in precedenza
define('DB_PREFIX', 'ci_');

// App
define('APP_NAME', 'MAD');
define('APP_VERSION', '2.1'); 
define('DEBUG', true);

// ========================================
// DATABASE CLASS
// ========================================

class Database {
    private static $connection = null;
    
    public static function connect() {
        if (self::$connection === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                self::$connection = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]);
            } catch (PDOException $e) {
                if (DEBUG) {
                    die("❌ Errore DB: " . $e->getMessage());
                }
                die("Errore di connessione");
            }
        }
        return self::$connection;
    }
    
    public static function query($sql, $params = []) {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public static function fetch($sql, $params = []) {
        return self::query($sql, $params)->fetch();
    }
    
    public static function fetchAll($sql, $params = []) {
        return self::query($sql, $params)->fetchAll();
    }
}

// ========================================
// UTILITY FUNCTIONS
// ========================================

function redirect($url) {
    // Se l'URL non inizia già con BASE_PATH, lo aggiungiamo.
    // Altrimenti, lo usiamo così com'è.
    if (BASE_PATH !== '/' && strpos($url, BASE_PATH) !== 0) {
        $location = BASE_PATH . $url;
    } else {
        $location = $url;
    }
    
    header("Location: " . $location);
    exit;
}

function isAdmin() {
    return isset($_SESSION['admin']) && $_SESSION['admin'] === true;
}

function isUser() {
    return isset($_SESSION['user']) && $_SESSION['user'] === true;
}

function requireAdmin() {
    if (!isAdmin()) {
        redirect('/admin?action=login');
    }
}

function sanitize($input) {
    // FIX: Aggiunto il null coalescing operator (?? '') per evitare l'errore "Deprecated" con valori null.
    return htmlspecialchars(trim($input ?? ''), ENT_QUOTES, 'UTF-8');
}

function success($message) {
    $_SESSION['success'] = $message;
}

function error($message) {
    $_SESSION['error'] = $message;
}

function getMessages() {
    $messages = [];
    if (isset($_SESSION['success'])) {
        $messages['success'] = $_SESSION['success'];
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        $messages['error'] = $_SESSION['error'];
        unset($_SESSION['error']);
    }
    return $messages;
}

// ========================================
// CSRF PROTECTION
// ========================================

function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}