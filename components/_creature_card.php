<div class="card creature-card h-100">
    <div class="card-header bg-success text-white">
        <div class="d-flex justify-content-between align-items-start">
            <h5 class="card-title mb-0"><?= sanitize($creature['display_name'] ?? $creature['name_it']) ?></h5>
            <span class="badge bg-light text-dark"><?= sanitize($creature['size']) ?></span>
        </div>
        <small><i class="bi bi-tag-fill"></i> <?= sanitize(ucfirst($creature['creature_type'])) ?></small>
    </div>
    <div class="card-body">
        <div class="row g-2 mb-3">
            <div class="col-6"><small class="text-muted d-block">CA</small><strong><?= sanitize($creature['armor_class'] ?? '-') ?></strong></div>
            <div class="col-6"><small class="text-muted d-block">PF</small><strong><?= sanitize($creature['hit_points'] ?? '-') ?></strong></div>
        </div>
        <div class="mb-2">
            <small class="text-muted"><i class="bi bi-speedometer2"></i> Velocità:</small>
            <small>
                <?php
                $speeds = [];
                if (!empty($creature['speed_ground'])) $speeds[] = "Terra " . $creature['speed_ground'] . "m";
                if (!empty($creature['speed_fly'])) $speeds[] = "Volo " . $creature['speed_fly'] . "m";
                if (!empty($creature['speed_swim'])) $speeds[] = "Nuoto " . $creature['speed_swim'] . "m";
                echo implode(', ', $speeds);
                ?>
            </small>
        </div>
        <p class="card-text small"><?= substr(sanitize($creature['description_it'] ?? ''), 0, 100) ?>...</p>
        <?php if ($creature['challenge_rating']): ?><span class="badge bg-warning text-dark">GS <?= sanitize($creature['challenge_rating']) ?></span><?php endif; ?>
    </div>
    <div class="card-footer">
        <button class="btn btn-sm btn-outline-success w-100" data-bs-toggle="modal" data-bs-target="#creatureModal<?= $creature['id'] ?>"><i class="bi bi-eye-fill"></i> Dettagli</button>
    </div>
</div>
