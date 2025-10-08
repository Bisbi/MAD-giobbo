<?php
/**
 * Login - Admin e User
 */

$page_title = 'Login';

// Se già loggato, redirect
if (isAdmin() || isUser()) {
    redirect('/');
}

// Processa form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $type = $_POST['type'] ?? 'user'; // 'user' o 'admin'
    
    if (!$username || !$password) {
        error('Compila tutti i campi!');
    } else {
        // Login ADMIN
        if ($type === 'admin') {
            $admin = Database::fetch(
                "SELECT * FROM ci_admin WHERE username = ?", 
                [$username]
            );
            
            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                
                // Log attività
                Database::query(
                    "INSERT INTO ci_logs (admin_id, action, details, ip_address) 
                     VALUES (?, 'login', 'Admin login successful', ?)",
                    [$admin['id'], $_SERVER['REMOTE_ADDR']]
                );
                
                // Aggiorna last_login
                Database::query(
                    "UPDATE ci_admin SET last_login = NOW() WHERE id = ?",
                    [$admin['id']]
                );
                
                success('Benvenuto Admin!');
                redirect('/?page=admin');
            } else {
                error('Credenziali admin non valide!');
            }
        }
        // Login USER
        else {
            $user = Database::fetch(
                "SELECT * FROM ci_users WHERE username = ? AND active = 1", 
                [$username]
            );
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_username'] = $user['username'];
                $_SESSION['user_display_name'] = $user['display_name'];
				$_SESSION['user_avatar'] = $user['avatar_url'];
                
                // Aggiorna last_login
                Database::query(
                    "UPDATE ci_users SET last_login = NOW() WHERE id = ?",
                    [$user['id']]
                );
                
                success('Benvenuto ' . $user['display_name'] . '!');
                redirect('/?page=books');
            } else {
                error('Credenziali utente non valide o account disattivato!');
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
                        <i class="bi bi-box-arrow-in-right text-primary" style="font-size: 3rem;"></i>
                        <h3 class="mt-2">Accedi</h3>
                        <p class="text-muted">Sistema Carte D&D 5e</p>
                    </div>
                    
                    <form method="POST">
                        <!-- Tipo login -->
                        <div class="mb-3">
                            <label class="form-label">Tipo Account</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="type" id="typeUser" value="user" checked>
                                <label class="btn btn-outline-primary" for="typeUser">
                                    <i class="bi bi-person-fill"></i> Utente
                                </label>
                                
                                <input type="radio" class="btn-check" name="type" id="typeAdmin" value="admin">
                                <label class="btn btn-outline-danger" for="typeAdmin">
                                    <i class="bi bi-shield-fill-check"></i> Admin
                                </label>
                            </div>
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
                                   placeholder="Inserisci username"
                                   required 
                                   autofocus>
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
                                   placeholder="Inserisci password"
                                   required>
                        </div>
                        
                        <!-- Submit -->
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="bi bi-box-arrow-in-right"></i> Accedi
                        </button>
                        
                        <!-- Link registrazione -->
                        <div class="text-center">
                            <small class="text-muted">
                                Non hai un account? 
                                <a href="<?= BASE_PATH ?>/?page=register">Registrati qui</a>
                            </small>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Info accounts -->
            <div class="card mt-3">
                <div class="card-body">
                    <small class="text-muted">
                        <i class="bi bi-info-circle-fill"></i> 
                        <strong>Account Utente:</strong> Crea e gestisci i tuoi libri personali<br>
                        <i class="bi bi-shield-fill-check"></i> 
                        <strong>Account Admin:</strong> Accesso completo al pannello amministrazione
                    </small>
                </div>
            </div>
            
        </div>
    </div>
</div>
