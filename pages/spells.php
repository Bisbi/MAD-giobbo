<?php
/**
 * Pagina Incantesimi - Visualizzazione con filtri
 */

$page_title = 'Incantesimi';

// GESTIONE AZIONI POST (per aggiunta a libro)
if ((isUser() || isAdmin()) && isset($_POST['action']) && $_POST['action'] === 'add_to_book') {
    $spell_id = (int)($_POST['spell_id'] ?? 0);
    $book_id = (int)($_POST['book_id'] ?? 0);
    $owner_id = isAdmin() ? $_SESSION['admin_id'] : $_SESSION['user_id'];
    
    // Verifica che il libro appartenga all'utente/admin
    $owner_type = isAdmin() ? 'admin' : 'user';
    $book = Database::fetch("SELECT id FROM ci_spellbooks WHERE id = ? AND {$owner_type}_id = ?", [$book_id, $owner_id]);

    if ($spell_id && $book) {
        $exists = Database::fetch("SELECT id FROM ci_spellbook_spells WHERE spellbook_id = ? AND spell_id = ?", [$book_id, $spell_id]);
        if (!$exists) {
            Database::query("INSERT INTO ci_spellbook_spells (spellbook_id, spell_id) VALUES (?, ?)", [$book_id, $spell_id]);
            success('Incantesimo aggiunto al libro!');
        } else {
            error('Incantesimo già presente nel libro.');
        }
    } else {
        error('Azione non permessa o libro non trovato.');
    }
    // Ricarica la pagina senza i dati POST
    redirect('/?page=spells');
}


// Parametri filtro
$level_filter = $_GET['level'] ?? '';
$class_filter = $_GET['class'] ?? '';
$school_filter = $_GET['school'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'level';
$order = $_GET['order'] ?? 'ASC';

// Costruisci query
$where = ['active = 1'];
$params = [];

if ($level_filter !== '') {
    $where[] = 'level = ?';
    $params[] = $level_filter;
}

if ($class_filter) {
    $where[] = 'classes LIKE ?';
    $params[] = '%' . $class_filter . '%';
}

if ($school_filter) {
    $where[] = 'school = ?';
    $params[] = $school_filter;
}

if ($search) {
    $where[] = '(name_it LIKE ? OR name_en LIKE ? OR description_it LIKE ?)';
    $search_term = '%' . $search . '%';
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_clause = implode(' AND ', $where);

// Ottieni incantesimi
$sql = "SELECT * FROM ci_spells WHERE $where_clause ORDER BY $sort $order";
$spells = Database::fetchAll($sql, $params);

// Prepara IDs per la stampa
$spell_ids_for_print = [];
if (!empty($spells)) {
    $spell_ids_for_print = array_column($spells, 'id');
}

// Carica opzioni filtri e libri utente (se loggato)
$user_books = [];
if(isUser() || isAdmin()){
    $owner_id = isAdmin() ? $_SESSION['admin_id'] : $_SESSION['user_id'];
    $owner_type = isAdmin() ? 'admin' : 'user';
    $user_books = Database::fetchAll("SELECT id, name FROM ci_spellbooks WHERE {$owner_type}_id = ? ORDER BY name", [$owner_id]);
}
$levels = range(0, 9);
$classes = ['bardo', 'chierico', 'druido', 'mago', 'paladino', 'ranger', 'stregone', 'warlock'];
$schools = Database::fetchAll("SELECT DISTINCT school FROM ci_spells WHERE school IS NOT NULL AND school != '' ORDER BY school");
?>

<div class="container mt-4">
    
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h2><i class="bi bi-book-fill text-primary"></i> Incantesimi D&D 5e</h2>
            <p class="text-muted">Sfoglia e stampa le carte degli incantesimi</p>
        </div>
        <div class="col-md-6 text-md-end">
            <?php if (isAdmin()): ?>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#spellFormModalAdd">
                <i class="bi bi-plus-circle-fill"></i> Aggiungi Incantesimo
            </button>
            <?php endif; ?>
            <?php if(!empty($spell_ids_for_print)): ?>
                <a href="<?= BASE_PATH ?>/?page=print&type=spells&ids=<?= implode(',', $spell_ids_for_print) ?>" class="btn btn-success" target="_blank">
                    <i class="bi bi-printer-fill"></i> Stampa Selezione
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Filtri -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= BASE_PATH ?>/">
                <input type="hidden" name="page" value="spells">
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label"><i class="bi bi-search"></i> Ricerca</label><input type="text" name="search" class="form-control" placeholder="Nome o descrizione..." value="<?= sanitize($search) ?>"></div>
                    <div class="col-md-2"><label class="form-label"><i class="bi bi-star-fill"></i> Livello</label><select name="level" class="form-select"><option value="">Tutti</option><?php foreach ($levels as $lv): ?><option value="<?= $lv ?>" <?= $level_filter === (string)$lv ? 'selected' : '' ?>><?= $lv == 0 ? 'Trucchetto' : "Livello $lv" ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-3"><label class="form-label"><i class="bi bi-people-fill"></i> Classe</label><select name="class" class="form-select"><option value="">Tutte</option><?php foreach ($classes as $cls): ?><option value="<?= $cls ?>" <?= $class_filter === $cls ? 'selected' : '' ?>><?= ucfirst($cls) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-3"><label class="form-label"><i class="bi bi-compass-fill"></i> Scuola</label><select name="school" class="form-select"><option value="">Tutte</option><?php foreach ($schools as $sch): ?><option value="<?= $sch['school'] ?>" <?= $school_filter === $sch['school'] ? 'selected' : '' ?>><?= $sch['school'] ?></option><?php endforeach; ?></select></div>
                </div>
                <div class="row mt-3"><div class="col"><button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill"></i> Filtra</button><a href="<?= BASE_PATH ?>/?page=spells" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i> Reset</a></div></div>
            </form>
        </div>
    </div>
    
    <div class="row mb-3"><div class="col"><p class="text-muted"><i class="bi bi-info-circle-fill"></i> Trovati <strong><?= count($spells) ?></strong> incantesimi</p></div></div>
    
    <div class="row g-3">
        <?php foreach ($spells as $spell): ?>
                <?php include 'templates/spell_card.php'; ?>
        <?php endforeach; ?>
    </div>
    
    <?php if (count($spells) == 0): ?>
        <div class="alert alert-info"><i class="bi bi-info-circle-fill"></i> Nessun incantesimo trovato con i filtri selezionati.</div>
    <?php endif; ?>
    
</div>

<?php 
// Includi tutti i modal
foreach ($spells as $spell) {
    include 'templates/spell_modal.php';
}
// Includi il modal per l'aggiunta (solo per admin)
if (isAdmin()) {
    $spell = null; // Resetta la variabile per il template
    include 'templates/spell_form_modal.php';
}
?>

