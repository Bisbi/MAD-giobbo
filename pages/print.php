<?php
/**
 * Pagina di Stampa Dedicata (A4, 3x3)
 * Gestisce la stampa di:
 * - Un intero libro (?page=print&type=book&id=BOOK_ID)
 * - Una selezione di incantesimi (?page=print&type=spells&ids=1,2,3)
 * - Una selezione di creature (?page=print&type=creatures&ids=4,5,6)
 */

require_once __DIR__ . '/../config.php';

$type = $_GET['type'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$ids_str = trim($_GET['ids'] ?? '');

$cards = [];
$spells = [];
$creatures = [];
$page_title = 'Stampa Carte';

// Carica dati in base al tipo
switch ($type) {
    case 'book':
        if ($id) {
            $book = Database::fetch("SELECT name FROM ci_spellbooks WHERE id = ?", [$id]);
            $page_title = 'Stampa: ' . ($book['name'] ?? 'Libro');
            
            $spells = Database::fetchAll("SELECT s.* FROM ci_spellbook_spells ss JOIN ci_spells s ON s.id = ss.spell_id WHERE ss.spellbook_id = ? AND s.active = 1 ORDER BY s.level ASC, s.name_it ASC", [$id]);
            $creatures_raw = Database::fetchAll("SELECT c.*, sc.overrides FROM ci_spellbook_creatures sc JOIN ci_creatures c ON c.id = sc.creature_id WHERE sc.spellbook_id = ? AND c.active = 1 ORDER BY c.name_it ASC", [$id]);
            
            // Applica override
            foreach ($creatures_raw as $creature) {
                if (!empty($creature['overrides'])) {
                    $overrides = json_decode($creature['overrides'], true);
                    if (is_array($overrides)) {
                        $creature = array_merge($creature, $overrides);
                    }
                }
                $creatures[] = $creature;
            }
        }
        break;

    case 'spells':
        if ($ids_str) {
            $ids = array_filter(explode(',', $ids_str), 'is_numeric');
            if(!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $spells = Database::fetchAll("SELECT * FROM ci_spells WHERE id IN ($placeholders) AND active = 1", $ids);
            }
        }
        break;

    case 'creatures':
        if ($ids_str) {
            $ids = array_filter(explode(',', $ids_str), 'is_numeric');
            if(!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $creatures = Database::fetchAll("SELECT * FROM ci_creatures WHERE id IN ($placeholders) AND active = 1", $ids);
            }
        }
        break;

    default:
        die('Tipo non valido specificato.');
}

// Funzione per determinare se una carta necessita di più pagine
function needsMultipleCards($type, $item) {
    if ($type === 'spell') {
        $content_length = strlen($item['description_it'] ?? '') + strlen($item['higher_levels'] ?? '');
        return $content_length > 850; // Soglia per incantesimi
    } else { // creature
        $content_length = strlen($item['actions'] ?? '') + strlen($item['special_abilities'] ?? '');
        return $content_length > 500; // Soglia per creature
    }
}

// Popola l'array delle carte, sdoppiando se necessario
foreach ($spells as $spell) {
    if (needsMultipleCards('spell', $spell)) {
        $cards[] = ['type' => 'spell', 'data' => $spell, 'part' => '1/2'];
        $cards[] = ['type' => 'spell', 'data' => $spell, 'part' => '2/2'];
    } else {
        $cards[] = ['type' => 'spell', 'data' => $spell, 'part' => null];
    }
}
foreach ($creatures as $creature) {
    if (needsMultipleCards('creature', $creature)) {
        $cards[] = ['type' => 'creature', 'data' => $creature, 'part' => '1/2'];
        $cards[] = ['type' => 'creature', 'data' => $creature, 'part' => '2/2'];
    } else {
        $cards[] = ['type' => 'creature', 'data' => $creature, 'part' => null];
    }
}

$sheets = array_chunk($cards, 9);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($page_title) ?></title>
    <style>
        /* Reset e Stili Base */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f0f0f0; }
        
        /* Controlli a schermo */
        .screen-only { text-align: center; background: white; padding: 20px; border-radius: 10px; max-width: 800px; margin: 20px auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .print-button { background: #2c3e50; color: white; border: none; padding: 12px 24px; font-size: 16px; cursor: pointer; border-radius: 5px; margin: 10px; transition: background 0.3s; }
        .print-button:hover { background: #34495e; }

        /* Stili di Stampa */
        @media print {
            body { background: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .screen-only { display: none !important; }
            .card-sheet { box-shadow: none !important; margin: 0 !important; }
        }

        /* Foglio A4 */
        .card-sheet {
            width: 210mm;
            height: 297mm;
            margin: 20px auto;
            padding: 5mm;
            background: white;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: repeat(3, 1fr);
            gap: 5mm;
            page-break-after: always;
        }

        /* CARD INCANTESIMO */
        .spell-card {
            width: 63mm; height: 88mm; border: 1.5px solid #aab; border-radius: 3mm; position: relative; overflow: hidden; background: #fdfdff; font-family: Georgia, serif; display: flex; flex-direction: column;
        }
        .spell-card-header { padding: 2mm 3mm; text-align: center; border-bottom: 1px solid #ccc; }
        .spell-card-title { font-size: 10px; font-weight: bold; line-height: 1.1; margin-bottom: 0.5mm; }
        .spell-card-type { font-size: 7px; font-style: italic; color: #557; }
        .spell-card-level { position: absolute; top: 2mm; right: 2mm; width: 6mm; height: 6mm; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: bold; border: 1px solid #335; background: #6a89cc; color: #fff; }
        .spell-card-part { position: absolute; top: 2mm; left: 2mm; background: #333; color: #fff; padding: 0.5mm 1.5mm; border-radius: 1mm; font-size: 7px; font-weight: bold; }
        .spell-card-content { padding: 1.5mm 2.5mm; font-size: 7.5px; line-height: 1.3; flex-grow: 1; overflow: hidden; }
        .spell-card-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1mm; margin-bottom: 1.5mm; font-size: 6.5px; }
        .spell-card-info-item { background: #eef2ff; border: 1px solid #dbe2f4; padding: 0.5mm 1mm; border-radius: 1mm; }
        .spell-card-description { background: #f8f9fa; border-left: 2mm solid #6a89cc; padding: 1mm; margin: 1mm 0; border-radius: 1mm; font-size: 7px; line-height: 1.4; }
        .spell-card-higher { background: #fff9e6; border: 1px solid #ffecb3; padding: 1mm; margin: 1mm 0; border-radius: 1mm; font-size: 6.5px; line-height: 1.3; }
        .spell-card-footer { padding: 1mm 2.5mm; display: flex; gap: 1mm; font-size: 7px; text-align: center; }
        .spell-card-tag { flex: 1; border: 1px solid; padding: 0.5mm 1mm; border-radius: 1mm; font-weight: bold; }
        .tag-concentration { background: #ffebee; border-color: #ffcdd2; color: #c62828; }
        .tag-ritual { background: #e3f2fd; border-color: #bbdefb; color: #1565c0; }
        .spell-card-classes { background: #f5f5f5; border: 1px dashed #ccc; padding: 0.5mm 1.5mm; border-radius: 1mm; font-size: 6px; margin-top: auto; text-align: center; }

        /* CARD CREATURA */
        .creature-card {
            width: 63mm; height: 88mm; border: 1.5px solid #333; border-radius: 3mm; position: relative; overflow: hidden; background: #fffaf0; font-family: 'Helvetica Neue', sans-serif; display: flex; flex-direction: column;
        }
        .creature-card-header { padding: 2mm; text-align: center; border-bottom: 1px solid #ddd; background: #fdf5e6; }
        .creature-card-title { font-size: 11px; font-weight: bold; margin: 0; }
        .creature-card-type { font-size: 7px; font-style: italic; color: #777; }
        .creature-card-part { position: absolute; top: 2mm; left: 2mm; background: #333; color: #fff; padding: 0.5mm 1.5mm; border-radius: 1mm; font-size: 7px; font-weight: bold; z-index: 10; }
        .creature-card-content { padding: 1.5mm 2.5mm; font-size: 7.5px; line-height: 1.2; flex-grow: 1; overflow: hidden; }
        .creature-stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2mm; text-align: center; margin-bottom: 1.5mm; }
        .creature-stat-box { background: #eee; border: 1px solid #ccc; border-radius: 1mm; padding: 0.5mm; }
        .creature-stat-box strong { font-size: 9px; }
        .creature-stat-box span { font-size: 6px; color: #555; }
        .creature-abilities { display: grid; grid-template-columns: repeat(6, 1fr); text-align: center; background: #f5f5f5; border-radius: 1mm; padding: 0.5mm; font-size: 7px; margin-bottom: 1.5mm; }
        .creature-abilities span { font-weight: bold; }
        .creature-section { margin-bottom: 1mm; }
        .creature-section h6 { font-size: 7.5px; font-weight: bold; border-bottom: 1px solid #eee; margin-bottom: 0.5mm; padding-bottom: 0.2mm; }
        .creature-section p { font-size: 7px; line-height: 1.3; }
        .creature-speeds { font-size: 7px; text-align: center; background: #f5f5f5; padding: 0.5mm; border-radius: 1mm; }
    </style>
</head>
<body>

<div class="screen-only">
    <h1><?= htmlspecialchars($page_title) ?></h1>
    <p>Trovate <?= count($cards) ?> carte totali, che verranno stampate su <?= count($sheets) ?> fogli.</p>
    <button class="print-button" onclick="window.print()">STAMPA ORA</button>
</div>

<?php foreach ($sheets as $sheet_cards): ?>
<div class="card-sheet">
    <?php foreach ($sheet_cards as $card): 
        $item = $card['data'];
        $part = $card['part'];
        
        if ($card['type'] === 'spell'): 
            $level = $item['level'];
    ?>
    <div class="spell-card">
        <div class="spell-card-level"><?= $level == 0 ? 'T' : $level ?></div>
        <?php if ($part): ?><div class="spell-card-part"><?= $part ?></div><?php endif; ?>
        <div class="spell-card-header">
            <div class="spell-card-title"><?= strtoupper(htmlspecialchars($item['name_it'])) ?></div>
            <div class="spell-card-type"><?= htmlspecialchars($item['school']) ?> - Liv. <?= $level ?></div>
        </div>
        <div class="spell-card-content">
            <?php if (!$part || $part === '1/2'): ?>
            <div class="spell-card-info-grid">
                <div class="spell-card-info-item"><strong>T:</strong> <?= htmlspecialchars($item['casting_time']) ?></div>
                <div class="spell-card-info-item"><strong>G:</strong> <?= htmlspecialchars($item['range_distance']) ?></div>
                <div class="spell-card-info-item"><strong>C:</strong> <?= htmlspecialchars($item['components']) ?></div>
                <div class="spell-card-info-item"><strong>D:</strong> <?= htmlspecialchars($item['duration']) ?></div>
            </div>
            <?php endif; ?>
            <div class="spell-card-description">
                <?php 
                $desc = $item['description_it'] ?? '';
                if ($part === '1/2') echo nl2br(htmlspecialchars(substr($desc, 0, 850)));
                elseif ($part === '2/2') echo nl2br(htmlspecialchars(substr($desc, 850)));
                else echo nl2br(htmlspecialchars($desc));
                ?>
            </div>
            <?php if (!empty($item['higher_levels']) && (!$part || $part === '2/2')): ?>
            <div class="spell-card-higher">
                <strong>A Livelli Sup.:</strong> <?= nl2br(htmlspecialchars($item['higher_levels'])) ?>
            </div>
            <?php endif; ?>
            <div class="spell-card-footer">
                <?php if (!empty($item['concentration'])): ?><div class="spell-card-tag tag-concentration">Concentrazione</div><?php endif; ?>
                <?php if (!empty($item['ritual'])): ?><div class="spell-card-tag tag-ritual">Rituale</div><?php endif; ?>
            </div>
            <?php if (!empty($item['classes']) && (!$part || $part === '2/2')): ?>
            <div class="spell-card-classes"><strong>Classi:</strong> <?= htmlspecialchars($item['classes']) ?></div>
            <?php endif; ?>
        </div>
    </div>
    <?php else: // CREATURA ?>
    <div class="creature-card">
        <?php if ($part): ?><div class="creature-card-part"><?= $part ?></div><?php endif; ?>
        <div class="creature-card-header">
            <h2 class="creature-card-title"><?= strtoupper(htmlspecialchars($item['name_it'])) ?></h2>
            <p class="creature-card-type"><?= htmlspecialchars($item['size']) ?> <?= htmlspecialchars(ucfirst($item['creature_type'])) ?></p>
        </div>
        <div class="creature-card-content">
            <?php if (!$part || $part === '1/2'): ?>
            <div class="creature-stats-grid">
                <div class="creature-stat-box"><strong><?= sanitize($item['armor_class']) ?></strong><br><span>AC</span></div>
                <div class="creature-stat-box"><strong><?= sanitize($item['hit_points']) ?></strong><br><span>HP</span></div>
                <div class="creature-stat-box"><strong><?= sanitize($item['challenge_rating']) ?></strong><br><span>CR</span></div>
            </div>
            <div class="creature-abilities">
                <div><span>FOR</span><br><?= $item['str'] ?></div>
                <div><span>DES</span><br><?= $item['dex'] ?></div>
                <div><span>COS</span><br><?= $item['con'] ?></div>
                <div><span>INT</span><br><?= $item['int'] ?></div>
                <div><span>SAG</span><br><?= $item['wis'] ?></div>
                <div><span>CAR</span><br><?= $item['cha'] ?></div>
            </div>
            <div class="creature-speeds">
                <strong>Velocità:</strong> 
                <?php 
                    $speeds = [];
                    if(!empty($item['speed_ground'])) $speeds[] = "Terra {$item['speed_ground']}m";
                    if(!empty($item['speed_fly'])) $speeds[] = "Volo {$item['speed_fly']}m";
                    if(!empty($item['speed_swim'])) $speeds[] = "Nuoto {$item['speed_swim']}m";
                    echo implode(', ', $speeds);
                ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($item['actions']) && (!$part || $part === '1/2')): ?>
            <div class="creature-section">
                <h6>Azioni</h6>
                <p><?= nl2br(htmlspecialchars($item['actions'])) ?></p>
            </div>
            <?php endif; ?>

            <?php if (!empty($item['special_abilities']) && (!$part || $part === '2/2')): ?>
            <div class="creature-section">
                <h6>Abilità Speciali</h6>
                <p><?= nl2br(htmlspecialchars($item['special_abilities'])) ?></p>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($item['senses']) || !empty($item['skills'])): ?>
            <div class="creature-section">
                <?php if(!empty($item['senses'])): ?><p><strong>Sensi:</strong> <?= htmlspecialchars($item['senses']) ?></p><?php endif; ?>
                <?php if(!empty($item['skills'])): ?><p><strong>Abilità:</strong> <?= htmlspecialchars($item['skills']) ?></p><?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endforeach; ?>
    <?php for ($i = count($sheet_cards); $i < 9; $i++): ?>
    <div style="width:63mm; height:88mm;"></div>
    <?php endfor; ?>
</div>
<?php endforeach; ?>

</body>
</html>
