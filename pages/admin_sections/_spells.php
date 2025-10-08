<div class="card">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-book-fill"></i> Gestione Incantesimi</h5>
        <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#spellFormModal">
            <i class="bi bi-plus-circle-fill"></i> Nuovo Incantesimo
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Livello</th>
                        <th>Scuola</th>
                        <th class="text-end">Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['spells'] as $spell): ?>
                    <tr>
                        <td><?= $spell['id'] ?></td>
                        <td><strong><?= sanitize($spell['name_it']) ?></strong></td>
                        <td><span class="badge bg-primary"><?= $spell['level'] == 0 ? 'T' : $spell['level'] ?></span></td>
                        <td><?= sanitize($spell['school']) ?></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-warning" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#spellFormModal"
                                    data-id="<?= $spell['id'] ?>"
                                    data-name_it="<?= htmlspecialchars($spell['name_it']) ?>"
                                    data-name_en="<?= htmlspecialchars($spell['name_en']) ?>"
                                    data-level="<?= $spell['level'] ?>"
                                    data-school="<?= htmlspecialchars($spell['school']) ?>"
                                    data-classes="<?= htmlspecialchars($spell['classes']) ?>"
                                    data-casting_time="<?= htmlspecialchars($spell['casting_time']) ?>"
                                    data-range_distance="<?= htmlspecialchars($spell['range_distance']) ?>"
                                    data-components="<?= htmlspecialchars($spell['components']) ?>"
                                    data-duration="<?= htmlspecialchars($spell['duration']) ?>"
                                    data-description_it="<?= htmlspecialchars($spell['description_it']) ?>"
                                    data-higher_levels="<?= htmlspecialchars($spell['higher_levels']) ?>"
                                    data-ritual="<?= $spell['ritual'] ?>"
                                    data-concentration="<?= $spell['concentration'] ?>">
                                <i class="bi bi-pencil-fill"></i> Modifica
                            </button>
                            <a href="?page=admin&section=spells&delete_spell=<?= $spell['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Disattivare questo incantesimo?')">
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

