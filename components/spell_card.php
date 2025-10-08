<div class="card spell-card h-100">
    <div class="card-header bg-primary text-white">
        <div class="d-flex justify-content-between align-items-start">
            <h5 class="card-title mb-0"><?= sanitize($spell['name_it']) ?></h5>
            <span class="badge bg-light text-dark"><?= $spell['level'] == 0 ? 'T' : $spell['level'] ?></span>
        </div>
        <small><?= sanitize($spell['school'] ?? '-') ?></small>
    </div>
    <div class="card-body">
        <div class="mb-2"><small class="text-muted"><i class="bi bi-clock-fill"></i> <?= sanitize($spell['casting_time'] ?? '-') ?> | <i class="bi bi-rulers"></i> <?= sanitize($spell['range_distance'] ?? '-') ?></small></div>
        <div class="mb-2"><small class="text-muted"><i class="bi bi-hourglass-split"></i> <?= sanitize($spell['duration'] ?? '-') ?></small></div>
        <p class="card-text small"><?= substr(sanitize($spell['description_it'] ?? ''), 0, 120) ?>...</p>
        <div>
            <?php if ($spell['concentration']): ?><span class="badge bg-warning text-dark"><i class="bi bi-eye-fill"></i> Concentrazione</span><?php endif; ?>
            <?php if ($spell['ritual']): ?><span class="badge bg-info"><i class="bi bi-circle-fill"></i> Rituale</span><?php endif; ?>
        </div>
    </div>
    <div class="card-footer">
        <button class="btn btn-sm btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#spellModal<?= $spell['id'] ?>"><i class="bi bi-eye-fill"></i> Dettagli</button>
    </div>
</div>
