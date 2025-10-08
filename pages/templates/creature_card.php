<?php
/**
 * Template per la card di una creatura.
 * Richiede che la variabile $creature sia definita.
 */

// Gestisce gli override dal libro
$display_name = $creature['display_name'] ?? $creature['name_it'];
?>
<div class="col-md-6 col-lg-4">
    <div class="card creature-card h-100" data-id="<?= $creature['id'] ?>">
        <div class="card-header bg-success text-white">
            <div class="d-flex justify-content-between align-items-start">
                <h5 class="card-title mb-0"><?= sanitize($display_name) ?></h5>
                <span class="badge bg-light text-dark"><?= sanitize($creature['size']) ?></span>
            </div>
            <small><i class="bi bi-tag-fill"></i> <?= sanitize(ucfirst($creature['creature_type'])) ?></small>
        </div>
        <div class="card-body d-flex flex-column">
            <div class="row g-2 mb-3">
                <div class="col-6"><small class="text-muted d-block">CA</small><strong><?= sanitize($creature['armor_class'] ?? '-') ?></strong></div>
                <div class="col-6"><small class="text-muted d-block">PF</small><strong><?= sanitize($creature['hit_points'] ?? '-') ?></strong></div>
            </div>
            <div class="mb-2">
                <small class="text-muted"><i class="bi bi-speedometer2"></i> Velocità:</small>
                <small>
                    <?php if ($creature['speed_ground']): ?>Terra <?= $creature['speed_ground'] ?>m <?php endif; ?>
                    <?php if ($creature['speed_fly']): ?>| Volo <?= $creature['speed_fly'] ?>m <?php endif; ?>
                    <?php if ($creature['speed_swim']): ?>| Nuoto <?= $creature['speed_swim'] ?>m <?php endif; ?>
                </small>
            </div>
            <div class="stats-grid mb-2 flex-grow-1">
                <small class="d-flex justify-content-between">
                    <span>FOR <strong><?= $creature['str'] ?></strong></span>
                    <span>DES <strong><?= $creature['dex'] ?></strong></span>
                    <span>COS <strong><?= $creature['con'] ?></strong></span>
                </small>
                <small class="d-flex justify-content-between">
                    <span>INT <strong><?= $creature['int'] ?></strong></span>
                    <span>SAG <strong><?= $creature['wis'] ?></strong></span>
                    <span>CAR <strong><?= $creature['cha'] ?></strong></span>
                </small>
            </div>
            <?php if ($creature['challenge_rating']): ?><span class="badge bg-warning text-dark">GS <?= sanitize($creature['challenge_rating']) ?></span><?php endif; ?>
        </div>
        <div class="card-footer">
            <button class="btn btn-sm btn-outline-success w-100" data-bs-toggle="modal" data-bs-target="#creatureModal<?= $creature['id'] ?>"><i class="bi bi-eye-fill"></i> Dettagli</button>
        </div>
    </div>
</div>
