<?php
/**
 * Template per la card di un incantesimo.
 * Richiede che la variabile $spell sia definita.
 */
?>
<div class="col-md-6 col-lg-4">
    <div class="card spell-card h-100" data-id="<?= $spell['id'] ?>">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-start">
                <h5 class="card-title mb-0"><?= sanitize($spell['name_it']) ?></h5>
                <span class="badge bg-light text-dark">
                    <?= $spell['level'] == 0 ? 'T' : $spell['level'] ?>
                </span>
            </div>
            <small><?= sanitize($spell['school'] ?? '-') ?></small>
        </div>
        <div class="card-body d-flex flex-column">
            <!-- Info rapide -->
            <div class="mb-2">
                <small class="text-muted">
                    <i class="bi bi-clock-fill"></i> <?= sanitize($spell['casting_time'] ?? '-') ?>
                    | <i class="bi bi-rulers"></i> <?= sanitize($spell['range_distance'] ?? '-') ?>
                </small>
            </div>
            <div class="mb-2">
                <small class="text-muted">
                    <i class="bi bi-hourglass-split"></i> <?= sanitize($spell['duration'] ?? '-') ?>
                </small>
            </div>
            
            <!-- Descrizione -->
            <p class="card-text small flex-grow-1">
                <?= substr(sanitize($spell['description_it'] ?? ''), 0, 120) ?>...
            </p>
            
            <!-- Note (se presenti) -->
            <?php if (!empty($spell['notes'])): ?>
            <div class="alert alert-info p-2 small mt-auto">
                <strong><i class="bi bi-chat-left-text-fill"></i> Note:</strong> <?= nl2br(sanitize($spell['notes'])) ?>
            </div>
            <?php endif; ?>

            <!-- Badges -->
            <div class="mt-2">
                <?php if ($spell['concentration']): ?>
                <span class="badge bg-warning text-dark">
                    <i class="bi bi-eye-fill"></i> Concentrazione
                </span>
                <?php endif; ?>
                
                <?php if ($spell['ritual']): ?>
                <span class="badge bg-info">
                    <i class="bi bi-circle-fill"></i> Rituale
                </span>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-footer">
            <button class="btn btn-sm btn-outline-primary w-100" 
                    data-bs-toggle="modal" 
                    data-bs-target="#spellModal<?= $spell['id'] ?>">
                <i class="bi bi-eye-fill"></i> Dettagli
            </button>
        </div>
    </div>
</div>

