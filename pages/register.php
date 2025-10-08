<?php
/**
 * Registrazione Utenti
 */

$page_title = 'Registrazione';

// Se già loggato, redirect
if (isAdmin() || isUser()) {
    redirect('/');
}

// Processa form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? ''); // <-- AGGIUNGI QUESTA RIGA
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $display_name = trim($_POST['display_name'] ?? '');
    
    // Validazione
    if (!$username || !$password || !$password_confirm || !$display_name || !$email) { // <-- AGGIUNGI !$email
        error('Compila tutti i campi!');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // <-- AGGIUNGI CONTROLLO EMAIL
        error('Inserisci un indirizzo email valido!');
    } elseif (strlen($username) < 3) {
        // ... (altri controlli)
    } else {
        // Verifica username o email non esistente
        $existing = Database::fetch(
            "SELECT id FROM ci_users WHERE username = ? OR email = ?", // <-- MODIFICA QUERY
            [$username, $email]
        );
        
        if ($existing) {
            error('Username o Email già esistente!');
        } else {
            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Inserisci utente
            try {
                // MODIFICA LA QUERY DI INSERIMENTO
                Database::query(
                    "INSERT INTO ci_users (username, email, password, display_name, created_at) 
                     VALUES (?, ?, ?, ?, NOW())",
                    [$username, $email, $password_hash, $display_name]
                );
                
                success('Registrazione completata! Ora puoi accedere.');
                redirect('/?page=login');
            } catch (Exception $e) {
                error('Errore durante la registrazione. Riprova.');
            }
        }
    }
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            
            <div class="card shadow">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-person-plus-fill text-primary" style="font-size: 3rem;"></i>
                        <h3 class="mt-2">Registrati</h3>
                        <p class="text-muted">Crea il tuo account gratuito</p>
                    </div>
                    
                    <form method="POST">
                        <!-- Display Name -->
                        <div class="mb-3">
                            <label for="display_name" class="form-label">
                                <i class="bi bi-person-badge-fill"></i> Nome Completo
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="display_name" 
                                   name="display_name" 
                                   placeholder="Es: Mario Rossi"
                                   required 
                                   autofocus
                                   value="<?= sanitize($_POST['display_name'] ?? '') ?>">
                            <small class="form-text text-muted">
                                Il nome che verrà mostrato nell'app
                            </small>
                        </div>
                        
                        <!-- Username -->
                        <div class="mb-3">
                            <label for="username" class="form-label">
                                <i class="bi bi-person-circle"></i> Username
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="username" 
                                   name="username" 
                                   placeholder="Scegli uno username (min 3 caratteri)"
                                   required
                                   minlength="3"
                                   value="<?= sanitize($_POST['username'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope-fill"></i> Email
                            </label>
                            <input type="email"
                                class="form-control"
                                id="email"
                                name="email"
                                placeholder="La tua email"
                                required
                                value="<?= sanitize($_POST['email'] ?? '') ?>">
                        </div>
                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock-fill"></i> Password
                            </label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Scegli una password (min 6 caratteri)"
                                   required
                                   minlength="6">
                        </div>
                        
                        <!-- Conferma Password -->
                        <div class="mb-3">
                            <label for="password_confirm" class="form-label">
                                <i class="bi bi-lock-fill"></i> Conferma Password
                            </label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password_confirm" 
                                   name="password_confirm" 
                                   placeholder="Ripeti la password"
                                   required
                                   minlength="6">
                        </div>
                        
                        <!-- Submit -->
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="bi bi-person-plus-fill"></i> Registrati
                        </button>
                        
                        <!-- Link login -->
                        <div class="text-center">
                            <small class="text-muted">
                                Hai già un account? 
                                <a href="<?= BASE_PATH ?>/?page=login">Accedi qui</a>
                            </small>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Info -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6><i class="bi bi-gift-fill text-success"></i> Cosa puoi fare con un account:</h6>
                    <ul class="small mb-0">
                        <li>Creare libri personalizzati di incantesimi</li>
                        <li>Salvare le tue creature preferite</li>
                        <li>Condividere i tuoi libri con altri giocatori</li>
                        <li>Stampare carte personalizzate per le tue sessioni</li>
                    </ul>
                </div>
            </div>
            
        </div>
    </div>
</div>
