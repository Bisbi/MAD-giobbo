<?php
/**
 * Modifica Contenuto Libro con Filtri Server-Side (Versione Completa)
 */

$page_title = 'Modifica Libro';

// Richiedi login
if (!isAdmin() && !isUser()) {
    error('Devi effettuare il login!');
    redirect('/?page=login');
}

// Ottieni ID libro
$book_id = (int)($_GET['id'] ?? 0);
if (!$book_id) {
    error('Libro non trovato!');
    redirect('/?page=books');
}

// Carica libro e verifica proprietà
$book = Database::fetch("SELECT * FROM ci_spellbooks WHERE id = ?", [$book_id]);
if (!$book) {
    error('Libro non trovato!');
    redirect('/?page=books');
}
$is_owner = false;
if (isAdmin() && isset($_SESSION['admin_id']) && $book['admin_id'] == $_SESSION['admin_id']) {
    $is_owner = true;
} elseif (isUser() && isset($_SESSION['user_id']) && $book['user_id'] == $_SESSION['user_id']) {
    $is_owner = true;
}
if (!$is_owner) {
    error('Non hai i permessi per modificare questo libro!');
    redirect('/?page=books');
}

// ========================================
// AZIONI POST
// ========================================

// Gestione personalizzazione creatura
if (isset($_POST['action']) && $_POST['action'] === 'override_creature') {
    $entry_id = (int)($_POST['entry_id'] ?? 0);
    $creature_id = (int)($_POST['creature_id'] ?? 0);

    // Verifica che l'entry appartenga a questo libro
    $entry = Database::fetch("SELECT id FROM ci_spellbook_creatures WHERE id = ? AND spellbook_id = ?", [$entry_id, $book_id]);
    $original_creature = Database::fetch("SELECT * FROM ci_creatures WHERE id = ?", [$creature_id]);

    if ($entry && $original_creature) {
        $overrides = [];
        $possible_overrides = ['name_it', 'hit_points', 'armor_class', 'challenge_rating', 'actions', 'special_abilities', 'description_it'];
        
        foreach ($possible_overrides as $field) {
            $new_value = trim($_POST[$field] ?? '');
            // Aggiungi all'override solo se è diverso dall'originale
            if ($new_value != $original_creature[$field]) {
                $overrides[$field] = $new_value;
            }
        }
        
        $json_overrides = !empty($overrides) ? json_encode($overrides) : NULL;
        
        Database::query("UPDATE ci_spellbook_creatures SET overrides = ? WHERE id = ?", [$json_overrides, $entry_id]);
        success('Creatura personalizzata con successo!');
    } else {
        error('Errore durante la personalizzazione della creatura.');
    }
    redirect(preg_replace('/&?remove_spell=[^&]*/', '', preg_replace('/&?remove_creature=[^&]*/', '', $_SERVER['REQUEST_URI'])));
}


// Aggiorna dettagli libro
if (isset($_POST['action']) && $_POST['action'] === 'update_details') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        error('Token CSRF non valido!');
    } else {
        $newName = trim($_POST['name'] ?? '');
        $newDescription = trim($_POST['description'] ?? '');
        $isPublic = isset($_POST['is_public']) ? 1 : 0;

        if (empty($newName)) {
            error('Il nome del libro non può essere vuoto!');
        } else {
            Database::query(
                "UPDATE ci_spellbooks SET name = ?, description = ?, is_public = ? WHERE id = ?",
                [$newName, $newDescription, $isPublic, $book_id]
            );
            success('Dettagli del libro aggiornati!');
        }
    }
    redirect(preg_replace('/&?remove_spell=[^&]*/', '', preg_replace('/&?remove_creature=[^&]*/', '', $_SERVER['REQUEST_URI'])));
}

// Aggiungi incantesimo
if (isset($_POST['action']) && $_POST['action'] === 'add_spell') {
    $spell_id = (int)$_POST['spell_id'];
    $exists = Database::fetch("SELECT id FROM ci_spellbook_spells WHERE spellbook_id = ? AND spell_id = ?", [$book_id, $spell_id]);
    if ($exists) {
        error('Incantesimo già presente nel libro!');
    } else {
        Database::query("INSERT INTO ci_spellbook_spells (spellbook_id, spell_id, sort_order) VALUES (?, ?, 0)", [$book_id, $spell_id]);
        success('Incantesimo aggiunto!');
    }
    redirect(preg_replace('/&?remove_spell=[^&]*/', '', preg_replace('/&?remove_creature=[^&]*/', '', $_SERVER['REQUEST_URI'])));
}

// Aggiungi creatura
if (isset($_POST['action']) && $_POST['action'] === 'add_creature') {
    $creature_id = (int)$_POST['creature_id'];
    $exists = Database::fetch("SELECT id FROM ci_spellbook_creatures WHERE spellbook_id = ? AND creature_id = ?", [$book_id, $creature_id]);
    if ($exists) {
        error('Creatura già presente nel libro!');
    } else {
        Database::query("INSERT INTO ci_spellbook_creatures (spellbook_id, creature_id, sort_order) VALUES (?, ?, 0)", [$book_id, $creature_id]);
        success('Creatura aggiunta!');
    }
    redirect(preg_replace('/&?remove_spell=[^&]*/', '', preg_replace('/&?remove_creature=[^&]*/', '', $_SERVER['REQUEST_URI'])));
}

// Rimuovi incantesimo
if (isset($_GET['remove_spell'])) {
    $spell_id = (int)$_GET['remove_spell'];
    Database::query("DELETE FROM ci_spellbook_spells WHERE spellbook_id = ? AND spell_id = ?", [$book_id, $spell_id]);
    success('Incantesimo rimosso!');
    redirect(preg_replace('/&?remove_spell=[^&]*/', '', $_SERVER['REQUEST_URI']));
}

// Rimuovi creatura
if (isset($_GET['remove_creature'])) {
    $creature_id = (int)$_GET['remove_creature'];
    Database::query("DELETE FROM ci_spellbook_creatures WHERE spellbook_id = ? AND creature_id = ?", [$book_id, $creature_id]);
    success('Creatura rimossa!');
    redirect(preg_replace('/&?remove_creature=[^&]*/', '', $_SERVER['REQUEST_URI']));
}


// ## 1. LEGGERE I PARAMETRI DEL FILTRO DALL'URL ##
$search_spells = $_GET['search_spells'] ?? '';
$level_filter = $_GET['level'] ?? '';
$class_filter = $_GET['class'] ?? '';
$school_filter = $_GET['school'] ?? '';

$search_creatures = $_GET['search_creatures'] ?? '';
$type_filter = $_GET['type'] ?? '';
$size_filter = $_GET['size'] ?? '';
$active_tab = $_GET['active_tab'] ?? 'spells';

// Carica opzioni per i menu a tendina dei filtri
$spell_classes = ['bardo', 'chierico', 'druido', 'mago', 'paladino', 'ranger', 'stregone', 'warlock'];
$spell_schools = Database::fetchAll("SELECT DISTINCT school FROM ci_spells WHERE school IS NOT NULL AND school != '' ORDER BY school");
$creature_types = Database::fetchAll("SELECT DISTINCT creature_type FROM ci_creatures WHERE creature_type IS NOT NULL AND creature_type != '' ORDER BY creature_type");
$creature_sizes = ['Minuscola', 'Piccola', 'Media', 'Grande', 'Enorme', 'Mastodontica'];

// ========================================
// CARICA DATI
// ========================================

$book_spells = Database::fetchAll("SELECT s.* FROM ci_spellbook_spells ss JOIN ci_spells s ON s.id = ss.spell_id WHERE ss.spellbook_id = ? ORDER BY s.level ASC, s.name_it ASC", [$book_id]);
$book_creatures = Database::fetchAll(
    "SELECT c.*, sc.id as entry_id, sc.overrides 
     FROM ci_spellbook_creatures sc 
     JOIN ci_creatures c ON c.id = sc.creature_id 
     WHERE sc.spellbook_id = ? 
     ORDER BY c.name_it ASC", 
    [$book_id]
);


// --- INCANTESIMI DISPONIBILI (filtrati) ---
$book_spell_ids = count($book_spells) > 0 ? array_column($book_spells, 'id') : [0];
$params_spells = $book_spell_ids;
$where_spells = [];
$placeholders = implode(',', array_fill(0, count($book_spell_ids), '?'));
$where_spells[] = "id NOT IN ($placeholders)";

if ($search_spells) {
    $where_spells[] = '(name_it LIKE ?)';
    $params_spells[] = '%' . $search_spells . '%';
}
if ($level_filter !== '') {
    $where_spells[] = 'level = ?';
    $params_spells[] = $level_filter;
}
if ($class_filter) {
    $where_spells[] = 'classes LIKE ?';
    $params_spells[] = '%' . $class_filter . '%';
}
if ($school_filter) {
    $where_spells[] = 'school = ?';
    $params_spells[] = $school_filter;
}
$where_clause_spells = implode(' AND ', $where_spells);
$all_spells = Database::fetchAll("SELECT * FROM ci_spells WHERE active = 1 AND $where_clause_spells ORDER BY level ASC, name_it ASC", $params_spells);

// --- CREATURE DISPONIBILI (filtrate) ---
$book_creature_ids = count($book_creatures) > 0 ? array_column($book_creatures, 'id') : [0];
$params_creatures = $book_creature_ids;
$where_creatures = [];
$placeholders = implode(',', array_fill(0, count($book_creature_ids), '?'));
$where_creatures[] = "id NOT IN ($placeholders)";

if ($search_creatures) {
    $where_creatures[] = '(name_it LIKE ?)';
    $params_creatures[] = '%' . $search_creatures . '%';
}
if ($type_filter) {
    $where_creatures[] = 'creature_type = ?';
    $params_creatures[] = $type_filter;
}
if ($size_filter) {
    $where_creatures[] = 'size = ?';
    $params_creatures[] = $size_filter;
}
$where_clause_creatures = implode(' AND ', $where_creatures);
$all_creatures = Database::fetchAll("SELECT * FROM ci_creatures WHERE active = 1 AND $where_clause_creatures ORDER BY name_it ASC", $params_creatures);
?>

<div class="container mt-4">
    
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="bi bi-pencil-fill text-warning"></i> Modifica: <?= sanitize($book['name']) ?></h2>
            <p class="text-muted">Aggiungi o rimuovi incantesimi e creature</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="<?= BASE_PATH ?>/?page=book_detail&id=<?= $book_id ?>" class="btn btn-outline-primary"><i class="bi bi-eye-fill"></i> Anteprima</a>
            <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#editBookDetailsModal"><i class="bi bi-gear-fill"></i> Modifica Dettagli</button>
            <a href="<?= BASE_PATH ?>/?page=books" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Torna ai Libri</a>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-6"><div class="card bg-primary text-white"><div class="card-body text-center"><h3><?= count($book_spells) ?></h3><p class="mb-0"><i class="bi bi-book-fill"></i> Incantesimi nel libro</p></div></div></div>
        <div class="col-md-6"><div class="card bg-success text-white"><div class="card-body text-center"><h3><?= count($book_creatures) ?></h3><p class="mb-0"><i class="bi bi-bug-fill"></i> Creature nel libro</p></div></div></div>
    </div>
    
    <ul class="nav nav-tabs mb-4" id="editTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= $active_tab === 'spells' ? 'active' : '' ?>" id="manage-spells-tab" data-bs-toggle="tab" data-bs-target="#manage-spells" type="button">
            <i class="bi bi-book-fill"></i> Gestisci Incantesimi
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= $active_tab === 'creatures' ? 'active' : '' ?>" id="manage-creatures-tab" data-bs-toggle="tab" data-bs-target="#manage-creatures" type="button">
            <i class="bi bi-bug-fill"></i> Gestisci Creature
        </button>
    </li>
</ul>
    
    <div class="tab-content" id="editTabContent">
        
        <div class="tab-pane fade <?= $active_tab === 'spells' ? 'show active' : '' ?>" id="manage-spells" role="tabpanel">
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="bi bi-book-fill"></i> Nel Libro (<?= count($book_spells) ?>)</h5></div>
                        <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                            <?php if (count($book_spells) > 0): ?>
                            <div class="list-group">
                                <?php foreach ($book_spells as $spell): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= sanitize($spell['name_it']) ?></strong><br>
                                        <small class="text-muted">Livello <?= $spell['level'] ?> - <?= sanitize($spell['school']) ?></small>
                                    </div>
                                    <a href="?page=book_edit&id=<?= $book_id ?>&remove_spell=<?= $spell['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Rimuovere questo incantesimo?')"><i class="bi bi-trash-fill"></i></a>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info mb-0">Nessun incantesimo nel libro.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-success text-white"><h5 class="mb-0"><i class="bi bi-plus-circle-fill"></i> Aggiungi Incantesimi</h5></div>
                        <div class="card-body">
                            <form method="GET" class="mb-3">
                                <input type="hidden" name="page" value="book_edit">
                                <input type="hidden" name="id" value="<?= $book_id ?>">
								<input type="hidden" name="active_tab" value="spells">
                                <div class="row g-2 align-items-center">
                                    <div class="col-12"><input type="text" name="search_spells" class="form-control form-control-sm" placeholder="Cerca per nome..." value="<?= sanitize($search_spells) ?>"></div>
                                    <div class="col-sm-4"><select name="level" class="form-select form-select-sm"><option value="">Livello</option><?php foreach(range(0,9) as $lv) echo "<option value='$lv'" . ($level_filter===(string)$lv?'selected':'') . ">" . ($lv==0?'Trucchetto':"Liv. $lv") . "</option>"; ?></select></div>
                                    <div class="col-sm-4"><select name="class" class="form-select form-select-sm"><option value="">Classe</option><?php foreach($spell_classes as $cls) echo "<option value='$cls'" . ($class_filter===$cls?'selected':'') . ">" . ucfirst($cls) . "</option>"; ?></select></div>
                                    <div class="col-sm-4"><select name="school" class="form-select form-select-sm"><option value="">Scuola</option><?php foreach($spell_schools as $sch) echo "<option value='{$sch['school']}'" . ($school_filter===$sch['school']?'selected':'') . ">{$sch['school']}</option>"; ?></select></div>
                                </div>
                                <div class="mt-2">
                                    <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-funnel-fill"></i> Filtra</button>
                                    <a href="?page=book_edit&id=<?= $book_id ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-circle"></i> Reset</a>
                                </div>
                            </form>
                            <div style="max-height: 450px; overflow-y: auto;">
                                <?php if (count($all_spells) > 0): ?>
                                <div class="list-group">
                                    <?php foreach ($all_spells as $spell): ?>
                                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                            <div style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#spellModal<?= $spell['id'] ?>" class="flex-grow-1">
                                                <strong><?= sanitize($spell['name_it']) ?></strong><br>
                                                <small class="text-muted">Livello <?= $spell['level'] ?> - <?= sanitize($spell['school']) ?></small>
                                            </div>
                                            <form method="POST" action="<?= htmlspecialchars(preg_replace('/&?remove_spell=[^&]*/', '', preg_replace('/&?remove_creature=[^&]*/', '', $_SERVER['REQUEST_URI']))) ?>" class="ms-2">
                                                <input type="hidden" name="action" value="add_spell">
                                                <input type="hidden" name="spell_id" value="<?= $spell['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-plus-lg"></i></button>
                                            </form>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php else: ?>
                                <div class="alert alert-info small"><i class="bi bi-info-circle-fill"></i> Nessun incantesimo trovato con i filtri attuali.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="tab-pane fade <?= $active_tab === 'creatures' ? 'show active' : '' ?>" id="manage-creatures" role="tabpanel">
            <div class="row">
                 <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-success text-white"><h5 class="mb-0"><i class="bi bi-bug-fill"></i> Nel Libro (<?= count($book_creatures) ?>)</h5></div>
                        <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                            <?php if (count($book_creatures) > 0): ?>
                            <div class="list-group">
                                <?php foreach ($book_creatures as $book_creature): 
                                    $overrides = !empty($book_creature['overrides']) ? json_decode($book_creature['overrides'], true) : [];
                                    $display_name = $overrides['name_it'] ?? $book_creature['name_it'];
                                ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= sanitize($display_name) ?></strong>
                                        <?php if(!empty($overrides)): ?><span class="badge bg-warning text-dark ms-1">P</span><?php endif; ?><br>
                                        <small class="text-muted"><?= sanitize(ucfirst($book_creature['creature_type'])) ?> - <?= sanitize($book_creature['size']) ?></small>
                                    </div>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#overrideCreatureModal_<?= $book_creature['entry_id'] ?>" title="Personalizza"><i class="bi bi-person-fill-gear"></i></button>
                                        <a href="?page=book_edit&id=<?= $book_id ?>&remove_creature=<?= $book_creature['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Rimuovere questa creatura?')" title="Rimuovi"><i class="bi bi-trash-fill"></i></a>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info mb-0">Nessuna creatura nel libro.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-warning text-dark"><h5 class="mb-0"><i class="bi bi-plus-circle-fill"></i> Aggiungi Creature</h5></div>
                        <div class="card-body">
                            <form method="GET" class="mb-3">
                                <input type="hidden" name="page" value="book_edit">
                                <input type="hidden" name="id" value="<?= $book_id ?>">
								<input type="hidden" name="active_tab" value="creatures">
                                <div class="row g-2 align-items-center">
                                    <div class="col-12"><input type="text" name="search_creatures" class="form-control form-control-sm" placeholder="Cerca per nome..." value="<?= sanitize($search_creatures) ?>"></div>
                                    <div class="col-6"><select name="type" class="form-select form-select-sm"><option value="">Tipo</option><?php foreach($creature_types as $type) echo "<option value='{$type['creature_type']}'" . ($type_filter===$type['creature_type']?'selected':'') . ">" . ucfirst($type['creature_type']) . "</option>"; ?></select></div>
                                    <div class="col-6"><select name="size" class="form-select form-select-sm"><option value="">Taglia</option><?php foreach($creature_sizes as $size) echo "<option value='$size'" . ($size_filter===$size?'selected':'') . ">$size</option>"; ?></select></div>
                                </div>
                                <div class="mt-2">
                                    <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-funnel-fill"></i> Filtra</button>
                                    <a href="?page=book_edit&id=<?= $book_id ?>&active_tab=creatures" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-circle"></i> Reset</a>
                                </div>
                            </form>
                            <div style="max-height: 450px; overflow-y: auto;">
                                <?php if (count($all_creatures) > 0): ?>
                                <div class="list-group">
                                    <?php foreach ($all_creatures as $creature): ?>
                                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                            <div style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#creatureModal<?= $creature['id'] ?>" class="flex-grow-1">
                                                <strong><?= sanitize($creature['name_it']) ?></strong><br>
                                                <small class="text-muted"><?= sanitize(ucfirst($creature['creature_type'])) ?> - <?= sanitize($creature['size']) ?></small>
                                            </div>
                                            <form method="POST" action="<?= htmlspecialchars(preg_replace('/&?remove_spell=[^&]*/', '', preg_replace('/&?remove_creature=[^&]*/', '', $_SERVER['REQUEST_URI']))) ?>" class="ms-2">
                                                <input type="hidden" name="action" value="add_creature">
                                                <input type="hidden" name="creature_id" value="<?= $creature['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-plus-lg"></i></button>
                                            </form>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php else: ?>
                                <div class="alert alert-info small"><i class="bi bi-info-circle-fill"></i> Nessuna creatura trovata con i filtri attuali.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editBookDetailsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="update_details">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-gear-fill"></i> Modifica Dettagli Libro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nome Libro</label>
                        <input type="text" class="form-control" name="name" value="<?= sanitize($book['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrizione</label>
                        <textarea class="form-control" name="description" rows="3"><?= sanitize($book['description']) ?></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_public" <?= $book['is_public'] ? 'checked' : '' ?>>
                        <label class="form-check-label">Rendi questo libro pubblico</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill"></i> Salva Modifiche</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
// Modali Dettaglio
foreach ($all_spells as $spell) { include 'templates/spell_modal.php'; }
foreach ($all_creatures as $creature) { include 'templates/creature_modal.php'; }
// Modali Personalizzazione Creatura
foreach ($book_creatures as $book_creature) {
    $creature = $book_creature; // Il template usa la variabile $creature
    include 'templates/creature_override_modal.php';
}
?>

