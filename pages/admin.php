<?php
/**
 * Admin Panel - Controllore Principale
 * FIXED: Import CSV, Modal ID, Action names
 */

$page_title = 'Admin Panel';
requireAdmin();

$admin_id = $_SESSION['admin_id'] ?? 0;
$ip_address = $_SERVER['REMOTE_ADDR'];

// ===================================================================
// GESTIONE AZIONI POST
// ===================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // --- SALVA INCANTESIMO ---
    if ($action === 'save_spell') {
        $spell_id = (int)($_POST['spell_id'] ?? 0);
        $data = [
            'name_it' => trim($_POST['name_it'] ?? ''),
            'name_en' => trim($_POST['name_en'] ?? ''),
            'level' => (int)($_POST['level'] ?? 0),
            'school' => trim($_POST['school'] ?? ''),
            'classes' => trim($_POST['classes'] ?? ''),
            'casting_time' => trim($_POST['casting_time'] ?? ''),
            'range_distance' => trim($_POST['range_distance'] ?? ''),
            'components' => trim($_POST['components'] ?? ''),
            'duration' => trim($_POST['duration'] ?? ''),
            'description_it' => trim($_POST['description_it'] ?? ''),
            'higher_levels' => trim($_POST['higher_levels'] ?? ''),
            'ritual' => isset($_POST['ritual']) ? 1 : 0,
            'concentration' => isset($_POST['concentration']) ? 1 : 0,
        ];

        if ($spell_id) {
            $data['id'] = $spell_id;
            Database::query("UPDATE ci_spells SET name_it=:name_it, name_en=:name_en, level=:level, school=:school, classes=:classes, casting_time=:casting_time, range_distance=:range_distance, components=:components, duration=:duration, description_it=:description_it, higher_levels=:higher_levels, ritual=:ritual, concentration=:concentration WHERE id=:id", $data);
            success('Incantesimo aggiornato!');
        } else {
            Database::query("INSERT INTO ci_spells (name_it, name_en, level, school, classes, casting_time, range_distance, components, duration, description_it, higher_levels, ritual, concentration, active) VALUES (:name_it, :name_en, :level, :school, :classes, :casting_time, :range_distance, :components, :duration, :description_it, :higher_levels, :ritual, :concentration, 1)", $data);
            success('Nuovo incantesimo creato!');
        }
        redirect('/?page=admin&section=spells');
    }
    
    // --- SALVA CREATURA ---
    if ($action === 'save_creature') {
        $creature_id = (int)($_POST['creature_id'] ?? 0);
        $data = [
            'name_it' => trim($_POST['name_it'] ?? ''),
            'name_en' => trim($_POST['name_en'] ?? ''),
            'creature_type' => trim($_POST['creature_type'] ?? ''),
            'size' => trim($_POST['size'] ?? ''),
            'armor_class' => trim($_POST['armor_class'] ?? ''),
            'hit_points' => trim($_POST['hit_points'] ?? ''),
            'speed_ground' => (int)($_POST['speed_ground'] ?? 0),
            'speed_fly' => (int)($_POST['speed_fly'] ?? 0),
            'speed_swim' => (int)($_POST['speed_swim'] ?? 0),
            'str' => (int)($_POST['str'] ?? 10),
            'dex' => (int)($_POST['dex'] ?? 10),
            'con' => (int)($_POST['con'] ?? 10),
            'int' => (int)($_POST['int'] ?? 10),
            'wis' => (int)($_POST['wis'] ?? 10),
            'cha' => (int)($_POST['cha'] ?? 10),
            'skills' => trim($_POST['skills'] ?? ''),
            'senses' => trim($_POST['senses'] ?? ''),
            'languages' => trim($_POST['languages'] ?? ''),
            'challenge_rating' => trim($_POST['challenge_rating'] ?? '0'),
            'special_abilities' => trim($_POST['special_abilities'] ?? ''),
            'actions' => trim($_POST['actions'] ?? ''),
            'description_it' => trim($_POST['description_it'] ?? ''),
        ];

        if ($creature_id) {
            $data['id'] = $creature_id;
            Database::query("UPDATE ci_creatures SET name_it=:name_it, name_en=:name_en, creature_type=:creature_type, size=:size, armor_class=:armor_class, hit_points=:hit_points, speed_ground=:speed_ground, speed_fly=:speed_fly, speed_swim=:speed_swim, str=:str, dex=:dex, con=:con, `int`=:int, wis=:wis, cha=:cha, skills=:skills, senses=:senses, languages=:languages, challenge_rating=:challenge_rating, special_abilities=:special_abilities, actions=:actions, description_it=:description_it WHERE id=:id", $data);
            success('Creatura aggiornata!');
        } else {
            Database::query("INSERT INTO ci_creatures (name_it, name_en, creature_type, size, armor_class, hit_points, speed_ground, speed_fly, speed_swim, str, dex, con, `int`, wis, cha, skills, senses, languages, challenge_rating, special_abilities, actions, description_it, active) VALUES (:name_it, :name_en, :creature_type, :size, :armor_class, :hit_points, :speed_ground, :speed_fly, :speed_swim, :str, :dex, :con, :int, :wis, :cha, :skills, :senses, :languages, :challenge_rating, :special_abilities, :actions, :description_it, 1)", $data);
            success('Nuova creatura creata!');
        }
        redirect('/?page=admin&section=creatures');
    }

    // --- IMPORT INCANTESIMI CSV ---
    if ($action === 'import_spells') {
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['csv_file']['tmp_name'];
            $skip_header = isset($_POST['skip_header']);
            
            $handle = fopen($file, 'r');
            if ($skip_header) {
                fgetcsv($handle); // Salta intestazione
            }
            
            $imported = 0;
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < 10) continue; // Skip righe incomplete
                
                Database::query("INSERT INTO ci_spells (name_it, name_en, level, school, classes, casting_time, range_distance, components, duration, description_it, higher_levels, ritual, concentration, active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)", [
                    $row[0], $row[1], (int)$row[2], $row[3], $row[4], $row[5], $row[6], $row[7], $row[8], $row[9], $row[10] ?? '', (int)($row[11] ?? 0), (int)($row[12] ?? 0)
                ]);
                $imported++;
            }
            fclose($handle);
            
            Database::query("INSERT INTO ci_logs (admin_id, action, details, ip_address) VALUES (?, 'import_spells', ?, ?)", [$admin_id, json_encode(['count' => $imported]), $ip_address]);
            success("Importati {$imported} incantesimi!");
        } else {
            error('Errore upload file!');
        }
        redirect('/?page=admin&section=import');
    }

    // --- IMPORT CREATURE CSV ---
    if ($action === 'import_creatures') {
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['csv_file']['tmp_name'];
            $skip_header = isset($_POST['skip_header']);
            
            $handle = fopen($file, 'r');
            if ($skip_header) {
                fgetcsv($handle);
            }
            
            $imported = 0;
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < 15) continue;
                
                Database::query("INSERT INTO ci_creatures (name_it, name_en, creature_type, size, armor_class, hit_points, speed_ground, speed_fly, speed_swim, str, dex, con, `int`, wis, cha, skills, senses, languages, challenge_rating, special_abilities, actions, description_it, active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)", [
                    $row[0], $row[1], $row[2], $row[3], $row[4], $row[5], (int)$row[6], (int)($row[7] ?? 0), (int)($row[8] ?? 0), (int)$row[9], (int)$row[10], (int)$row[11], (int)$row[12], (int)$row[13], (int)$row[14], $row[15] ?? '', $row[16] ?? '', $row[17] ?? '', $row[18] ?? '0', $row[19] ?? '', $row[20] ?? '', $row[21] ?? ''
                ]);
                $imported++;
            }
            fclose($handle);
            
            Database::query("INSERT INTO ci_logs (admin_id, action, details, ip_address) VALUES (?, 'import_creatures', ?, ?)", [$admin_id, json_encode(['count' => $imported]), $ip_address]);
            success("Importate {$imported} creature!");
        } else {
            error('Errore upload file!');
        }
        redirect('/?page=admin&section=import');
    }
}

// --- GESTIONE AZIONI GET ---
if (isset($_GET['delete_spell'])) {
    $spell_id = (int)$_GET['delete_spell'];
    Database::query("UPDATE ci_spells SET active = 0 WHERE id = ?", [$spell_id]);
    Database::query("INSERT INTO ci_logs (admin_id, action, details, ip_address) VALUES (?, 'delete_spell', ?, ?)", [$admin_id, json_encode(['spell_id' => $spell_id]), $ip_address]);
    success('Incantesimo disattivato!');
    redirect('/?page=admin&section=spells');
}

if (isset($_GET['delete_creature'])) {
    $creature_id = (int)$_GET['delete_creature'];
    Database::query("UPDATE ci_creatures SET active = 0 WHERE id = ?", [$creature_id]);
    Database::query("INSERT INTO ci_logs (admin_id, action, details, ip_address) VALUES (?, 'delete_creature', ?, ?)", [$admin_id, json_encode(['creature_id' => $creature_id]), $ip_address]);
    success('Creatura disattivata!');
    redirect('/?page=admin&section=creatures');
}

if (isset($_GET['toggle_user'])) {
    $user_id = (int)$_GET['toggle_user'];
    $user = Database::fetch("SELECT active FROM ci_users WHERE id = ?", [$user_id]);
    if ($user) {
        $new_status = $user['active'] ? 0 : 1;
        Database::query("UPDATE ci_users SET active = ? WHERE id = ?", [$new_status, $user_id]);
        Database::query("INSERT INTO ci_logs (admin_id, action, details, ip_address) VALUES (?, 'toggle_user', ?, ?)", [$admin_id, json_encode(['user_id' => $user_id, 'new_status' => $new_status]), $ip_address]);
        success('Stato utente aggiornato!');
    }
    redirect('/?page=admin&section=users');
}

// ===================================================================
// CARICAMENTO DATI
// ===================================================================

$section = $_GET['section'] ?? 'dashboard';
$data = [];

switch ($section) {
    case 'dashboard':
        $data['stats'] = [
            'spells_active' => Database::fetch("SELECT COUNT(*) as count FROM ci_spells WHERE active = 1")['count'] ?? 0,
            'creatures_active' => Database::fetch("SELECT COUNT(*) as count FROM ci_creatures WHERE active = 1")['count'] ?? 0,
            'books_total' => Database::fetch("SELECT COUNT(*) as count FROM ci_spellbooks")['count'] ?? 0,
            'users_active' => Database::fetch("SELECT COUNT(*) as count FROM ci_users WHERE active = 1")['count'] ?? 0,
        ];
        $data['recent_logs'] = Database::fetchAll("SELECT l.*, a.username FROM ci_logs l JOIN ci_admin a ON a.id = l.admin_id ORDER BY l.created_at DESC LIMIT 10");
        break;
    case 'spells':
        $data['spells'] = Database::fetchAll("SELECT * FROM ci_spells WHERE active = 1 ORDER BY level ASC, name_it ASC");
        break;
    case 'creatures':
        $data['creatures'] = Database::fetchAll("SELECT * FROM ci_creatures WHERE active = 1 ORDER BY name_it ASC");
        break;
    case 'users':
        $data['users'] = Database::fetchAll("SELECT * FROM ci_users ORDER BY created_at DESC");
        break;
}
?>

<div class="container-fluid mt-4 flex-grow-1">
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-shield-fill-check text-danger"></i> Admin Panel</h2>
            <p class="text-muted">Gestione completa del sistema</p>
        </div>
    </div>
    
    <ul class="nav nav-pills mb-4">
        <li class="nav-item"><a class="nav-link <?= $section === 'dashboard' ? 'active' : '' ?>" href="?page=admin&section=dashboard">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link <?= $section === 'spells' ? 'active' : '' ?>" href="?page=admin&section=spells">Incantesimi</a></li>
        <li class="nav-item"><a class="nav-link <?= $section === 'creatures' ? 'active' : '' ?>" href="?page=admin&section=creatures">Creature</a></li>
        <li class="nav-item"><a class="nav-link <?= $section === 'users' ? 'active' : '' ?>" href="?page=admin&section=users">Utenti</a></li>
        <li class="nav-item"><a class="nav-link <?= $section === 'import' ? 'active' : '' ?>" href="?page=admin&section=import">Import CSV</a></li>
    </ul>

    <?php
    $section_file = __DIR__ . "/admin_sections/_{$section}.php";
    if (file_exists($section_file)) {
        include $section_file;
    } else {
        echo '<div class="alert alert-danger">Sezione non trovata.</div>';
    }
    ?>
</div>

<!-- Modal Form Incantesimo -->
<?php 
$spell = null;
include __DIR__ . '/templates/spell_form_modal.php'; 
?>

<!-- Modal Form Creatura -->
<?php 
$creature = null;
include __DIR__ . '/templates/creature_form_modal.php'; 
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // MODAL INCANTESIMO
    const spellModal = document.getElementById('spellFormModal');
    if (spellModal) {
        spellModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const form = spellModal.querySelector('form');
            const title = spellModal.querySelector('.modal-title');
            const submitBtn = spellModal.querySelector('button[type="submit"]');

            const spellId = button.getAttribute('data-id');
            if (spellId) {
                // MODIFICA
                title.innerHTML = '<i class="bi bi-pencil-fill"></i> Modifica Incantesimo';
                submitBtn.innerHTML = '<i class="bi bi-save-fill"></i> Salva Modifiche';
                
                form.querySelector('#spell_id').value = spellId;
                form.querySelector('#name_it').value = button.getAttribute('data-name_it') || '';
                form.querySelector('#name_en').value = button.getAttribute('data-name_en') || '';
                form.querySelector('#level').value = button.getAttribute('data-level') || '0';
                form.querySelector('#school').value = button.getAttribute('data-school') || '';
                form.querySelector('#classes').value = button.getAttribute('data-classes') || '';
                form.querySelector('#casting_time').value = button.getAttribute('data-casting_time') || '';
                form.querySelector('#range_distance').value = button.getAttribute('data-range_distance') || '';
                form.querySelector('#components').value = button.getAttribute('data-components') || '';
                form.querySelector('#duration').value = button.getAttribute('data-duration') || '';
                form.querySelector('#description_it').value = button.getAttribute('data-description_it') || '';
                form.querySelector('#higher_levels').value = button.getAttribute('data-higher_levels') || '';
                form.querySelector('#ritual').checked = button.getAttribute('data-ritual') == '1';
                form.querySelector('#concentration').checked = button.getAttribute('data-concentration') == '1';
            } else {
                // NUOVO
                title.innerHTML = '<i class="bi bi-plus-circle-fill"></i> Nuovo Incantesimo';
                submitBtn.innerHTML = '<i class="bi bi-plus-circle-fill"></i> Crea Incantesimo';
                form.reset();
                form.querySelector('#spell_id').value = '';
            }
        });
    }

    // MODAL CREATURA
    const creatureModal = document.getElementById('creatureFormModal');
    if(creatureModal) {
        creatureModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const form = creatureModal.querySelector('form');
            const title = creatureModal.querySelector('.modal-title');
            const submitBtn = creatureModal.querySelector('button[type="submit"]');

            const creatureId = button.getAttribute('data-id');
            if (creatureId) {
                // MODIFICA
                title.innerHTML = '<i class="bi bi-pencil-fill"></i> Modifica Creatura';
                submitBtn.innerHTML = '<i class="bi bi-save-fill"></i> Salva Modifiche';

                form.querySelector('#creature_id').value = creatureId;
                form.querySelector('#name_it').value = button.getAttribute('data-name-it') || '';
                form.querySelector('#name_en').value = button.getAttribute('data-name-en') || '';
                form.querySelector('#creature_type').value = button.getAttribute('data-creature-type') || '';
                form.querySelector('#size').value = button.getAttribute('data-size') || '';
                form.querySelector('#armor_class').value = button.getAttribute('data-armor-class') || '';
                form.querySelector('#hit_points').value = button.getAttribute('data-hit-points') || '';
                form.querySelector('#speed_ground').value = button.getAttribute('data-speed-ground') || '30';
                form.querySelector('#speed_fly').value = button.getAttribute('data-speed-fly') || '0';
                form.querySelector('#speed_swim').value = button.getAttribute('data-speed-swim') || '0';
                form.querySelector('#str').value = button.getAttribute('data-str') || '10';
                form.querySelector('#dex').value = button.getAttribute('data-dex') || '10';
                form.querySelector('#con').value = button.getAttribute('data-con') || '10';
                form.querySelector('#int').value = button.getAttribute('data-int') || '10';
                form.querySelector('#wis').value = button.getAttribute('data-wis') || '10';
                form.querySelector('#cha').value = button.getAttribute('data-cha') || '10';
                form.querySelector('#skills').value = button.getAttribute('data-skills') || '';
                form.querySelector('#senses').value = button.getAttribute('data-senses') || '';
                form.querySelector('#languages').value = button.getAttribute('data-languages') || '';
                form.querySelector('#challenge_rating').value = button.getAttribute('data-challenge-rating') || '0';
                form.querySelector('#special_abilities').value = button.getAttribute('data-special-abilities') || '';
                form.querySelector('#actions').value = button.getAttribute('data-actions') || '';
                form.querySelector('#description_it').value = button.getAttribute('data-description-it') || '';
            } else {
                // NUOVO
                title.innerHTML = '<i class="bi bi-plus-circle-fill"></i> Nuova Creatura';
                submitBtn.innerHTML = '<i class="bi bi-plus-circle-fill"></i> Crea Creatura';
                form.reset();
                form.querySelector('#creature_id').value = '';
            }
        });
    }
});
</script>
