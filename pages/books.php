<?php
/**
 * Pagina Libri - Gestione collezioni personali
 */

$page_title = 'Libri';

// Richiedi login
if (!isAdmin() && !isUser()) {
    error('Devi effettuare il login per gestire i libri!');
    redirect('/?page=login');
}

// Ottieni ID utente/admin
$owner_id = isAdmin() ? $_SESSION['admin_id'] : $_SESSION['user_id'];
$owner_type = isAdmin() ? 'admin' : 'user';

// ========================================
// AZIONI
// ========================================

// Crea nuovo libro
if (isset($_POST['action']) && $_POST['action'] === 'create') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $is_public = isset($_POST['is_public']) ? 1 : 0;
    
    if (!$name) {
        error('Inserisci un nome per il libro!');
    } else {
        $share_token = bin2hex(random_bytes(16));
        
        if (isAdmin()) {
            Database::query(
                "INSERT INTO ci_spellbooks (admin_id, owner_type, name, description, is_public, share_token, created_at) 
                 VALUES (?, 'admin', ?, ?, ?, ?, NOW())",
                [$owner_id, $name, $description, $is_public, $share_token]
            );
        } else {
            Database::query(
                "INSERT INTO ci_spellbooks (user_id, owner_type, name, description, is_public, share_token, created_at) 
                 VALUES (?, 'user', ?, ?, ?, ?, NOW())",
                [$owner_id, $name, $description, $is_public, $share_token]
            );
        }
        
        success('Libro creato con successo!');
        redirect('/?page=books');
    }
}

// Elimina libro
if (isset($_GET['delete'])) {
    $book_id = (int)$_GET['delete'];
    
    // Verifica proprietà
    if (isAdmin()) {
        $book = Database::fetch("SELECT * FROM ci_spellbooks WHERE id = ? AND admin_id = ?", [$book_id, $owner_id]);
    } else {
        $book = Database::fetch("SELECT * FROM ci_spellbooks WHERE id = ? AND user_id = ?", [$book_id, $owner_id]);
    }
    
    if ($book) {
        // Elimina contenuto
        Database::query("DELETE FROM ci_spellbook_spells WHERE spellbook_id = ?", [$book_id]);
        Database::query("DELETE FROM ci_spellbook_creatures WHERE spellbook_id = ?", [$book_id]);
        Database::query("DELETE FROM ci_spellbooks WHERE id = ?", [$book_id]);
        
        success('Libro eliminato!');
    } else {
        error('Libro non trovato o non autorizzato!');
    }
    
    redirect('/?page=books');
}

/// ========================================
// CARICA LIBRI
// ========================================

if (isAdmin()) {
    // L'admin, come da richiesta, vede solo i propri libri
    $books = Database::fetchAll(
        "SELECT * FROM ci_spellbooks WHERE admin_id = ? ORDER BY created_at DESC",
        [$owner_id]
    );
} else {
    // L'utente vede i propri libri (pubblici e privati) + tutti i libri pubblici degli altri
    $books = Database::fetchAll(
        "SELECT sb.*, u.display_name as owner_name
         FROM ci_spellbooks sb
         LEFT JOIN ci_users u ON sb.user_id = u.id
         WHERE (sb.user_id = ?) OR (sb.is_public = 1)
         ORDER BY sb.created_at DESC",
        [$owner_id]
    );
}

// Suddivide i libri in "miei" e "pubblici di altri" e conta le carte
$my_books = [];
$public_books = [];
// Identifica l'ID dell'utente o admin loggato
$current_owner_id = isAdmin() ? ($_SESSION['admin_id'] ?? null) : ($_SESSION['user_id'] ?? null);

foreach ($books as &$book) {
    // Conta incantesimi e creature
    $spell_count = Database::fetch("SELECT COUNT(*) as count FROM ci_spellbook_spells WHERE spellbook_id = ?", [$book['id']])['count'] ?? 0;
    $creature_count = Database::fetch("SELECT COUNT(*) as count FROM ci_spellbook_creatures WHERE spellbook_id = ?", [$book['id']])['count'] ?? 0;
    
    // ##### RIGHE MANCANTI DA AGGIUNGERE #####
    $book['spell_count'] = $spell_count;
    $book['creature_count'] = $creature_count;
    // ##### FINE RIGHE MANCANTI #####

    $book['total_cards'] = $spell_count + $creature_count;

    // Controlla se il libro appartiene all'utente loggato
    $is_my_book = (isUser() && isset($book['user_id']) && $book['user_id'] == $current_owner_id) || (isAdmin() && isset($book['admin_id']) && $book['admin_id'] == $current_owner_id);

    if ($is_my_book) {
        $my_books[] = $book;
    } else {
        $public_books[] = $book;
    }
}
unset($book);
?>

<div class="container mt-4">
    
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h2><i class="bi bi-journals text-warning"></i> I Miei Libri</h2>
            <p class="text-muted">Gestisci le tue collezioni personali</p>
        </div>
        <div class="col-md-6 text-md-end">
			    <a href="?page=profile" class="btn btn-outline-secondary">
					<i class="bi bi-person-circle"></i> Il Mio Profilo
				</a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBookModal">
                <i class="bi bi-plus-circle-fill"></i> Nuovo Libro
            </button>
        </div>
    </div>
    
    <!-- Lista Libri -->
<h4 class="mb-3"><i class="bi bi-person-badge"></i> I Miei Libri</h4>
<?php if (count($my_books) > 0): ?>
    <div class="row g-3">
        <?php foreach ($my_books as $book): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 d-flex flex-column">
                    <div class="card-header bg-warning text-dark">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="card-title mb-0"><?= sanitize($book['name']) ?></h5>
                            <?php if ($book['is_public']): ?>
                            <span class="badge bg-success" title="Libro pubblico"><i class="bi bi-globe"></i></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($book['description']): ?>
                        <small><?= sanitize($book['description']) ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="row text-center mb-3">
                            <div class="col-6"><h4 class="text-primary mb-0"><?= $book['spell_count'] ?></h4><small class="text-muted">Incantesimi</small></div>
                            <div class="col-6"><h4 class="text-success mb-0"><?= $book['creature_count'] ?></h4><small class="text-muted">Creature</small></div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted"><i class="bi bi-calendar-fill"></i> Creato il: <?= date('d/m/Y', strtotime($book['created_at'])) ?></small>
                        </div>
                        <?php if ($book['is_public']): ?>
                        <div class="input-group input-group-sm mt-auto">
                            <input type="text" class="form-control" value="<?= BASE_PATH ?>/?page=view&token=<?= $book['share_token'] ?>" readonly id="shareLink<?= $book['id'] ?>">
                            <button class="btn btn-outline-secondary" type="button" onclick="copyLink(<?= $book['id'] ?>)"><i class="bi bi-clipboard"></i></button>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer mt-auto">
                        <div class="btn-group w-100">
                            <a href="<?= BASE_PATH ?>/?page=book_detail&id=<?= $book['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye-fill"></i> Vedi</a>
                            <a href="<?= BASE_PATH ?>/?page=book_edit&id=<?= $book['id'] ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil-fill"></i> Modifica</a>
                            <a href="<?= BASE_PATH ?>/?page=books&delete=<?= $book['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Eliminare questo libro?')"><i class="bi bi-trash-fill"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="text-center py-5">
        <i class="bi bi-journals text-muted" style="font-size: 5rem;"></i>
        <h4 class="mt-3">Nessun libro personale creato</h4>
        <p class="text-muted">Inizia creando il tuo primo libro di incantesimi!</p>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBookModal"><i class="bi bi-plus-circle-fill"></i> Crea Primo Libro</button>
    </div>
<?php endif; ?>

<?php if (count($public_books) > 0): ?>
    <hr class="my-5">
    <h4 class="mb-3"><i class="bi bi-globe"></i> Libri Pubblici della Community</h4>
    <div class="row g-3">
        <?php foreach ($public_books as $book): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 d-flex flex-column">
                    <div class="card-header bg-light">
                         <div class="d-flex justify-content-between align-items-start">
                            <h5 class="card-title mb-0"><?= sanitize($book['name']) ?></h5>
                            <span class="badge bg-success" title="Libro pubblico"><i class="bi bi-globe"></i></span>
                        </div>
                        <?php if ($book['description']): ?>
                        <small class="text-muted"><?= sanitize($book['description']) ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                         <div class="row text-center mb-3">
                            <div class="col-6"><h4 class="text-primary mb-0"><?= $book['spell_count'] ?></h4><small class="text-muted">Incantesimi</small></div>
                            <div class="col-6"><h4 class="text-success mb-0"><?= $book['creature_count'] ?></h4><small class="text-muted">Creature</small></div>
                        </div>
                        <div class="mb-2">
                             <small class="text-muted"><i class="bi bi-person-circle"></i> Di: <strong><?= sanitize($book['owner_name'] ?? 'Sconosciuto') ?></strong></small>
                        </div>
                         <div class="input-group input-group-sm mt-auto">
                           <input type="text" class="form-control" value="<?= BASE_PATH ?>/?page=view&token=<?= $book['share_token'] ?>" readonly id="shareLink<?= $book['id'] ?>">
                           <button class="btn btn-outline-secondary" type="button" onclick="copyLink(<?= $book['id'] ?>)"><i class="bi bi-clipboard"></i></button>
                       </div>
                    </div>
                    <div class="card-footer mt-auto">
                        <a href="<?= BASE_PATH ?>/?page=book_detail&id=<?= $book['id'] ?>" class="btn btn-sm btn-outline-primary w-100"><i class="bi bi-eye-fill"></i> Vedi Contenuto</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
    
</div>

<!-- Modal Crea Libro -->
<div class="modal fade" id="createBookModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle-fill"></i> Crea Nuovo Libro
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <div class="modal-body">
                    <!-- Nome -->
                    <div class="mb-3">
                        <label for="book_name" class="form-label">
                            Nome Libro <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="book_name" 
                               name="name" 
                               placeholder="Es: Incantesimi Mago Livello 1"
                               required>
                    </div>
                    
                    <!-- Descrizione -->
                    <div class="mb-3">
                        <label for="book_description" class="form-label">
                            Descrizione (opzionale)
                        </label>
                        <textarea class="form-control" 
                                  id="book_description" 
                                  name="description" 
                                  rows="3"
                                  placeholder="Breve descrizione del libro..."></textarea>
                    </div>
                    
                    <!-- Pubblico -->
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="is_public" 
                               name="is_public">
                        <label class="form-check-label" for="is_public">
                            <i class="bi bi-globe"></i> Libro pubblico (genera link condivisione)
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save-fill"></i> Crea Libro
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Copia link condivisione
function copyLink(bookId) {
    const input = document.getElementById('shareLink' + bookId);
    input.select();
    document.execCommand('copy');
    
    // Feedback visivo
    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-check-circle-fill"></i>';
    setTimeout(() => {
        btn.innerHTML = originalHTML;
    }, 2000);
}
</script>
