<div class="row g-4 mb-4">
    <div class="col-md-3"><div class="card text-white bg-primary"><div class="card-body text-center"><h3><?= $data['stats']['spells_active'] ?></h3><p class="mb-0">Incantesimi Attivi</p></div></div></div>
    <div class="col-md-3"><div class="card text-white bg-success"><div class="card-body text-center"><h3><?= $data['stats']['creatures_active'] ?></h3><p class="mb-0">Creature Attive</p></div></div></div>
    <div class="col-md-3"><div class="card text-white bg-warning"><div class="card-body text-center"><h3><?= $data['stats']['books_total'] ?></h3><p class="mb-0">Libri Totali</p></div></div></div>
    <div class="col-md-3"><div class="card text-white bg-info"><div class="card-body text-center"><h3><?= $data['stats']['users_active'] ?></h3><p class="mb-0">Utenti Attivi</p></div></div></div>
</div>
<div class="card">
    <div class="card-header bg-dark text-white"><h5 class="mb-0"><i class="bi bi-clock-history"></i> Attività Recente</h5></div>
    <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
        <thead><tr><th>Data/Ora</th><th>Admin</th><th>Azione</th><th>IP</th></tr></thead>
        <tbody>
            <?php foreach ($data['recent_logs'] as $log): ?>
                <tr><td><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td><td><?= sanitize($log['username']) ?></td><td><span class="badge bg-secondary"><?= sanitize($log['action']) ?></span></td><td><small class="text-muted"><?= sanitize($log['ip_address']) ?></small></td></tr>
            <?php endforeach; ?>
        </tbody>
    </table></div></div>
</div>