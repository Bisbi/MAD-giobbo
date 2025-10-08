<?php
/**
 * Homepage - Dashboard principale
 */

$page_title = 'Dashboard';

// Carica statistiche
try {
    $stats = [
        'spells' => Database::fetch("SELECT COUNT(*) as count FROM ci_spells WHERE active = 1")['count'] ?? 0,
        'creatures' => Database::fetch("SELECT COUNT(*) as count FROM ci_creatures WHERE active = 1")['count'] ?? 0,
        'books' => Database::fetch("SELECT COUNT(*) as count FROM ci_spellbooks")['count'] ?? 0,
    ];
    $stats['total'] = $stats['spells'] + $stats['creatures'];
} catch (Exception $e) {
    $stats = ['spells' => 0, 'creatures' => 0, 'books' => 0, 'total' => 0];
}
?>

<div class="container mt-4">
    
    <!-- Hero Section -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="display-4 fw-bold d-flex align-items-center justify-content-center">
                <img src="<?= BASE_PATH ?>/icons/android/android-launchericon-96-96.png" alt="Logo" width="70" height="70" class="me-3"> 
                Benvenuto in <?= APP_NAME ?>
            </h1>
            <p class="lead text-muted">
                Componi e stampa il tuo Grimorio di Incantesimi e la tua collezione di creature, registrati o fai il login e crea il tuo libro.
            </p>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="stat-card">
                <h3><?= $stats['spells'] ?></h3>
                <p class="mb-0"><i class="bi bi-book-fill"></i> Incantesimi</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h3><?= $stats['creatures'] ?></h3>
                <p class="mb-0"><i class="bi bi-bug-fill"></i> Creature</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h3><?= $stats['books'] ?></h3>
                <p class="mb-0"><i class="bi bi-journals"></i> Libri</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h3><?= $stats['total'] ?></h3>
                <p class="mb-0"><i class="bi bi-collection-fill"></i> Carte Totali</p>
            </div>
        </div>
    </div>
    
    <!-- Feature Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-book-fill text-primary" style="font-size: 3rem;"></i>
                    <h5 class="card-title mt-3">Incantesimi</h5>
                    <p class="card-text">
                        Visualizza e stampa carte per tutti i tuoi incantesimi preferiti di D&D 5e.
                    </p>
                    <a href="<?= BASE_PATH ?>/?page=spells" class="btn btn-primary">
                        <i class="bi bi-arrow-right"></i> Esplora
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-bug-fill text-success" style="font-size: 3rem;"></i>
                    <h5 class="card-title mt-3">Creature</h5>
                    <p class="card-text">
                        Famigli, compagni animali, evocazioni e cavalcature per le tue avventure.
                    </p>
                    <a href="<?= BASE_PATH ?>/?page=creatures" class="btn btn-success">
                        <i class="bi bi-arrow-right"></i> Esplora
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-journals text-warning" style="font-size: 3rem;"></i>
                    <h5 class="card-title mt-3">Libri</h5>
                    <p class="card-text">
                        Crea raccolte personalizzate di incantesimi per i tuoi personaggi.
                    </p>
                    <a href="<?= BASE_PATH ?>/?page=books" class="btn btn-warning">
                        <i class="bi bi-arrow-right"></i> Esplora
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Nuova Sezione Chat -->
    <div class="row mt-5">
        <div class="col-12">
            <h2 class="h3 mb-3 text-indigo-400">
                <i class="bi bi-chat-dots-fill me-2"></i> Chat di Condivisione
            </h2>
            <div id="chat-root-container" style="height: 600px; border-radius: 0.5rem; overflow: hidden;">
                <!-- Qui verrà montato il componente React ChatApp -->
            </div>
        </div>
    </div>
</div>
