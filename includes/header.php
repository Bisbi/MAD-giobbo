<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? APP_NAME ?> - <?= APP_NAME ?></title>
	<!-- FIX FLASH DARK MODE: Esecuzione immediata del tema salvato -->
    <script>
        // Funzione immediata per prevenire il FOUC (Flash of Unstyled Content)
        (function() {
            // 1. Legge il tema salvato o usa 'light' come default
            const savedTheme = localStorage.getItem('theme') || 'light';
            
            // 2. Applica immediatamente l'attributo data-theme al tag html
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    <!-- ************************************************************ -->
    
    <!-- I tag <meta http-equiv="Cache-Control"> sono stati rimossi. -->
    <!-- Ora ci affidiamo esclusivamente ai PHP headers in no-cache.php (caricato tramite config.php). -->
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <?php 
    // Ora usiamo l'helper 'asset()' definito in no-cache.php per maggiore coerenza
    // Questo richiede che la funzione getMessages() sia definita e disponibile.
    
    // Custom CSS - CON VERSIONING tramite la funzione 'asset'
    ?>
    <link rel="stylesheet" href="<?= asset('/assets/css/custom.css') ?>">
    
    <!-- Print CSS -->
    <link rel="stylesheet" href="<?= asset('/assets/css/print.css') ?>" media="print">
	
	<link rel="apple-touch-icon" href="<?= BASE_PATH ?>/icons/ios/180.png">
	
	<link rel="icon" type="image/png" sizes="192x192" href="<?= BASE_PATH ?>/icons/android/android-launchericon-192-192.png">
    
    <link rel="icon" type="image/png" href="<?= BASE_PATH ?>/icons/android/android-launchericon-48-48.png">
    
    <!-- PWA Meta -->
    <meta name="theme-color" content="#6b73ff">
    <link rel="manifest" href="<?= BASE_PATH ?>/manifest.json">
    
    <!-- Preconnect per performance -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
</head>
<body class="d-flex flex-column min-vh-100"><?php
    // Prepara messaggi per i toast JavaScript (script inline senza spazi)
    if (function_exists('getMessages')) {
        $messages = getMessages();
        $toastMessages = [];
        if (!empty($messages['success'])) {
            $toastMessages[] = ['type' => 'success', 'message' => function_exists('sanitize') ? sanitize($messages['success']) : $messages['success']];
        }
        if (!empty($messages['error'])) {
            $toastMessages[] = ['type' => 'error', 'message' => function_exists('sanitize') ? sanitize($messages['error']) : $messages['error']];}
        if (!empty($toastMessages)) {
            echo '<script>window.systemMessages=' . json_encode($toastMessages) . ';</script>';
        }
    }
