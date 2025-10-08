<?php
/**
 * Template per il modal di dettaglio di un incantesimo.
 * Riceve le variabili:
 * $spell: I dati dell'incantesimo da visualizzare.
 * Opzionale: $on_edit_page: true se siamo in book_edit.php
 */

// Logica per determinare quale pulsante mostrare
$show_simple_add_button = (isset($_GET['page']) && $_GET['page'] === 'book_edit');

// Prepara i dati per il dropdown se necessario
if (!$show_simple_add_button && (isUser() || isAdmin())) {
    $owner_id = isAdmin() ? $_SESSION['admin_id'] : $_SESSION['user_id'];
    $owner_type = isAdmin() ? 'admin' : 'user';
    $user_books = Database::fetchAll(
        "SELECT id, name FROM ci_spellbooks WHERE " . ($owner_type === 'admin' ? "admin_id" : "user_id") . " = ? ORDER BY name ASC",
        [$owner_id]
    );
}
?>
<div class="modal fade" id="spellModal<?= $spell['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><?= sanitize($spell['name_it']) ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Livello:</strong> <?= $spell['level'] == 0 ? 'Trucchetto' : $spell['level'] ?><br>
                        <strong>Scuola:</strong> <?= sanitize($spell['school'] ?? '-') ?><br>
                        <strong>Tempo:</strong> <?= sanitize($spell['casting_time'] ?? '-') ?><br>
                        <strong>Gittata:</strong> <?= sanitize($spell['range_distance'] ?? '-') ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Componenti:</strong> <?= sanitize($spell['components'] ?? '-') ?><br>
                        <strong>Durata:</strong> <?= sanitize($spell['duration'] ?? '-') ?><br>
                        <strong>Concentrazione:</strong> <?= $spell['concentration'] ? 'Sì' : 'No' ?><br>
                        <strong>Rituale:</strong> <?= $spell['ritual'] ? 'Sì' : 'No' ?>
                    </div>
                </div>
                
                <h6>Descrizione:</h6>
                <p><?= nl2br(sanitize($spell['description_it'] ?? '')) ?></p>
                
                <?php if ($spell['higher_levels']): ?>
                <h6>A Livelli Superiori:</h6>
                <p><?= nl2br(sanitize($spell['higher_levels'])) ?></p>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>

                <?php if ($show_simple_add_button): ?>
                    <form method="POST" action="?page=book_edit&id=<?= $_GET['id'] ?? 0 ?>&active_tab=spells">
                        <input type="hidden" name="action" value="add_spell">
                        <input type="hidden" name="spell_id" value="<?= $spell['id'] ?>">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle-fill"></i> Aggiungi a questo Libro</button>
                    </form>
                <?php elseif (isUser() || isAdmin()): ?>
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="addSpellToBookDropdown<?= $spell['id'] ?>" data-bs-toggle="dropdown">
                            <i class="bi bi-plus-circle-fill"></i> Aggiungi a Libro
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if (empty($user_books)): ?>
                                <li><a class="dropdown-item disabled" href="#">Nessun libro trovato</a></li>
                            <?php else: ?>
                                <?php foreach ($user_books as $book): ?>
                                    <li>
                                        <form method="POST" action="?page=book_edit&id=<?= $book['id'] ?>&active_tab=spells">
                                            <input type="hidden" name="action" value="add_spell">
                                            <input type="hidden" name="spell_id" value="<?= $spell['id'] ?>">
                                            <button type="submit" class="dropdown-item"><?= sanitize($book['name']) ?></button>
                                        </form>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="?page=books"><i class="bi bi-journal-plus"></i> Gestisci Libri</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="?page=login" class="btn btn-primary"><i class="bi bi-box-arrow-in-right"></i> Accedi per aggiungere</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

