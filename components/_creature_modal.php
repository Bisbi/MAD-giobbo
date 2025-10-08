<div class="modal fade" id="creatureModal<?= $creature['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><?= sanitize($creature['display_name'] ?? $creature['name_it']) ?></h5>
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
                        <strong>Velocità:</strong> 
                        <?php
                        $speeds = [];
                        if (!empty($creature['speed_ground'])) $speeds[] = "Terra " . $creature['speed_ground'] . "m";
                        if (!empty($creature['speed_fly'])) $speeds[] = "Volo " . $creature['speed_fly'] . "m";
                        if (!empty($creature['speed_swim'])) $speeds[] = "Nuoto " . $creature['speed_swim'] . "m";
                        echo implode(', ', $speeds);
                        ?><br>
                        <strong>GS:</strong> <?= sanitize($creature['challenge_rating'] ?? '-') ?>
                    </div>
                </div>
                <h6>Caratteristiche:</h6>
                <div class="row text-center mb-3 bg-light p-2 rounded">
                    <div class="col"><strong>FOR</strong><br><?= $creature['str'] ?></div>
                    <div class="col"><strong>DES</strong><br><?= $creature['dex'] ?></div>
                    <div class="col"><strong>COS</strong><br><?= $creature['con'] ?></div>
                    <div class="col"><strong>INT</strong><br><?= $creature['int'] ?></div>
                    <div class="col"><strong>SAG</strong><br><?= $creature['wis'] ?></div>
                    <div class="col"><strong>CAR</strong><br><?= $creature['cha'] ?></div>
                </div>
                <?php if ($creature['skills']): ?><h6>Competenze:</h6><p><?= nl2br(sanitize($creature['skills'])) ?></p><?php endif; ?>
                <?php if ($creature['senses']): ?><h6>Sensi:</h6><p><?= nl2br(sanitize($creature['senses'])) ?></p><?php endif; ?>
                <?php if ($creature['languages']): ?><h6>Linguaggi:</h6><p><?= nl2br(sanitize($creature['languages'])) ?></p><?php endif; ?>
                <?php if ($creature['special_abilities']): ?><h6>Abilità Speciali:</h6><p><?= nl2br(sanitize($creature['special_abilities'])) ?></p><?php endif; ?>
                <?php if ($creature['actions']): ?><h6>Azioni:</h6><p><?= nl2br(sanitize($creature['actions'])) ?></p><?php endif; ?>
                <h6>Descrizione:</h6>
                <p><?= nl2br(sanitize($creature['description_it'] ?? '')) ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
            </div>
        </div>
    </div>
</div>
