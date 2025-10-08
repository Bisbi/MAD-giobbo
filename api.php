<?php
/**
 * 🎲 Sistema Carte D&D - API Endpoint Unificato  
 * Gestisce tutte le chiamate API in un singolo file
 */

require_once 'config.php';

// Headers per API JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gestione preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$endpoint = $_GET['endpoint'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// Rate limiting semplice (100 richieste per ora per IP)
$clientIp = $_SERVER['REMOTE_ADDR'];
$rateKey = 'api_rate_' . md5($clientIp);
$currentHour = date('Y-m-d H');

if (function_exists('apcu_fetch')) {
    $requests = apcu_fetch($rateKey . '_' . $currentHour) ?: 0;
    if ($requests >= 100) {
        http_response_code(429);
        echo json_encode(['error' => 'Rate limit exceeded. Try again in an hour.']);
        exit;
    }
    apcu_store($rateKey . '_' . $currentHour, $requests + 1, 3600);
}

// Funzioni helper per API
function apiResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function apiError($message, $status = 400) {
    apiResponse(['error' => $message, 'status' => $status], $status);
}

function validateRequired($data, $fields) {
    foreach ($fields as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            apiError("Campo richiesto mancante: $field");
        }
    }
}

function getPaginationParams() {
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = min(50, max(1, intval($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;
    
    return [$page, $limit, $offset];
}

// Router principale
try {
    switch ($endpoint) {
        
        // === ENDPOINT INCANTESIMI ===
        case 'spells':
            if ($method === 'GET') {
                // Lista incantesimi con filtri
                [$page, $limit, $offset] = getPaginationParams();
                
                $where = ['active = 1'];
                $params = [];
                
                // Filtri
                if (!empty($_GET['level'])) {
                    $where[] = 'level = ?';
                    $params[] = $_GET['level'];
                }
                
                if (!empty($_GET['school'])) {
                    $where[] = 'school = ?';
                    $params[] = $_GET['school'];
                }
                
                if (!empty($_GET['search'])) {
                    $where[] = '(name_it LIKE ? OR name_en LIKE ? OR description_it LIKE ?)';
                    $searchTerm = '%' . $_GET['search'] . '%';
                    $params[] = $searchTerm;
                    $params[] = $searchTerm;
                    $params[] = $searchTerm;
                }
                
                $whereClause = implode(' AND ', $where);
                
                // Count totale per paginazione
                $totalCount = Database::fetch("SELECT COUNT(*) as count FROM ci_spells WHERE $whereClause", $params)['count'];
                
                // Dati paginati
                $spells = Database::fetchAll("SELECT * FROM ci_spells WHERE $whereClause ORDER BY level, name_it LIMIT $limit OFFSET $offset", $params);
                
                apiResponse([
                    'data' => $spells,
                    'pagination' => [
                        'page' => $page,
                        'limit' => $limit,
                        'total' => $totalCount,
                        'pages' => ceil($totalCount / $limit)
                    ]
                ]);
            }
            break;
            
        case 'spells/search':
            if ($method === 'GET') {
                $query = trim($_GET['q'] ?? '');
                if (strlen($query) < 2) {
                    apiError('Query di ricerca troppo corta (minimo 2 caratteri)');
                }
                
                $spells = Database::fetchAll("
                    SELECT id, name_it, name_en, level, school 
                    FROM ci_spells 
                    WHERE active = 1 AND (name_it LIKE ? OR name_en LIKE ?) 
                    ORDER BY 
                        CASE WHEN name_it LIKE ? THEN 1 ELSE 2 END,
                        name_it
                    LIMIT 10
                ", ["%$query%", "%$query%", "$query%"]);
                
                apiResponse($spells);
            }
            break;
            
        case 'spells/detail':
            if ($method === 'GET') {
                $id = intval($_GET['id'] ?? 0);
                if (!$id) apiError('ID incantesimo richiesto');
                
                $spell = Database::fetch("SELECT * FROM ci_spells WHERE id = ? AND active = 1", [$id]);
                if (!$spell) apiError('Incantesimo non trovato', 404);
                
                apiResponse($spell);
            }
            break;
            
        // === ENDPOINT CREATURE ===
        case 'creatures':
            if ($method === 'GET') {
                [$page, $limit, $offset] = getPaginationParams();
                
                $where = ['active = 1'];
                $params = [];
                
                // Filtri
                if (!empty($_GET['creature_type'])) {
                    $where[] = 'creature_type = ?';
                    $params[] = $_GET['creature_type'];
                }
                
                if (!empty($_GET['size'])) {
                    $where[] = 'size = ?';
                    $params[] = $_GET['size'];
                }
                
                if (!empty($_GET['max_cr'])) {
                    // Filtro CR numerico semplificato
                    $where[] = 'CAST(challenge_rating AS DECIMAL(3,2)) <= ?';
                    $params[] = floatval($_GET['max_cr']);
                }
                
                if (!empty($_GET['search'])) {
                    $where[] = '(name_it LIKE ? OR name_en LIKE ? OR description_it LIKE ?)';
                    $searchTerm = '%' . $_GET['search'] . '%';
                    $params[] = $searchTerm;
                    $params[] = $searchTerm;
                    $params[] = $searchTerm;
                }
                
                $whereClause = implode(' AND ', $where);
                
                // Count totale
                $totalCount = Database::fetch("SELECT COUNT(*) as count FROM ci_creatures WHERE $whereClause", $params)['count'];
                
                // Dati paginati
                $creatures = Database::fetchAll("SELECT * FROM ci_creatures WHERE $whereClause ORDER BY creature_type, name_it LIMIT $limit OFFSET $offset", $params);
                
                apiResponse([
                    'data' => $creatures,
                    'pagination' => [
                        'page' => $page,
                        'limit' => $limit,
                        'total' => $totalCount,
                        'pages' => ceil($totalCount / $limit)
                    ]
                ]);
            }
            break;
            
        case 'creatures/types':
            if ($method === 'GET') {
                $types = Database::fetchAll("
                    SELECT 
                        creature_type,
                        COUNT(*) as count
                    FROM ci_creatures 
                    WHERE active = 1 
                    GROUP BY creature_type 
                    ORDER BY creature_type
                ");
                
                apiResponse($types);
            }
            break;
            
        case 'creatures/detail':
            if ($method === 'GET') {
                $id = intval($_GET['id'] ?? 0);
                if (!$id) apiError('ID creatura richiesto');
                
                $creature = Database::fetch("SELECT * FROM ci_creatures WHERE id = ? AND active = 1", [$id]);
                if (!$creature) apiError('Creatura non trovata', 404);
                
                apiResponse($creature);
            }
            break;
            
        case 'creatures/random':
            if ($method === 'GET') {
                $type = $_GET['type'] ?? '';
                $whereClause = 'active = 1';
                $params = [];
                
                if ($type) {
                    $whereClause .= ' AND creature_type = ?';
                    $params[] = $type;
                }
                
                $creature = Database::fetch("SELECT * FROM ci_creatures WHERE $whereClause ORDER BY RAND() LIMIT 1", $params);
                
                if (!$creature) apiError('Nessuna creatura trovata', 404);
                
                apiResponse($creature);
            }
            break;
            
        // === ENDPOINT STATISTICHE ===
        case 'stats':
            if ($method === 'GET') {
                $stats = [
                    'spells' => [
                        'total' => Database::fetch("SELECT COUNT(*) as count FROM ci_spells WHERE active = 1")['count'],
                        'by_level' => Database::fetchAll("
                            SELECT level, COUNT(*) as count 
                            FROM ci_spells 
                            WHERE active = 1 
                            GROUP BY level 
                            ORDER BY level
                        "),
                        'by_school' => Database::fetchAll("
                            SELECT school, COUNT(*) as count 
                            FROM ci_spells 
                            WHERE active = 1 
                            GROUP BY school 
                            ORDER BY count DESC
                        ")
                    ],
                    'creatures' => [
                        'total' => Database::fetch("SELECT COUNT(*) as count FROM ci_creatures WHERE active = 1")['count'],
                        'by_type' => Database::fetchAll("
                            SELECT creature_type, COUNT(*) as count 
                            FROM ci_creatures 
                            WHERE active = 1 
                            GROUP BY creature_type 
                            ORDER BY count DESC
                        "),
                        'by_size' => Database::fetchAll("
                            SELECT size, COUNT(*) as count 
                            FROM ci_creatures 
                            WHERE active = 1 
                            GROUP BY size 
                            ORDER BY 
                                CASE size 
                                    WHEN 'Tiny' THEN 1
                                    WHEN 'Small' THEN 2
                                    WHEN 'Medium' THEN 3
                                    WHEN 'Large' THEN 4
                                    WHEN 'Huge' THEN 5
                                    WHEN 'Gargantuan' THEN 6
                                    ELSE 7
                                END
                        ")
                    ],
                    'generated_at' => date('c')
                ];
                
                apiResponse($stats);
            }
            break;
            
        // === ENDPOINT UTILITÀ ===
        case 'health':
            // Health check per monitoraggio
            $health = [
                'status' => 'ok',
                'timestamp' => time(),
                'version' => '1.0.0',
                'database' => 'connected'
            ];
            
            try {
                Database::query("SELECT 1");
                $health['database'] = 'connected';
            } catch (Exception $e) {
                $health['database'] = 'error';
                $health['status'] = 'degraded';
            }
            
            apiResponse($health);
            break;
            
        case 'search':
            // Ricerca globale in incantesimi e creature
            if ($method === 'GET') {
                $query = trim($_GET['q'] ?? '');
                if (strlen($query) < 2) {
                    apiError('Query di ricerca troppo corta (minimo 2 caratteri)');
                }
                
                $results = [
                    'spells' => Database::fetchAll("
                        SELECT 'spell' as type, id, name_it, name_en, level as extra
                        FROM ci_spells 
                        WHERE active = 1 AND (name_it LIKE ? OR name_en LIKE ?) 
                        ORDER BY name_it
                        LIMIT 5
                    ", ["%$query%", "%$query%"]),
                    
                    'creatures' => Database::fetchAll("
                        SELECT 'creature' as type, id, name_it, name_en, creature_type as extra
                        FROM ci_creatures 
                        WHERE active = 1 AND (name_it LIKE ? OR name_en LIKE ?) 
                        ORDER BY name_it
                        LIMIT 5  
                    ", ["%$query%", "%$query%"])
                ];
                
                apiResponse($results);
            }
            break;
            
        // === ENDPOINT ADMIN (Richiede autenticazione) ===
        case 'admin/logs':
            requireAdmin();
            if ($method === 'GET') {
                [$page, $limit, $offset] = getPaginationParams();
                
                $logs = Database::fetchAll("
                    SELECT l.*, a.username 
                    FROM ci_logs l 
                    LEFT JOIN ci_admin a ON l.admin_id = a.id 
                    ORDER BY l.created_at DESC 
                    LIMIT $limit OFFSET $offset
                ");
                
                $totalCount = Database::fetch("SELECT COUNT(*) as count FROM ci_logs")['count'];
                
                apiResponse([
                    'data' => $logs,
                    'pagination' => [
                        'page' => $page,
                        'limit' => $limit,
                        'total' => $totalCount,
                        'pages' => ceil($totalCount / $limit)
                    ]
                ]);
            }
            break;
            
        default:
            apiError('Endpoint non trovato: ' . $endpoint, 404);
    }
    
} catch (Exception $e) {
    debugLog("API Error [$endpoint]: " . $e->getMessage());
    
    if (DEBUG) {
        apiError('Errore interno: ' . $e->getMessage(), 500);
    } else {
        apiError('Errore interno del server', 500);
    }
}

?>
