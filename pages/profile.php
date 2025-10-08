<?php
$page_title = 'Il Mio Profilo';

// Richiedi login utente
if (!isUser()) {
    redirect('/?page=login');
}

$user_id = $_SESSION['user_id'];

// --- GESTIONE AZIONI POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // --- AGGIORNA PROFILO (NOME E EMAIL) ---
    if ($_POST['action'] === 'update_profile') {
        $display_name = trim($_POST['display_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if (empty($display_name) || empty($email)) {
            error('Nome e email non possono essere vuoti.');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            error('Formato email non valido.');
        } else {
            $existing = Database::fetch("SELECT id FROM ci_users WHERE email = ? AND id != ?", [$email, $user_id]);
            if ($existing) {
                error('Email già in uso da un altro account.');
            } else {
                Database::query("UPDATE ci_users SET display_name = ?, email = ? WHERE id = ?", [$display_name, $email, $user_id]);
                $_SESSION['user_display_name'] = $display_name;
                success('Profilo aggiornato con successo!');
            }
        }
    }

    // --- CAMBIA PASSWORD ---
    if ($_POST['action'] === 'change_password') {
        $current_pass = $_POST['current_password'] ?? '';
        $new_pass = $_POST['new_password'] ?? '';
        $confirm_pass = $_POST['confirm_password'] ?? '';
        $user_data = Database::fetch("SELECT password FROM ci_users WHERE id = ?", [$user_id]);
        if (!$user_data || !password_verify($current_pass, $user_data['password'])) {
            error('La password attuale non è corretta.');
        } elseif (strlen($new_pass) < 6) {
            error('La nuova password deve essere di almeno 6 caratteri.');
        } elseif ($new_pass !== $confirm_pass) {
            error('Le nuove password non coincidono.');
        } else {
            $new_password_hash = password_hash($new_pass, PASSWORD_DEFAULT);
            Database::query("UPDATE ci_users SET password = ? WHERE id = ?", [$new_password_hash, $user_id]);
            success('Password aggiornata con successo!');
        }
    }

    // --- AGGIORNA AVATAR ---
    if ($_POST['action'] === 'update_avatar') {
        $avatar_count = Database::fetch("SELECT COUNT(*) as c FROM ci_user_avatars WHERE user_id = ?", [$user_id])['c'] ?? 0;
        if ($avatar_count >= 12) {
            error('Hai raggiunto il limite massimo di 12 avatar. Elimina un avatar dalla tua collezione per caricarne di nuovi.');
        } elseif (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['avatar'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($file['type'], $allowed_types)) {
                error('Formato file non valido. Sono ammessi solo JPG, PNG, GIF.');
            } elseif ($file['size'] > 2 * 1024 * 1024) { // 2MB
                error('Il file è troppo grande. Dimensione massima: 2MB.');
            } else {
                $upload_dir = ROOT_PATH . '/uploads/avatars/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $new_filename = 'user_' . $user_id . '_' . time() . '.' . $extension;
                if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_filename)) {
                    $avatar_path = 'uploads/avatars/' . $new_filename;
                    Database::query("INSERT INTO ci_user_avatars (user_id, avatar_url) VALUES (?, ?)", [$user_id, $avatar_path]);
                    Database::query("UPDATE ci_users SET avatar_url = ? WHERE id = ?", [$avatar_path, $user_id]);
                    $_SESSION['user_avatar'] = $avatar_path;
                    success('Nuovo avatar caricato e aggiunto alla tua collezione!');
                } else {
                    error('Errore durante il caricamento del file.');
                }
            }
        } else {
            error('Nessun file selezionato o errore nel caricamento.');
        }
    }

    // --- IMPOSTA AVATAR PREDEFINITO ---
    if ($_POST['action'] === 'set_default_avatar') {
        $avatar_url = $_POST['avatar_url'] ?? '';
        if (!empty($avatar_url) && strpos($avatar_url, 'assets/img/default_avatars/') === 0) {
            Database::query("UPDATE ci_users SET avatar_url = ? WHERE id = ?", [$avatar_url, $user_id]);
            $_SESSION['user_avatar'] = $avatar_url;
            success('Avatar impostato con successo!');
        } else {
            error('Avatar selezionato non valido.');
        }
    }

    // --- CANCELLA ACCOUNT ---
    if ($_POST['action'] === 'delete_account') {
        $deleted_user = Database::fetch("SELECT id FROM ci_users WHERE username = 'utente_cancellato'");
        if (!$deleted_user) {
            error('Errore critico: utente di fallback non trovato. Contatta un amministratore.');
            redirect('/?page=profile');
        }
        $deleted_user_id = $deleted_user['id'];
        Database::query("DELETE FROM ci_spellbooks WHERE user_id = ? AND is_public = 0", [$user_id]);
        Database::query("UPDATE ci_spellbooks SET user_id = ? WHERE user_id = ? AND is_public = 1", [$deleted_user_id, $user_id]);
        Database::query("DELETE FROM ci_users WHERE id = ?", [$user_id]);
        session_destroy();
        $_SESSION = [];
        session_start();
        success('Il tuo account e i tuoi dati privati sono stati eliminati con successo.');
        redirect('/');
    }
    redirect('/?page=profile');
}

// --- CARICAMENTO DATI PER LA VISUALIZZAZIONE ---
$user = Database::fetch("SELECT * FROM ci_users WHERE id = ?", [$user_id]);
$stats = [
    'total_books' => Database::fetch("SELECT COUNT(*) as c FROM ci_spellbooks WHERE user_id = ?", [$user_id])['c'] ?? 0,
    'public_books' => Database::fetch("SELECT COUNT(*) as c FROM ci_spellbooks WHERE user_id = ? AND is_public = 1", [$user_id])['c'] ?? 0,
    'avatar_count' => Database::fetch("SELECT COUNT(*) as c FROM ci_user_avatars WHERE user_id = ?", [$user_id])['c'] ?? 0,
];
?>

<div class="container mt-4">
    <h2><i class="bi bi-person-circle"></i> Il Mio Profilo</h2>
    <p class="text-muted">Gestisci le informazioni e le impostazioni del tuo account.</p>
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header"><h5>I tuoi dati</h5></div>
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
            <div class="card mb-4">
                <div class="card-header"><h5>Cambia Password</h5></div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Password Attuale</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Nuova Password (almeno 6 caratteri)</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" minlength="6" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Conferma Nuova Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="6" required>
                        </div>
                        <button type="submit" class="btn btn-warning">Aggiorna Password</button>
                    </form>
                </div>
            </div>
            <div class="card border-danger">
                <div class="card-header bg-danger text-white"><h5>Zona Pericolo</h5></div>
                <div class="card-body">
                    <p>La cancellazione del tuo account è un'azione irreversibile.</p>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal"><i class="bi bi-trash-fill"></i> Cancella il Mio Account</button>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card mb-4 text-center">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Avatar</h5>
                    <span class="badge bg-secondary"><?= $stats['avatar_count'] ?> / 12</span>
                </div>
                <div class="card-body">
                    <img src="<?= BASE_PATH ?>/<?= sanitize($user['avatar_url'] ?? 'assets/img/default_avatar.png') ?>" alt="Avatar" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_avatar">
                        <div class="mb-3">
                            <label for="avatar" class="form-label small">Carica una nuova immagine (max 2MB)</label>
                            <input class="form-control form-control-sm" type="file" id="avatar" name="avatar" required>
                        </div>
                        <div class="btn-group w-100" role="group">
                            <button type="submit" class="btn btn-secondary btn-sm">Carica</button>
                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#selectAvatarModal">Scegli</button>
                            <?php if (!empty($user['avatar_url'])): ?>
                                <a href="?page=print_token&avatar_url=<?= urlencode($user['avatar_url']) ?>" class="btn btn-outline-primary btn-sm" target="_blank">Stampa</a>
                            <?php endif; ?>
                        </div>
                    </form>
                    <hr>
                    <a href="?page=my_avatars" class="btn btn-outline-success w-100"><i class="bi bi-images"></i> Vai alla Mia Collezione</a>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header"><h5>Le Tue Statistiche</h5></div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">Libri Creati <span class="badge bg-primary rounded-pill"><?= $stats['total_books'] ?></span></li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">Libri Pubblici <span class="badge bg-success rounded-pill"><?= $stats['public_books'] ?></span></li>
                </ul>
            </div>
            <div class="card">
                <div class="card-header"><h5><i class="bi bi-robot"></i> Crea con l'AI</h5></div>
                <div class="card-body">
                    <p class="small text-muted">Copia il prompt e usalo nel tuo generatore di immagini AI preferito.</p>
                    <div class="p-2 bg-light border rounded mb-2" id="prompt-container" style="font-family: monospace; font-size: 11px;">
                        D&D character portrait token of a [RACE/CLASS], fantasy digital art, bust shot, centered, vibrant colors, on a simple background, circular frame
                    </div>
                    <button class="btn btn-outline-secondary btn-sm" onclick="copyPrompt()"><i class="bi bi-clipboard"></i> Copia</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Conferma Cancellazione</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><p>Sei sicuro di voler cancellare il tuo account? Questa azione è irreversibile.</p></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                <form method="POST" style="display: inline;"><input type="hidden" name="action" value="delete_account"><button type="submit" class="btn btn-danger">Sì, Cancella Account</button></form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="selectAvatarModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Scegli un Avatar Predefinito</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-2">
                    <?php
                    $default_avatars_path = ROOT_PATH . '/assets/img/default_avatars/';
                    $avatar_files = glob($default_avatars_path . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
                    if (empty($avatar_files)): ?>
                        <div class="col-12 text-center text-muted">Nessun avatar predefinito trovato.</div>
                    <?php else: foreach ($avatar_files as $avatar_path):
                        $avatar_file = basename($avatar_path);
                        $avatar_url = 'assets/img/default_avatars/' . $avatar_file;
                    ?>
                    <div class="col-3 col-md-2">
                        <form method="POST" class="w-100">
                            <input type="hidden" name="action" value="set_default_avatar">
                            <input type="hidden" name="avatar_url" value="<?= sanitize($avatar_url) ?>">
                            <button type="submit" class="btn btn-link p-0 border-0"><img src="<?= BASE_PATH ?>/<?= $avatar_url ?>" class="img-fluid rounded-circle" style="cursor: pointer;" alt="Avatar"></button>
                        </form>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyPrompt() {
    const promptText = document.getElementById('prompt-container').innerText;
    navigator.clipboard.writeText(promptText).then(() => { alert('Prompt copiato!'); });
}
</script>