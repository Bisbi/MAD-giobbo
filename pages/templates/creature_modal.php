<?php
/**
 * Template per il modal di dettaglio di una creatura.
 * Riceve le variabili:
 * $creature: I dati della creatura da visualizzare.
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
<div class="modal fade" id="creatureModal<?= $creature['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><?= sanitize($creature['name_it']) ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Tipo:</strong> <?= sanitize(ucfirst($creature['creature_type'])) ?><br>
                        <strong>Taglia:</strong> <?= sanitize($creature['size']) ?><br>
                        <strong>CA:</strong> <?= sanitize($creature['armor_class'] ?? '-') ?><br>
                        <strong>PF:</strong> <?= sanitize($creature['hit_points'] ?? '-') ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Velocità Terra:</strong> <?= $creature['speed_ground'] ?>m<br>
                        <?php if ($creature['speed_fly']): ?><strong>Velocità Volo:</strong> <?= $creature['speed_fly'] ?>m<br><?php endif; ?>
                        <?php if ($creature['speed_swim']): ?><strong>Velocità Nuoto:</strong> <?= $creature['speed_swim'] ?>m<br><?php endif; ?>
                        <strong>GS:</strong> <?= sanitize($creature['challenge_rating'] ?? '-') ?>
                    </div>
                </div>
                <h6>Caratteristiche:</h6>
                <div class="row text-center mb-3">
                    <div class="col-2"><strong>FOR</strong><br><?= $creature['str'] ?></div>
                    <div class="col-2"><strong>DES</strong><br><?= $creature['dex'] ?></div>
                    <div class="col-2"><strong>COS</strong><br><?= $creature['con'] ?></div>
                    <div class="col-2"><strong>INT</strong><br><?= $creature['int'] ?></div>
                    <div class="col-2"><strong>SAG</strong><br><?= $creature['wis'] ?></div>
                    <div class="col-2"><strong>CAR</strong><br><?= $creature['cha'] ?></div>
                </div>
                <?php if ($creature['special_abilities']): ?>
                <h6>Abilità Speciali:</h6>
                <p><?= nl2br(sanitize($creature['special_abilities'])) ?></p>
                <?php endif; ?>
                
                <?php if ($creature['actions']): ?>
                <h6>Azioni:</h6>
                <p><?= nl2br(sanitize($creature['actions'])) ?></p>
                <?php endif; ?>
                
                <h6>Descrizione:</h6>
                <p><?= nl2br(sanitize($creature['description_it'] ?? '')) ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>

                <?php if ($show_simple_add_button): ?>
                    <form method="POST" action="?page=book_edit&id=<?= $_GET['id'] ?? 0 ?>&active_tab=creatures">
                        <input type="hidden" name="action" value="add_creature">
                        <input type="hidden" name="creature_id" value="<?= $creature['id'] ?>">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle-fill"></i> Aggiungi a questo Libro</button>
                    </form>
                <?php elseif (isUser() || isAdmin()): ?>
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-plus-circle-fill"></i> Aggiungi a Libro
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if (empty($user_books)): ?>
                                <li><a class="dropdown-item disabled" href="#">Nessun libro trovato</a></li>
                            <?php else: ?>
                                <?php foreach ($user_books as $book): ?>
                                    <li>
                                        <form method="POST" action="?page=book_edit&id=<?= $book['id'] ?>&active_tab=creatures">
                                            <input type="hidden" name="action" value="add_creature">
                                            <input type="hidden" name="creature_id" value="<?= $creature['id'] ?>">
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

