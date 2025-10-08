<?php
$page_title = 'Il Mio Profilo';

// Richiedi login utente
if (!isUser()) {
    redirect('/?page=login');
}

$user_id = $_SESSION['user_id'];
$user = Database::fetch("SELECT * FROM ci_users WHERE id = ?", [$user_id]);

// --- GESTIONE AZIONE: AGGIORNA PROFILO ---
if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $display_name = trim($_POST['display_name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($display_name) || empty($email)) {
        error('Nome e email non possono essere vuoti.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error('Formato email non valido.');
    } else {
        // Controlla se la nuova email è già usata da un altro utente
        $existing = Database::fetch("SELECT id FROM ci_users WHERE email = ? AND id != ?", [$email, $user_id]);
        if ($existing) {
            error('Email già in uso da un altro account.');
        } else {
            Database::query("UPDATE ci_users SET display_name = ?, email = ? WHERE id = ?", [$display_name, $email, $user_id]);
            success('Profilo aggiornato con successo!');
        }
    }
    redirect('/?page=profile');
}

// --- GESTIONE AZIONE: CANCELLA ACCOUNT ---
if (isset($_POST['action']) && $_POST['action'] === 'delete_account') {
    // 1. Trova l'ID dell'utente "Utente Cancellato"
    $deleted_user = Database::fetch("SELECT id FROM ci_users WHERE username = 'utente_cancellato'");
    if (!$deleted_user) {
        error('Errore critico: utente di fallback non trovato. Contatta un amministratore.');
        redirect('/?page=profile');
    }
    $deleted_user_id = $deleted_user['id'];

    // 2. Cancella i libri privati dell'utente
    Database::query("DELETE FROM ci_spellbooks WHERE user_id = ? AND is_public = 0", [$user_id]);

    // 3. Riassegna i libri pubblici all'utente cancellato
    Database::query("UPDATE ci_spellbooks SET user_id = ? WHERE user_id = ? AND is_public = 1", [$deleted_user_id, $user_id]);

    // 4. Cancella l'utente
    Database::query("DELETE FROM ci_users WHERE id = ?", [$user_id]);

    // 5. Esegui il logout e reindirizza
    session_destroy();
    $_SESSION = [];
    session_start(); // Inizia una nuova sessione per il messaggio di successo
    success('Il tuo account e i tuoi dati privati sono stati eliminati con successo.');
    redirect('/');
}

?>

<div class="container mt-4">
    <h2><i class="bi bi-person-circle"></i> Il Mio Profilo</h2>
    <p class="text-muted">Gestisci le informazioni del tuo account.</p>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5>I tuoi dati</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="mb-3">
                            <label for="display_name" class="form-label">Nome Visualizzato</label>
                            <input type="text" class="form-control" id="display_name" name="display_name" value="<?= sanitize($user['display_name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= sanitize($user['email']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" value="<?= sanitize($user['username']) ?>" disabled readonly>
                            <small class="form-text text-muted">L'username non può essere modificato.</small>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill"></i> Salva Modifiche</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mt-4 mt-lg-0">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5>Zona Pericolo</h5>
                </div>
                <div class="card-body">
                    <p>La cancellazione del tuo account è un'azione irreversibile.</p>
                    <ul>
                        <li>Tutti i tuoi **libri privati** verranno eliminati definitivamente.</li>
                        <li>Tutti i tuoi **libri pubblici** verranno conservati e assegnati a "Utente Cancellato".</li>
                    </ul>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                        <i class="bi bi-trash-fill"></i> Cancella il Mio Account
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Conferma Cancellazione</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Sei assolutamente sicuro di voler cancellare il tuo account? Questa azione non può essere annullata.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete_account">
                    <button type="submit" class="btn btn-danger">Sì, Cancella il Mio Account</button>
                </form>
            </div>
        </div>
    </div>
</div>