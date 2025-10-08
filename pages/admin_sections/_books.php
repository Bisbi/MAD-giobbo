<div class="card">
    <div class="card-header bg-warning text-dark"><h5 class="mb-0"><i class="bi bi-journals"></i> Gestione Libri Utenti</h5></div>
    <div class="card-body"><div class="table-responsive"><table class="table table-striped table-hover">
        <thead><tr><th>Nome Libro</th><th>Proprietario</th><th>N° Carte</th><th>Stato</th><th class="text-end">Azioni</th></tr></thead>
        <tbody>
            <?php if (empty($data['all_books'])): ?>
                <tr><td colspan="5" class="text-center text-muted">Nessun libro trovato.</td></tr>
            <?php else: foreach ($data['all_books'] as $book): ?>
            <tr>
                <td><strong><?= sanitize($book['name']) ?></strong></td>
                <td><?= sanitize($book['owner_name'] ?? 'N/D') ?></td>
                <td><span class="badge bg-dark"><?= $book['total_cards'] ?></span></td>
                <td><?php if ($book['is_public']): ?><span class="badge bg-success">Pubblico</span><?php else: ?><span class="badge bg-secondary">Privato</span><?php endif; ?></td>
                <td class="text-end"><a href="?page=book_detail&id=<?= $book['id'] ?>" class="btn btn-sm btn-info" target="_blank"><i class="bi bi-eye-fill"></i> Vedi</a></td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table></div></div>
</div>