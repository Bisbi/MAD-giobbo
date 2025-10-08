<div class="card">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-bug-fill"></i> Gestione Creature</h5>
        <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#creatureFormModal">
            <i class="bi bi-plus-circle-fill"></i> Nuova Creatura
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Taglia</th>
                        <th>GS</th>
                        <th class="text-end">Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['creatures'] as $creature): ?>
                    <tr>
                        <td><?= $creature['id'] ?></td>
                        <td><strong><?= sanitize($creature['name_it']) ?></strong></td>
                        <td><span class="badge bg-secondary"><?= sanitize($creature['creature_type']) ?></span></td>
                        <td><?= sanitize($creature['size']) ?></td>
                        <td><?= sanitize($creature['challenge_rating']) ?></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-warning" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#creatureFormModal"
                                    data-id="<?= $creature['id'] ?>"
                                    data-name-it="<?= htmlspecialchars($creature['name_it']) ?>"
                                    data-name-en="<?= htmlspecialchars($creature['name_en']) ?>"
                                    data-creature-type="<?= htmlspecialchars($creature['creature_type']) ?>"
                                    data-size="<?= htmlspecialchars($creature['size']) ?>"
                                    data-armor-class="<?= htmlspecialchars($creature['armor_class']) ?>"
                                    data-hit-points="<?= htmlspecialchars($creature['hit_points']) ?>"
                                    data-speed-ground="<?= $creature['speed_ground'] ?>"
                                    data-speed-fly="<?= $creature['speed_fly'] ?>"
                                    data-speed-swim="<?= $creature['speed_swim'] ?>"
                                    data-str="<?= $creature['str'] ?>"
                                    data-dex="<?= $creature['dex'] ?>"
                                    data-con="<?= $creature['con'] ?>"
                                    data-int="<?= $creature['int'] ?>"
                                    data-wis="<?= $creature['wis'] ?>"
                                    data-cha="<?= $creature['cha'] ?>"
                                    data-skills="<?= htmlspecialchars($creature['skills']) ?>"
                                    data-senses="<?= htmlspecialchars($creature['senses']) ?>"
                                    data-languages="<?= htmlspecialchars($creature['languages']) ?>"
                                    data-challenge-rating="<?= htmlspecialchars($creature['challenge_rating']) ?>"
                                    data-special-abilities="<?= htmlspecialchars($creature['special_abilities']) ?>"
                                    data-actions="<?= htmlspecialchars($creature['actions']) ?>"
                                    data-description-it="<?= htmlspecialchars($creature['description_it']) ?>">
                                <i class="bi bi-pencil-fill"></i> Modifica
                            </button>
                            <a href="?page=admin&section=creatures&delete_creature=<?= $creature['id'] ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Disattivare questa creatura?')">
                                <i class="bi bi-trash-fill"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
