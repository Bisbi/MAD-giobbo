<?php
require_once __DIR__ . '/../config.php';

$avatar_urls = [];

// Raccoglie gli URL degli avatar da stampare (da POST o GET)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['avatar_urls']) && is_array($_POST['avatar_urls'])) {
    $avatar_urls = $_POST['avatar_urls'];
} 
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['avatar_url'])) {
    $avatar_urls[] = $_GET['avatar_url'];
}

if (empty($avatar_urls)) {
    die('Nessun avatar selezionato per la stampa.');
}

// Controllo di sicurezza
foreach ($avatar_urls as $url) {
    if (strpos($url, 'uploads/avatars/') !== 0 && strpos($url, 'assets/img/default_avatars/') !== 0) {
        die('Rilevato percorso avatar non valido.');
    }
}

// Prepara i dati per la stampa
$tent_sheets = array_chunk($avatar_urls, 6);
$token_sheets = array_chunk($avatar_urls, 9);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Stampa Set Completo: Tende e Token</title>
<style>
    body { font-family: sans-serif; background-color: #f0f0f0; margin: 0; padding: 0; }
    @media print {
        body { background-color: #fff; }
        .print-controls, .section-title { display: none; }
        @page { size: A4 portrait; margin: 1cm; }

        /* Forza Firefox e altri browser a stampare bordi e sfondi */
        .tent-10x5, .stat-circle, .token {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
    .print-controls { text-align: center; padding: 20px; }
    .print-controls button { padding: 10px 20px; font-size: 16px; cursor: pointer; }
    .section-title { text-align: center; font-size: 1.5rem; font-weight: bold; margin: 20px 0; color: #333; }
    .page-break { page-break-before: always; }
    
    .sheet-tents {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        grid-template-rows: repeat(2, 1fr);
        gap: 1cm;
        width: 190mm;
        height: 277mm;
        margin: 0 auto;
        padding: 0;
        background-color: white;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        page-break-after: always;
        align-items: start;
    }
    .tent-10x5 {
        width: 5cm;
        height: 10cm;
        /* MODIFICA: Bordo nero e leggermente più spesso per essere più visibile */
        border: 1.5px solid black;
        display: flex;
        flex-direction: column;
        margin: 0 auto;
    }
    .fold-line {
        height: 1.5px; /* Altezza della linea */
        width: 100%;
        /* MODIFICA: Sostituita la linea tratteggiata con un'immagine SVG per compatibilità con Firefox */
        background-image: url("data:image/svg+xml,%3csvg width='100%25' height='100%25' xmlns='http://www.w3.org/2000/svg'%3e%3crect width='100%25' height='100%25' fill='none' stroke='black' stroke-width='2' stroke-dasharray='6,5' stroke-dashoffset='0' stroke-linecap='square'/%3e%3c/svg%3e");
    }
    .tent-side {
        width: 100%;
        height: 50%;
        box-sizing: border-box;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .player-side {
        transform: rotate(180deg);
    }
    .player-side img { width: 100%; height: 100%; object-fit: cover; }
    
    .dm-side {
        padding: 4mm;
        flex-direction: column;
        gap: 3mm;
        font-family: 'Garamond', serif;
    }
    .name-line {
    width: 100%;
    border: none;
    border-top: 1.5px solid black;
    margin: 0;
    height: 1px;
}
    .stats-circles {
        display: flex;
        justify-content: space-around;
        width: 100%;
    }
    .stat-item-vertical {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .stat-circle {
        width: 1.2cm;
        height: 1.2cm;
        /* MODIFICA: Bordo nero e leggermente più spesso */
        border: 1.5px solid black;
        border-radius: 50%;
    }
    .stat-label {
        font-size: 9px;
        font-weight: bold;
        margin-top: 1mm;
        color: #333;
    }

    /* --- Stili per i Token Rotondi (Bordo Nero) --- */
    .token-sheet { width: 210mm; height: 297mm; padding: 1cm; box-sizing: border-box; display: grid; grid-template-columns: repeat(3, 1fr); grid-template-rows: repeat(3, 1fr); gap: 1.5cm; align-content: start; justify-items: center; }
    .token { width: 2.54cm; height: 2.54cm; border-radius: 50%; border: 1.5px solid black; overflow: hidden; } /* MODIFICA: Bordo nero e più spesso */
    .token img { width: 100%; height: 100%; object-fit: cover; }
</style>
</head>
<body>

    <div class="print-controls">
        <p>Stai per stampare <?= count($avatar_urls) ?> set di Tende e Token.</p>
        <button onclick="window.print()">Stampa Tutto</button>
    </div>

    <div class="section-title">Tende da Iniziativa</div>
    <?php foreach ($tent_sheets as $tent_group): ?>
    <div class="sheet-tents">
        <?php foreach ($tent_group as $avatar_url): ?>
            <div class="tent-10x5">
                <div class="tent-side player-side">
                    <img src="<?= BASE_PATH ?>/<?= htmlspecialchars($avatar_url) ?>" alt="Avatar">
                </div>
                <div class="fold-line"></div>
                <div class="tent-side dm-side">
                    <div class="name-field"></div>

					<hr class="name-line">
                    <div class="stats-circles">
                        <div class="stat-item-vertical">
                            <div class="stat-circle"></div>
                            <div class="stat-label">CA</div>
                        </div>
                        <div class="stat-item-vertical">
                            <div class="stat-circle"></div>
                            <div class="stat-label">PF</div>
                        </div>
                        <div class="stat-item-vertical">
                            <div class="stat-circle"></div>
                            <div class="stat-label">PP</div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

    <div class="page-break">
        <div class="section-title">Token Rotondi</div>
        <?php foreach ($token_sheets as $sheet_avatars): ?>
            <div class="token-sheet">
                <?php foreach ($sheet_avatars as $avatar_url): ?>
                    <div class="token"><img src="<?= BASE_PATH ?>/<?= htmlspecialchars($avatar_url) ?>" alt="Token"></div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>

</body>
</html>