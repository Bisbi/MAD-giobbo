<?php
// Questi controlli assumono che le funzioni isAdmin() e isUser() siano definite 
// in config.php o in un file incluso prima dell'header/navbar.

$is_admin = isAdmin();
$is_user = isUser();
$user_name = '';

if ($is_admin) {
    $user_name = $_SESSION['admin_username'] ?? 'Admin';
} elseif ($is_user) {
    $user_name = $_SESSION['user_display_name'] ?? $_SESSION['user_username'] ?? 'Utente';
}

// Determina pagina attiva
$current_page = $_GET['page'] ?? 'home';
?><nav class="navbar navbar-expand-lg navbar-dark bg-gradient sticky-top">
    <div class="container-fluid">
        <!-- Brand -->
        <a class="navbar-brand fw-bold d-flex align-items-center" href="<?= BASE_PATH ?>/">
            <img src="<?= BASE_PATH ?>/icons/android/android-launchericon-48-48.png" alt="Logo" width="30" height="30" class="me-2">
            <?= APP_NAME ?>
        </a>
        
        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Menu -->
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'home' ? 'active' : '' ?>" href="<?= BASE_PATH ?>/">
                        <i class="bi bi-house-fill"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'spells' ? 'active' : '' ?>" href="<?= BASE_PATH ?>/?page=spells">
                        <i class="bi bi-book-fill"></i> Incantesimi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'creatures' ? 'active' : '' ?>" href="<?= BASE_PATH ?>/?page=creatures">
                        <i class="bi bi-bug-fill"></i> Creature
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'books' ? 'active' : '' ?>" href="<?= BASE_PATH ?>/?page=books">
                        <i class="bi bi-journals"></i> Libri
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'about' ? 'active' : '' ?>" href="<?= BASE_PATH ?>/?page=about">
                        <img src="icons/kofi_symbol.webp" alt="Girl in a jacket" width="15" height="15"> Supportaci
                    </a>
                </li>
                
                <?php if ($is_admin): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'admin' ? 'active' : '' ?>" href="<?= BASE_PATH ?>/?page=admin">
                        <i class="bi bi-shield-fill-check"></i> Admin
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            
            <!-- User Actions -->
            <div class="d-flex align-items-center">
                <!-- Dark Mode Toggle - SENZA title per evitare tooltip -->
                <button id="themeToggle" class="theme-toggle me-3" aria-label="Cambia tema">
                    <i id="themeIcon" class="bi bi-moon-fill"></i>
                </button>
                
                <?php if ($is_admin || $is_user): ?>
                    <a href="<?= BASE_PATH ?>/?page=profile" class="navbar-text me-3 d-flex align-items-center text-decoration-none" style="color: inherit;">
                        <?php if (!empty($_SESSION['user_avatar'])): ?>
                            <img src="<?= BASE_PATH ?>/<?= sanitize($_SESSION['user_avatar']) ?>" alt="Avatar" class="rounded-circle me-2" style="width: 28px; height: 28px; object-fit: cover;">
                        <?php else: ?>
                            <i class="bi bi-person-circle me-2"></i>
                        <?php endif; ?>
                        <?= sanitize($user_name) ?>
                    </a>
                    <a href="<?= BASE_PATH ?>/?page=logout" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_PATH ?>/?page=login" class="btn btn-outline-light btn-sm me-2">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </a>
                    <a href="<?= BASE_PATH ?>/?page=register" class="btn btn-light btn-sm">
                        <i class="bi bi-person-plus-fill"></i> Registrati
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
