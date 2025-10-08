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
                <?php if ($spell['classes']): ?>
                <h6>Classi:</h6>
                <p><?= sanitize($spell['classes']) ?></p>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
            </div>
        </div>
    </div>
</div>
