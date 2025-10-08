<div class="card">
    <div class="card-header bg-info text-white"><h5 class="mb-0"><i class="bi bi-people-fill"></i> Gestione Utenti</h5></div>
    <div class="card-body"><div class="table-responsive"><table class="table table-striped table-hover">
        <thead><tr><th>ID</th><th>Username</th><th>Nome</th><th>Email</th><th>Stato</th><th class="text-end">Azioni</th></tr></thead>
        <tbody>
            <?php foreach ($data['users'] as $user): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= sanitize($user['username']) ?></td>
                <td><?= sanitize($user['display_name']) ?></td>
                <td><?= sanitize($user['email']) ?></td>
                <td><?php if ($user['active']): ?><span class="badge bg-success">Attivo</span><?php else: ?><span class="badge bg-danger">Disattivato</span><?php endif; ?></td>
                <td class="text-end">
                    <a href="?page=admin&section=users&toggle_user=<?= $user['id'] ?>" class="btn btn-sm <?= $user['active'] ? 'btn-warning' : 'btn-success' ?>">
                        <i class="bi bi-toggle-<?= $user['active'] ? 'on' : 'off' ?>"></i> <?= $user['active'] ? 'Disattiva' : 'Attiva' ?>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table></div></div>
</div>