<?php
/**
 * Pagina Creature - Visualizzazione con filtri
 */

$page_title = 'Creature';

// GESTIONE AZIONI POST (per aggiunta a libro)
if ((isUser() || isAdmin()) && isset($_POST['action']) && $_POST['action'] === 'add_to_book') {
    $creature_id = (int)($_POST['creature_id'] ?? 0);
    $book_id = (int)($_POST['book_id'] ?? 0);
    $owner_id = isAdmin() ? $_SESSION['admin_id'] : $_SESSION['user_id'];
    
    $owner_type = isAdmin() ? 'admin' : 'user';
    $book = Database::fetch("SELECT id FROM ci_spellbooks WHERE id = ? AND {$owner_type}_id = ?", [$book_id, $owner_id]);

    if ($creature_id && $book) {
        $exists = Database::fetch("SELECT id FROM ci_spellbook_creatures WHERE spellbook_id = ? AND creature_id = ?", [$book_id, $creature_id]);
        if (!$exists) {
            Database::query("INSERT INTO ci_spellbook_creatures (spellbook_id, creature_id) VALUES (?, ?)", [$book_id, $creature_id]);
            success('Creatura aggiunta al libro!');
        } else {
            error('Creatura già presente nel libro.');
        }
    } else {
        error('Azione non permessa o libro non trovato.');
    }
    redirect('/?page=creatures');
}

// Parametri filtro
$type_filter = $_GET['type'] ?? '';
$size_filter = $_GET['size'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'name_it';
$order = $_GET['order'] ?? 'ASC';

// Costruisci query
$where = ['active = 1'];
$params = [];
if ($type_filter) { $where[] = 'creature_type = ?'; $params[] = $type_filter; }
if ($size_filter) { $where[] = 'size = ?'; $params[] = $size_filter; }
if ($search) {
    $where[] = '(name_it LIKE ? OR name_en LIKE ? OR description_it LIKE ?)';
    $search_term = '%' . $search . '%';
    $params = array_merge($params, [$search_term, $search_term, $search_term]);
}
$where_clause = implode(' AND ', $where);
$sql = "SELECT * FROM ci_creatures WHERE $where_clause ORDER BY $sort $order";
$creatures = Database::fetchAll($sql, $params);

// Prepara IDs per la stampa
$creature_ids_for_print = [];
if (!empty($creatures)) {
    $creature_ids_for_print = array_column($creatures, 'id');
}

// Carica opzioni filtri e libri utente
$user_books = [];
if(isUser() || isAdmin()){
    $owner_id = isAdmin() ? $_SESSION['admin_id'] : $_SESSION['user_id'];
    $owner_type = isAdmin() ? 'admin' : 'user';
    $user_books = Database::fetchAll("SELECT id, name FROM ci_spellbooks WHERE {$owner_type}_id = ? ORDER BY name", [$owner_id]);
}
$types = Database::fetchAll("SELECT DISTINCT creature_type FROM ci_creatures WHERE creature_type IS NOT NULL AND creature_type != '' ORDER BY creature_type");
$sizes = ['Minuscola', 'Piccola', 'Media', 'Grande', 'Enorme', 'Mastodontica'];
?>

<div class="container mt-4">
    
    <div class="row mb-4">
        <div class="col-md-6">
            <h2><i class="bi bi-bug-fill text-success"></i> Creature D&D 5e</h2>
            <p class="text-muted">Sfoglia il bestiario e gestisci le tue creature.</p>
        </div>
        <div class="col-md-6 text-md-end">
            <?php if (isAdmin()): ?>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#creatureFormModalAdd">
                <i class="bi bi-plus-circle-fill"></i> Aggiungi Creatura
            </button>
            <?php endif; ?>
            <?php if(!empty($creature_ids_for_print)): ?>
                <a href="<?= BASE_PATH ?>/?page=print&type=creatures&ids=<?= implode(',', $creature_ids_for_print) ?>" class="btn btn-success" target="_blank">
                    <i class="bi bi-printer-fill"></i> Stampa Selezione
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= BASE_PATH ?>/">
                <input type="hidden" name="page" value="creatures">
                <div class="row g-3">
                    <div class="col-md-5"><label class="form-label"><i class="bi bi-search"></i> Ricerca</label><input type="text" name="search" class="form-control" placeholder="Nome o descrizione..." value="<?= sanitize($search) ?>"></div>
                    <div class="col-md-4"><label class="form-label"><i class="bi bi-tag-fill"></i> Tipo</label><select name="type" class="form-select"><option value="">Tutti i Tipi</option><?php foreach ($types as $type): ?><option value="<?= $type['creature_type'] ?>" <?= $type_filter === $type['creature_type'] ? 'selected' : '' ?>><?= ucfirst($type['creature_type']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-3"><label class="form-label"><i class="bi bi-arrows-angle-expand"></i> Taglia</label><select name="size" class="form-select"><option value="">Tutte le Taglie</option><?php foreach ($sizes as $size): ?><option value="<?= $size ?>" <?= $size_filter === $size ? 'selected' : '' ?>><?= $size ?></option><?php endforeach; ?></select></div>
                </div>
                <div class="row mt-3"><div class="col"><button type="submit" class="btn btn-success"><i class="bi bi-funnel-fill"></i> Filtra</button><a href="<?= BASE_PATH ?>/?page=creatures" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i> Reset</a></div></div>
            </form>
        </div>
    </div>
    
    <div class="row mb-3"><div class="col"><p class="text-muted"><i class="bi bi-info-circle-fill"></i> Trovate <strong><?= count($creatures) ?></strong> creature</p></div></div>
    
    <div class="row g-3">
        <?php foreach ($creatures as $creature): ?>
            <?php include 'templates/creature_card.php'; ?>
        <?php endforeach; ?>
    </div>
    
    <?php if (count($creatures) == 0): ?>
        <div class="alert alert-info"><i class="bi bi-info-circle-fill"></i> Nessuna creatura trovata con i filtri selezionati.</div>
    <?php endif; ?>
    
</div>

<?php 
// Includi tutti i modal
foreach ($creatures as $creature) {
    include 'templates/creature_modal.php';
}
// Includi il modal per l'aggiunta (solo per admin)
if (isAdmin()) {
    $creature = null; // Resetta la variabile per il template
    include 'templates/creature_form_modal.php';
}
?>

