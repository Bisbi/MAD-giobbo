<?php
$page_title = 'La Mia Collezione di Avatar';
if (!isUser()) { redirect('/?page=login'); }
$user_id = $_SESSION['user_id'];

// Azione: Imposta avatar principale
if (isset($_GET['action']) && $_GET['action'] === 'set_current_avatar') {
    $avatar_url = $_GET['avatar_url'] ?? '';
    if (!empty($avatar_url) && strpos($avatar_url, 'uploads/avatars/') === 0) {
        $owned_avatar = Database::fetch("SELECT id FROM ci_user_avatars WHERE user_id = ? AND avatar_url = ?", [$user_id, $avatar_url]);
        if ($owned_avatar) {
            Database::query("UPDATE ci_users SET avatar_url = ? WHERE id = ?", [$avatar_url, $user_id]);
            $_SESSION['user_avatar'] = $avatar_url;
            success('Avatar principale aggiornato!');
        } else {
            error('Non hai i permessi per usare questo avatar.');
        }
    } else {
        error('Avatar selezionato non valido.');
    }
    redirect('/?page=my_avatars');
}

// ## AGGIUNTO: AZIONE PER ELIMINARE L'AVATAR ##
if (isset($_GET['action']) && $_GET['action'] === 'delete_avatar' && isset($_GET['id'])) {
    $avatar_id_to_delete = (int)$_GET['id'];
    
    // Verifica che l'avatar appartenga all'utente
    $avatar = Database::fetch("SELECT * FROM ci_user_avatars WHERE id = ? AND user_id = ?", [$avatar_id_to_delete, $user_id]);

    if ($avatar) {
        // 1. Elimina il file fisico dal server
        $file_path = ROOT_PATH . '/' . $avatar['avatar_url'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // 2. Elimina il record dal database
        Database::query("DELETE FROM ci_user_avatars WHERE id = ?", [$avatar_id_to_delete]);

        // 3. Se era l'avatar corrente, pulisci il profilo e la sessione
        if (isset($_SESSION['user_avatar']) && $_SESSION['user_avatar'] == $avatar['avatar_url']) {
            Database::query("UPDATE ci_users SET avatar_url = NULL WHERE id = ?", [$user_id]);
            $_SESSION['user_avatar'] = null;
        }
        
        success('Avatar eliminato con successo.');
    } else {
        error('Avatar non trovato o non autorizzato.');
    }
    redirect('/?page=my_avatars');
}


// Carica tutti gli avatar dell'utente
$my_avatars = Database::fetchAll("SELECT * FROM ci_user_avatars WHERE user_id = ? ORDER BY created_at DESC", [$user_id]);
?>

<div class="container mt-4">
    <h2><i class="bi bi-images"></i> La Mia Collezione di Avatar</h2>
    <p class="text-muted">Seleziona gli avatar da stampare, imposta il tuo avatar principale o elimina quelli che non usi più.</p>
    
    <p class="lead border-bottom pb-2 mb-3">Hai caricato <strong><?= count($my_avatars) ?></strong> su <strong>12</strong> avatar disponibili.</p>

    <?php if (empty($my_avatars)): ?>
        <div class="alert alert-info">Non hai ancora caricato nessun avatar. Inizia dalla <a href="?page=profile">pagina del tuo profilo</a>!</div>
    <?php else: ?>
        <form action="?page=print_token" method="POST" target="_blank">
    <div class="row g-3">
        <?php foreach ($my_avatars as $avatar): ?>
            <div class="col-6 col-md-3 col-lg-2 text-center">
                <label class="avatar-selectable d-block position-relative">
                    <input type="checkbox" name="avatar_urls[]" value="<?= sanitize($avatar['avatar_url']) ?>" class="form-check-input visually-hidden">
                    
                    <img src="<?= BASE_PATH ?>/<?= sanitize($avatar['avatar_url']) ?>" class="img-fluid rounded-circle" alt="Avatar Selezionabile">
                    
                    <div class="selection-overlay">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                </label>
                
                <div class="btn-group w-100 mt-2" role="group">
                     <a href="?page=my_avatars&action=set_current_avatar&avatar_url=<?= urlencode(sanitize($avatar['avatar_url'])) ?>" class="btn btn-sm btn-outline-secondary" title="Imposta come avatar principale">Usa</a>
                    <a href="?page=my_avatars&action=delete_avatar&id=<?= $avatar['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Sei sicuro di voler eliminare questo avatar?')" title="Elimina avatar"><i class="bi bi-trash-fill"></i></a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <hr class="my-4">
    <button type="submit" class="btn btn-primary"><i class="bi bi-printer-fill"></i> Stampa Selezionati</button>
</form>
    <?php endif; ?>
</div>

<style>
    .avatar-selectable {
        cursor: pointer;
        border-radius: 50%;
        overflow: hidden; /* Assicura che l'overlay non esca dal cerchio */
    }

    .avatar-selectable img {
        border: 3px solid transparent;
        aspect-ratio: 1/1;
        object-fit: cover;
        transition: border-color 0.2s;
    }

    .selection-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(13, 110, 253, 0.5); /* Sfondo blu semi-trasparente */
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 2rem; /* Dimensione dell'icona di spunta */
        opacity: 0; /* Nascosto di default */
        transition: opacity 0.2s;
        border-radius: 50%;
    }

    /* Quando il checkbox nascosto è selezionato, mostra l'overlay... */
    .avatar-selectable input[type="checkbox"]:checked ~ .selection-overlay {
        opacity: 1;
    }

    /* ...e aggiungi un bordo all'immagine */
    .avatar-selectable input[type="checkbox"]:checked ~ img {
        border-color: #0d6efd;
    }

    /* Mostra un bordo anche al passaggio del mouse per indicare che è cliccabile */
    .avatar-selectable:hover img {
        border-color: #a2c5f7;
    }
</style>