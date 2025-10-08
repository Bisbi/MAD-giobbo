<div class="card">
    <div class="card-header bg-dark text-white"><h5 class="mb-0"><i class="bi bi-clock-history"></i> Log Attività (Ultimi 100)</h5></div>
    <div class="card-body"><div class="table-responsive"><table class="table table-sm table-hover">
        <thead><tr><th>ID</th><th>Data/Ora</th><th>Admin</th><th>Azione</th><th>Dettagli</th><th>IP</th></tr></thead>
        <tbody>
            <?php foreach ($data['logs'] as $log): ?>
            <tr>
                <td><?= $log['id'] ?></td><td><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td><td><?= sanitize($log['username'] ?? '-') ?></td>
                <td><span class="badge bg-secondary"><?= sanitize($log['action']) ?></span></td>
                <td><small><?= substr(sanitize($log['details'] ?? ''), 0, 50) ?></small></td>
                <td><small class="text-muted"><?= sanitize($log['ip_address']) ?></small></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table></div></div>
</div>