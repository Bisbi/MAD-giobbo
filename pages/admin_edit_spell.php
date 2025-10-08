<?php
/**
 * Admin - Modifica Incantesimo
 */
$page_title = 'Modifica Incantesimo';
requireAdmin();

// Ottieni ID e carica l'incantesimo
$spell_id = (int)($_GET['id'] ?? 0);
if (!$spell_id) {
    error('ID Incantesimo non valido.');
    redirect('/?page=admin&section=spells');
}

$spell = Database::fetch("SELECT * FROM ci_spells WHERE id = ?", [$spell_id]);
if (!$spell) {
    error('Incantesimo non trovato.');
    redirect('/?page=admin&section=spells');
}

// Gestione del salvataggio del form
if (isset($_POST['action']) && $_POST['action'] === 'update_spell') {
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

    $query = "UPDATE ci_spells SET 
                name_it = :name_it, name_en = :name_en, level = :level, school = :school,
                classes = :classes, casting_time = :casting_time, range_distance = :range_distance,
                components = :components, duration = :duration, description_it = :description_it,
                higher_levels = :higher_levels, ritual = :ritual, concentration = :concentration
              WHERE id = :id";
    
    $data['id'] = $spell_id;

    Database::query($query, $data);
    
    success('Incantesimo aggiornato con successo!');
    redirect('/?page=admin&section=spells');
}
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-pencil-fill text-warning"></i> Modifica: <?= sanitize($spell['name_it']) ?></h2>
            <p class="text-muted">ID Incantesimo: <?= $spell['id'] ?></p>
        </div>
        <div class="col text-end">
             <a href="<?= BASE_PATH ?>/?page=admin&section=spells" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Torna a Gestione Incantesimi</a>
        </div>
    </div>

    <form method="POST">
        <input type="hidden" name="action" value="update_spell">
        <div class="card">
            <div class="card-body">
                 <div class="row g-3">
                    <div class="col-md-6"><div class="form-floating"><input type="text" class="form-control" id="name_it" name="name_it" value="<?= sanitize($spell['name_it']) ?>" required><label for="name_it">Nome (IT)</label></div></div>
                    <div class="col-md-6"><div class="form-floating"><input type="text" class="form-control" id="name_en" name="name_en" value="<?= sanitize($spell['name_en']) ?>"><label for="name_en">Nome (EN)</label></div></div>

                    <div class="col-md-6"><div class="form-floating"><input type="number" class="form-control" id="level" name="level" value="<?= (int)$spell['level'] ?>"><label for="level">Livello</label></div></div>
                    <div class="col-md-6"><div class="form-floating"><input type="text" class="form-control" id="school" name="school" value="<?= sanitize($spell['school']) ?>"><label for="school">Scuola di Magia</label></div></div>

                    <div class="col-12"><div class="form-floating"><input type="text" class="form-control" id="classes" name="classes" value="<?= sanitize($spell['classes']) ?>"><label for="classes">Classi (separate da virgola)</label></div></div>
                    
                    <div class="col-md-6"><div class="form-floating"><input type="text" class="form-control" id="casting_time" name="casting_time" value="<?= sanitize($spell['casting_time']) ?>"><label for="casting_time">Tempo di Lancio</label></div></div>
                    <div class="col-md-6"><div class="form-floating"><input type="text" class="form-control" id="range_distance" name="range_distance" value="<?= sanitize($spell['range_distance']) ?>"><label for="range_distance">Gittata</label></div></div>

                    <div class="col-md-6"><div class="form-floating"><input type="text" class="form-control" id="components" name="components" value="<?= sanitize($spell['components']) ?>"><label for="components">Componenti</label></div></div>
                    <div class="col-md-6"><div class="form-floating"><input type="text" class="form-control" id="duration" name="duration" value="<?= sanitize($spell['duration']) ?>"><label for="duration">Durata</label></div></div>
                    
                    <div class="col-12"><label for="description_it" class="form-label">Descrizione</label><textarea class="form-control" name="description_it" id="description_it" rows="6"><?= sanitize($spell['description_it']) ?></textarea></div>
                    <div class="col-12"><label for="higher_levels" class="form-label">A Livelli Superiori</label><textarea class="form-control" name="higher_levels" id="higher_levels" rows="3"><?= sanitize($spell['higher_levels']) ?></textarea></div>

                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="ritual" name="ritual" value="1" <?= $spell['ritual'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="ritual">Rituale</label>
                        </div>
                    </div>
                     <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="concentration" name="concentration" value="1" <?= $spell['concentration'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="concentration">Concentrazione</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill"></i> Salva Modifiche</button>
            </div>
        </div>
    </form>
</div>