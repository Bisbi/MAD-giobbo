<?php
/**
 * 🎲 Sistema Carte D&D - Setup Automatico
 * Installazione guidata in 5 minuti
 */

// Impedisci esecuzione se già installato
if (file_exists('.env') && file_exists('config.php')) {
    $checkInstall = true;
    include 'config.php';
    try {
        $check = Database::fetch("SELECT COUNT(*) as count FROM ci_admin");
        if ($check['count'] > 0) {
            die("⚠️ Sistema già installato! <a href='index.php'>Vai alla homepage</a>");
        }
    } catch (Exception $e) {
        // Database non ancora configurato, continua con setup
    }
}

$step = $_GET['step'] ?? 1;
$errors = [];
$success = [];

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎲 Setup Sistema Carte D&D</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .step { background: #6b73ff; color: white; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { background: #6b73ff; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background: #5a63e6; }
        .error { background: #fee; border: 1px solid #fcc; padding: 10px; border-radius: 4px; color: #c33; margin: 10px 0; }
        .success { background: #efe; border: 1px solid #cfc; padding: 10px; border-radius: 4px; color: #363; margin: 10px 0; }
        .note { background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; border-radius: 4px; color: #856404; margin: 10px 0; }
        .progress { background: #eee; height: 20px; border-radius: 10px; margin: 20px 0; }
        .progress-bar { background: #6b73ff; height: 100%; border-radius: 10px; transition: width 0.3s; }
    </style>
</head>
<body>

<div class="container">
    <h1>🎲 Setup Sistema Carte D&D</h1>
    <div class="step">Passo <?= $step ?> di 4</div>
    
    <div class="progress">
        <div class="progress-bar" style="width: <?= $step * 25 ?>%"></div>
    </div>

<?php

if ($_POST && $step == 1) {
    // Step 1: Configurazione Database
    $dbHost = trim($_POST['db_host']);
    $dbName = trim($_POST['db_name']);
    $dbUser = trim($_POST['db_user']);
    $dbPass = trim($_POST['db_pass']);
    $appUrl = rtrim(trim($_POST['app_url']), '/');
    
    // Validazione
    if (!$dbHost) $errors[] = "Host database richiesto";
    if (!$dbName) $errors[] = "Nome database richiesto";  
    if (!$dbUser) $errors[] = "Username database richiesto";
    if (!$appUrl) $errors[] = "URL applicazione richiesto";
    
    if (empty($errors)) {
        // Test connessione database
        try {
            $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Crea file .env
            $envContent = "DB_HOST=$dbHost\n";
            $envContent .= "DB_NAME=$dbName\n";
            $envContent .= "DB_USER=$dbUser\n";
            $envContent .= "DB_PASS=$dbPass\n\n";
            $envContent .= "APP_NAME=Carte D&D 5e\n";
            $envContent .= "APP_URL=$appUrl\n";
            $envContent .= "DEBUG=false\n\n";
            $envContent .= "ADMIN_USER=admin@carted&d.com\n";
            $envContent .= "ADMIN_PASS=admin123\n\n";
            $envContent .= "SECRET_KEY=" . bin2hex(random_bytes(32)) . "\n";
            
            file_put_contents('.env', $envContent);
            
            header("Location: setup.php?step=2");
            exit;
            
        } catch (Exception $e) {
            $errors[] = "Errore connessione database: " . $e->getMessage();
        }
    }
}

if ($_POST && $step == 2) {
    // Step 2: Installazione Tabelle
    include 'config.php';
    
    try {
        $schema = file_get_contents('schema.sql');
        // Rimuovi commenti e dividi per query
        $queries = array_filter(array_map('trim', explode(';', $schema)));
        
        foreach ($queries as $query) {
            if (!empty($query) && !str_starts_with($query, '--')) {
                Database::query($query);
            }
        }
        
        $success[] = "Database installato con successo!";
        header("Location: setup.php?step=3");
        exit;
        
    } catch (Exception $e) {
        $errors[] = "Errore installazione database: " . $e->getMessage();
    }
}

if ($_POST && $step == 3) {
    // Step 3: Configurazione Admin
    include 'config.php';
    
    $adminEmail = trim($_POST['admin_email']);
    $adminPass = trim($_POST['admin_pass']);
    $adminPassConfirm = trim($_POST['admin_pass_confirm']);
    
    if (!$adminEmail) $errors[] = "Email admin richiesta";
    if (!$adminPass) $errors[] = "Password admin richiesta";
    if ($adminPass !== $adminPassConfirm) $errors[] = "Le password non coincidono";
    if (strlen($adminPass) < 6) $errors[] = "Password minimo 6 caratteri";
    
    if (empty($errors)) {
        try {
            // Aggiorna admin
            $hashedPassword = password_hash($adminPass, PASSWORD_DEFAULT);
            Database::query("UPDATE ci_admin SET username = ?, password = ? WHERE id = 1", 
                           [$adminEmail, $hashedPassword]);
            
            // Aggiorna .env
            $env = file_get_contents('.env');
            $env = preg_replace('/ADMIN_USER=.*/', "ADMIN_USER=$adminEmail", $env);
            $env = preg_replace('/ADMIN_PASS=.*/', "ADMIN_PASS=$adminPass", $env);
            file_put_contents('.env', $env);
            
            header("Location: setup.php?step=4");
            exit;
            
        } catch (Exception $e) {
            $errors[] = "Errore configurazione admin: " . $e->getMessage();
        }
    }
}

// Mostra form in base al passo
switch ($step) {
    case 1: ?>
        <h2>📊 Configurazione Database</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <div>❌ <?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="note">
            <strong>💡 Informazioni Hosting:</strong><br>
            • Crea un database MySQL nel tuo hosting<br>
            • Annota nome database, username e password<br>  
            • Host è solitamente "localhost"
        </div>
        
        <form method="post">
            <div class="form-group">
                <label for="db_host">Host Database:</label>
                <input type="text" id="db_host" name="db_host" value="<?= $_POST['db_host'] ?? 'localhost' ?>" required>
            </div>
            
            <div class="form-group">
                <label for="db_name">Nome Database:</label>
                <input type="text" id="db_name" name="db_name" value="<?= $_POST['db_name'] ?? '' ?>" required>
            </div>
            
            <div class="form-group">
                <label for="db_user">Username Database:</label>
                <input type="text" id="db_user" name="db_user" value="<?= $_POST['db_user'] ?? '' ?>" required>
            </div>
            
            <div class="form-group">
                <label for="db_pass">Password Database:</label>
                <input type="password" id="db_pass" name="db_pass" value="<?= $_POST['db_pass'] ?? '' ?>">
            </div>
            
            <div class="form-group">
                <label for="app_url">URL Sito (es. https://miosito.com/carte):</label>
                <input type="url" id="app_url" name="app_url" value="<?= $_POST['app_url'] ?? 'http://localhost/carte' ?>" required>
            </div>
            
            <button type="submit">Continua →</button>
        </form>
        
        <?php break;
        
    case 2: ?>
        <h2>🛢️ Installazione Database</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <div>❌ <?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <p>Configurazione database completata! Ora installiamo le tabelle e i dati di esempio.</p>
        
        <div class="note">
            <strong>🎯 Cosa verrà installato:</strong><br>
            • Tabelle per incantesimi, creature e admin<br>
            • 4 incantesimi di esempio<br>
            • 4 creature/famigli di esempio<br>
            • Account admin temporaneo
        </div>
        
        <form method="post">
            <button type="submit">Installa Database →</button>
        </form>
        
        <?php break;
        
    case 3: ?>
        <h2>👑 Configurazione Admin</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <div>❌ <?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <p>Crea il tuo account amministratore per gestire incantesimi e creature.</p>
        
        <form method="post">
            <div class="form-group">
                <label for="admin_email">Email Admin:</label>
                <input type="email" id="admin_email" name="admin_email" value="<?= $_POST['admin_email'] ?? 'admin@carted&d.com' ?>" required>
            </div>
            
            <div class="form-group">
                <label for="admin_pass">Password Admin:</label>
                <input type="password" id="admin_pass" name="admin_pass" required>
            </div>
            
            <div class="form-group">
                <label for="admin_pass_confirm">Conferma Password:</label>
                <input type="password" id="admin_pass_confirm" name="admin_pass_confirm" required>
            </div>
            
            <button type="submit">Crea Admin →</button>
        </form>
        
        <?php break;
        
    case 4: ?>
        <h2>🎉 Installazione Completata!</h2>
        
        <div class="success">
            <strong>✅ Sistema Carte D&D installato con successo!</strong>
        </div>
        
        <h3>🎯 Prossimi Passi:</h3>
        <ol>
            <li><strong>Elimina setup.php</strong> per sicurezza (opzionale)</li>
            <li><a href="index.php">Vai alla Homepage</a> per vedere il sistema</li>
            <li><a href="admin.php">Accedi al Pannello Admin</a> per gestire contenuti</li>
            <li>Inizia ad aggiungere i tuoi incantesimi e creature!</li>
        </ol>
        
        <h3>📚 Funzionalità Disponibili:</h3>
        <ul>
            <li>🎴 <strong>Carte Incantesimi</strong> - Visualizza e stampa incantesimi D&D</li>
            <li>🐾 <strong>Carte Creature</strong> - Famigli, compagni, evocazioni</li>  
            <li>👑 <strong>Admin Panel</strong> - Gestisci tutto dal pannello admin</li>
            <li>📥 <strong>Import/Export</strong> - Carica i tuoi dati da file CSV</li>
            <li>🖨️ <strong>Stampa Ottimizzata</strong> - Layout per stampante domestica</li>
        </ul>
        
        <div class="note">
            <strong>🔐 Credenziali Admin:</strong><br>
            Email: <code><?= htmlspecialchars($_POST['admin_email'] ?? 'admin@carted&d.com') ?></code><br>
            Password: <code>[la password che hai scelto]</code>
        </div>
        
        <p style="text-align: center; margin-top: 30px;">
            <a href="index.php" style="background: #6b73ff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px;">🎲 Inizia ad Usare il Sistema!</a>
        </p>
        
        <?php break;
}

?>

</div>

</body>
</html>
