<?php
/**
 * Template per il modal di personalizzazione (override) di una creatura in un libro.
 * Riceve le variabili:
 * $creature: I dati originali della creatura.
 * $book_creature: L'entry della creatura nel libro, che contiene l'ID univoco e gli overrides.
 */

$entry_id = $book_creature['entry_id'];
$modal_id = 'overrideCreatureModal_' . $entry_id;

// Decodifica gli override esistenti o usa un array vuoto
$current_overrides = !empty($book_creature['overrides']) ? json_decode($book_creature['overrides'], true) : [];

// Aggiunta di una sanificazione extra per i valori di default in caso di dati nulli/vuoti
$get_id = $_GET['id'] ?? 0;
$creature_name = sanitize($creature['name_it'] ?? 'Creatura Sconosciuta');
?>

<div class="modal fade" id="<?= $modal_id ?>" tabindex="-1">
    <!-- Utilizziamo MODAL-XL e MODAL-DIALOG-SCROLLABLE per massimizzare lo spazio e la scorrevolezza -->
    <div class="modal-dialog modal-xl modal-dialog-scrollable"> 
        <div class="modal-content">
            <form method="POST" action="?page=book_edit&id=<?= $get_id ?>">
                <input type="hidden" name="action" value="override_creature">
                <input type="hidden" name="entry_id" value="<?= $entry_id ?>">
                <input type="hidden" name="creature_id" value="<?= $creature['id'] ?? 0 ?>">

                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="bi bi-person-fill-gear"></i> 
                        Personalizza: <?= $creature_name ?>
                    </h5>
                    <!-- CORREZIONE: btn-close-white per visibilità sull'header bg-warning -->
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Modifica solo i campi che vuoi personalizzare per questo libro. I campi lasciati vuoti useranno i valori predefiniti della creatura.</p>
                    
                    <!-- WRAPPER SCROLLABILE AGGIUNTIVO PER GARANTIRE LO SCORRIMENTO INTERNO -->
                    <!--div class="overflow-auto p-1" style="max-height: 70vh;"--> 
                        <div class="row g-3">
                            <div class="col-md-6"><div class="form-floating"><input type="text" class="form-control" name="name_it" value="<?= sanitize($current_overrides['name_it'] ?? $creature['name_it'] ?? '') ?>"><label>Nome Personalizzato</label></div></div>
                            <div class="col-md-6"><div class="form-floating"><input type="text" class="form-control" name="hit_points" value="<?= sanitize($current_overrides['hit_points'] ?? $creature['hit_points'] ?? '') ?>"><label>Punti Ferita</label></div></div>
                            
                            <div class="col-md-6"><div class="form-floating"><input type="text" class="form-control" name="armor_class" value="<?= sanitize($current_overrides['armor_class'] ?? $creature['armor_class'] ?? '') ?>"><label>Classe Armatura</label></div></div>
                            <div class="col-md-6"><div class="form-floating"><input type="text" class="form-control" name="challenge_rating" value="<?= sanitize($current_overrides['challenge_rating'] ?? $creature['challenge_rating'] ?? '') ?>"><label>Grado Sfida (GS)</label></div></div>

                            <div class="col-12"><label class="form-label">Azioni</label><textarea class="form-control" name="actions" rows="4"><?= sanitize($current_overrides['actions'] ?? $creature['actions'] ?? '') ?></textarea></div>
                            <div class="col-12"><label class="form-label">Abilità Speciali</label><textarea class="form-control" name="special_abilities" rows="4"><?= sanitize($current_overrides['special_abilities'] ?? $creature['special_abilities'] ?? '') ?></textarea></div>
                            <div class="col-12"><label class="form-label">Descrizione</label><textarea class="form-control" name="description_it" rows="3"><?= sanitize($current_overrides['description_it'] ?? $creature['description_it'] ?? '') ?></textarea></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-save-fill"></i> Salva Personalizzazione</button>
                </div>
            </form>
        </div>
    </div>
</div>
