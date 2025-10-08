<?php
/**
 * Dettaglio Libro - Visualizzazione contenuto
 */

$page_title = 'Dettaglio Libro';

// Ottieni ID libro
$book_id = (int)($_GET['id'] ?? 0);

if (!$book_id) {
    error('Libro non trovato!');
    redirect('/?page=books');
}

// Carica libro
$book = Database::fetch("SELECT * FROM ci_spellbooks WHERE id = ?", [$book_id]);

if (!$book) {
    error('Libro non trovato!');
    redirect('/?page=books');
}

// Verifica permessi
$can_view = false;
$can_edit = false;

// 1. Controlla se l'utente corrente è il PROPRIETARIO del libro
$is_owner = false;
if (isAdmin() && isset($_SESSION['admin_id']) && $book['admin_id'] == $_SESSION['admin_id']) {
    $is_owner = true; // L'admin loggato è il proprietario
} elseif (isUser() && isset($_SESSION['user_id']) && $book['user_id'] == $_SESSION['user_id']) {
    $is_owner = true; // L'utente loggato è il proprietario
}

if ($is_owner) {
    $can_edit = true;
}

// 2. Controlla i permessi di visualizzazione
// Puoi vedere se: sei il proprietario, o il libro è pubblico, o sei un admin (che può vedere tutto)
if ($can_edit || $book['is_public'] || isAdmin()) {
    $can_view = true;
}

if (!$can_view) {
    error('Non hai i permessi per visualizzare questo libro!');
    redirect('/?page=books');
}

// Carica incantesimi
$spells = Database::fetchAll(
    "SELECT s.*, ss.sort_order, ss.notes 
     FROM ci_spellbook_spells ss
     JOIN ci_spells s ON s.id = ss.spell_id
     WHERE ss.spellbook_id = ?
     ORDER BY ss.sort_order ASC, s.level ASC, s.name_it ASC",
    [$book_id]
);

// Carica creature
$creatures = Database::fetchAll(
    "SELECT c.*, sc.sort_order, sc.overrides
     FROM ci_spellbook_creatures sc
     JOIN ci_creatures c ON c.id = sc.creature_id
     WHERE sc.spellbook_id = ?
     ORDER BY sc.sort_order ASC, c.name_it ASC",
    [$book_id]
);

$total_cards = count($spells) + count($creatures);

// Applica override
foreach ($creatures as &$creature) {
    $creature['display_name'] = $creature['name_it'];
    
    if (!empty($creature['overrides'])) {
        $overrides = json_decode($creature['overrides'], true);
        
        if (is_array($overrides)) {
            $creature = array_merge($creature, $overrides);
            if (isset($overrides['name_it'])) {
                $creature['display_name'] = $overrides['name_it'];
            }
        }
    }
}
unset($creature);
?>

<div class="container my-4 flex-grow-1">
    
    <!-- Header Libro -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>
                <i class="bi bi-journal-bookmark-fill text-warning"></i> 
                <?= sanitize($book['name']) ?>
            </h2>
            <?php if ($book['description']): ?>
            <p class="text-muted"><?= sanitize($book['description']) ?></p>
            <?php endif; ?>
            
            <div class="mb-2">
                <span class="badge bg-primary">
                    <i class="bi bi-book-fill"></i> <?= count($spells) ?> Incantesimi
                </span>
                <span class="badge bg-success">
                    <i class="bi bi-bug-fill"></i> <?= count($creatures) ?> Creature
                </span>
                <span class="badge bg-secondary">
                    <i class="bi bi-collection-fill"></i> <?= $total_cards ?> Carte Totali
                </span>
                <?php if ($book['is_public']): ?>
                <span class="badge bg-info">
                    <i class="bi bi-globe"></i> Pubblico
                </span>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="<?= BASE_PATH ?>/?page=print&type=book&id=<?= $book_id ?>" class="btn btn-success mb-2">
                <i class="bi bi-printer-fill"></i> Stampa Tutto
            </a>
            <?php if ($can_edit): ?>
            <a href="<?= BASE_PATH ?>/?page=book_edit&id=<?= $book_id ?>" class="btn btn-warning mb-2">
                <i class="bi bi-pencil-fill"></i> Modifica Contenuto
            </a>
            <?php endif; ?>
            <a href="<?= BASE_PATH ?>/?page=books" class="btn btn-outline-secondary mb-2">
                <i class="bi bi-arrow-left"></i> Torna ai Libri
            </a>
        </div>
    </div>
    
    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" id="bookTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="spells-tab" data-bs-toggle="tab" data-bs-target="#spells-pane" type="button">
                <i class="bi bi-book-fill"></i> Incantesimi (<?= count($spells) ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="creatures-tab" data-bs-toggle="tab" data-bs-target="#creatures-pane" type="button">
                <i class="bi bi-bug-fill"></i> Creature (<?= count($creatures) ?>)
            </button>
        </li>
    </ul>
    
    <!-- Tab Content -->
    <div class="tab-content" id="bookTabContent">
        
        <!-- Tab Incantesimi -->
        <div class="tab-pane fade show active" id="spells-pane" role="tabpanel">
            <?php if (count($spells) > 0): ?>
            <div class="row g-3">
                <?php foreach ($spells as $spell): ?>
                    <?php include 'templates/spell_card.php'; ?>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle-fill"></i> 
                Nessun incantesimo in questo libro. 
                <?php if ($can_edit): ?>
                <a href="<?= BASE_PATH ?>/?page=book_edit&id=<?= $book_id ?>">Aggiungi incantesimi</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Tab Creature -->
        <div class="tab-pane fade" id="creatures-pane" role="tabpanel">
            <?php if (count($creatures) > 0): ?>
            <div class="row g-3">
                <?php foreach ($creatures as $creature): ?>
                    <?php include 'templates/creature_card.php'; ?>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle-fill"></i> 
                Nessuna creatura in questo libro.
                <?php if ($can_edit): ?>
                <a href="<?= BASE_PATH ?>/?page=book_edit&id=<?= $book_id ?>">Aggiungi creature</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
    </div>
</div>

<!-- Modali -->
<?php 
foreach ($spells as $spell) {
    include 'templates/spell_modal.php';
}
foreach ($creatures as $creature) {
    include 'templates/creature_modal.php';
}
?>

