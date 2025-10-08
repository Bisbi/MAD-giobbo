<?php
/**
 * Endpoint API per la copia di un libro incantesimi condiviso da un utente all'altro.
 * Questa logica interroga il database MySQL per eseguire la copia della struttura del libro.
 *
 * NOTA: Assumiamo che la classe 'Database' e le funzioni di Utility (es. getMessages) siano già caricate.
 */

// -----------------------------------------------------------
// 1. Configurazione Iniziale e Ricezione Dati
// -----------------------------------------------------------

header('Content-Type: application/json');

// La richiesta arriva dal frontend (React/fetch) in formato JSON
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Dati attesi dal frontend
$shareToken = $data['shareToken'] ?? null;
$recipientFirebaseId = $data['recipientFirebaseId'] ?? null; // ID Firebase/Canvas dell'utente ricevente
$senderAlias = $data['senderAlias'] ?? 'Utente Sconosciuto';

// -----------------------------------------------------------
// 2. Mappatura Utente (CRITICO!)
// -----------------------------------------------------------
// Funzione helper per recuperare il vero ID utente MySQL (ci_users.id)
// Basato sull'ID Firebase/Canvas ricevuto dal frontend.
function get_mysql_user_id($firebaseId) {
    // QUI DOVRESTI IMPLEMENTARE LA LOGICA REALE PER CERCARE UN UTENTE
    // NELLA TABELLA ci_users BASANDOTI SUL SUO FIREBASE/CANVAS ID.
    
    // Siccome l'ID Firebase non è nel tuo dump MySQL, per ora usiamo il
    // placeholder fisso '6' (capitano) come ID utente MySQL valido.
    // DEVI ASSOLUTAMENTE SOSTITUIRE QUESTO CON UNA VERA QUERY.
    
    // Esempio fittizio con ID utente statico:
    return 6; 
}


$recipientUserId = get_mysql_user_id($recipientFirebaseId);

if (!$shareToken || !$recipientUserId) {
    echo json_encode(['success' => false, 'message' => 'Dati di condivisione mancanti o utente non autenticato.']);
    exit;
}

// -----------------------------------------------------------
// 3. Recupero del Libro Originale (MySQL)
// -----------------------------------------------------------

try {
    // 3a. Trova il libro originale tramite share_token e verifica che sia pubblico
    $sql_book = "
        SELECT id, name, description, content_json
        FROM ci_spellbooks 
        WHERE share_token = ? AND is_public = 1
    ";
    
    // USO DI Database::fetch() per ottenere una singola riga
    $originalBook = Database::fetch($sql_book, [$shareToken]); 

    if (!$originalBook) {
        echo json_encode(['success' => false, 'message' => 'Libro non trovato o non condivisibile.']);
        exit;
    }

    $originalBookId = $originalBook['id'];
    $newBookName = $originalBook['name'] . " (Copia)";
    // Imposta la descrizione automatica con l'alias del mittente e il token originale
    $newBookDescription = "Copia condivisa da " . $senderAlias . " (Token: " . $shareToken . ") – [Originale: {$originalBook['name']}]";


    // 3b. Recupera tutti gli incantesimi del libro originale
    $sql_spells = "
        SELECT spell_id, sort_order, notes 
        FROM ci_spellbook_spells 
        WHERE spellbook_id = ? 
        ORDER BY sort_order
    ";
    // USO DI Database::fetchAll() per ottenere tutte le righe
    $originalSpells = Database::fetchAll($sql_spells, [$originalBookId]); 


    // 3c. Recupera tutte le creature del libro originale
    $sql_creatures = "
        SELECT creature_id, sort_order, overrides 
        FROM ci_spellbook_creatures 
        WHERE spellbook_id = ? 
        ORDER BY sort_order
    ";
    // USO DI Database::fetchAll() per ottenere tutte le righe
    $originalCreatures = Database::fetchAll($sql_creatures, [$originalBookId]); 

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Errore nel recupero dati: ' . $e->getMessage()]);
    exit;
}

// -----------------------------------------------------------
// 4. Creazione della Nuova Copia del Libro (Transazione)
// -----------------------------------------------------------

try {
    $pdo = Database::connect();
    // Inizia la transazione (Garantisce l'integrità dei dati)
    $pdo->beginTransaction(); 

    // 4a. Inserisci il nuovo record in ci_spellbooks (come libro privato dell'utente)
    $sql_insert_book = "
        INSERT INTO ci_spellbooks 
        (user_id, owner_type, name, description, content_json, is_public, share_token)
        VALUES (?, 'user', ?, ?, ?, 0, NULL)
    ";
    
    // Per ottenere l'ID inserito con PDO dobbiamo usare l'oggetto PDO stesso
    $stmt = $pdo->prepare($sql_insert_book);
    $stmt->execute([
        $recipientUserId, 
        $newBookName, 
        $newBookDescription, 
        $originalBook['content_json']
    ]);
    $newBookId = $pdo->lastInsertId(); // Ottiene l'ID appena generato

    if (!$newBookId) {
        throw new Exception("Impossibile creare il nuovo record del libro.");
    }


    // 4b. Copia gli incantesimi nella nuova entry
    $sql_insert_spell = "
        INSERT INTO ci_spellbook_spells 
        (spellbook_id, spell_id, sort_order, notes)
        VALUES (?, ?, ?, ?)
    ";
    foreach ($originalSpells as $spell) {
        // USO DI Database::query() (che usa prepare/execute) per l'inserimento
        Database::query($sql_insert_spell, [ 
            $newBookId, 
            $spell['spell_id'], 
            $spell['sort_order'], 
            $spell['notes']
        ]); 
    }


    // 4c. Copia le creature (con overrides) nella nuova entry
    $sql_insert_creature = "
        INSERT INTO ci_spellbook_creatures 
        (spellbook_id, creature_id, sort_order, overrides)
        VALUES (?, ?, ?, ?)
    ";
    foreach ($originalCreatures as $creature) {
        // USO DI Database::query() per l'inserimento
        Database::query($sql_insert_creature, [ 
            $newBookId, 
            $creature['creature_id'], 
            $creature['sort_order'], 
            $creature['overrides']
        ]); 
    }

    // Committa la transazione: tutti i dati sono stati copiati
    $pdo->commit(); 

    echo json_encode([
        'success' => true, 
        'message' => "Copia del libro '{$originalBook['name']}' salvata con successo nel tuo Grimorio personale!",
        'newBookId' => $newBookId
    ]);

} catch (Exception $e) {
    // Rollback in caso di errore: annulla tutte le query eseguite
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack(); 
    }
    error_log("Errore nella copia del libro: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Errore nel salvataggio della copia del libro nel database. Riprova più tardi.']);
}
