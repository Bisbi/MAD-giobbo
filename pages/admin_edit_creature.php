<?php
/**
 * Admin - Modifica Creatura
 */
$page_title = 'Modifica Creatura';
requireAdmin();

// Ottieni ID e carica la creatura
$creature_id = (int)($_GET['id'] ?? 0);
if (!$creature_id) {
    error('ID Creatura non valido.');
    redirect('/?page=admin&section=creatures');
}

$creature = Database::fetch("SELECT * FROM ci_creatures WHERE id = ?", [$creature_id]);
if (!$creature) {
    error('Creatura non trovata.');
    redirect('/?page=admin&section=creatures');
}

// Gestione del salvataggio del form
if (isset($_POST['action']) && $_POST['action'] === 'update_creature') {
    // Recupera tutti i dati dal form
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

    // Costruisci la query di UPDATE
    $query = "UPDATE ci_creatures SET 
                name_it = :name_it, name_en = :name_en, creature_type = :creature_type, 
                size = :size, armor_class = :armor_class, hit_points = :hit_points, 
                speed_ground = :speed_ground, speed_fly = :speed_fly, speed_swim = :speed_swim, 
                str = :str, dex = :dex, con = :con, `int` = :int, wis = :wis, cha = :cha, 
                skills = :skills, senses = :senses, languages = :languages, 
                challenge_rating = :challenge_rating, special_abilities = :special_abilities, 
                actions = :actions, description_it = :description_it 
              WHERE id = :id";
    
    $data['id'] = $creature_id;

    Database::query($query, $data);
    
    success('Creatura aggiornata con successo!');
    redirect('/?page=admin&section=creatures');
}
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-pencil-fill text-warning"></i> Modifica: <?= sanitize($creature['name_it']) ?></h2>
            <p class="text-muted">ID Creatura: <?= $creature['id'] ?></p>
        </div>
        <div class="col text-end">
            <a href="<?= BASE_PATH ?>/?page=admin&section=creatures" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Torna a Gestione Creature</a>
        </div>
    </div>

    <form method="POST">
        <input type="hidden" name="action" value="update_creature">
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><div class="form-floating"><input type="text" class="form-control" id="name_it" name="name_it" value="<?= sanitize($creature['name_it']) ?>" required><label for="name_it">Nome (IT)</label></div></div>
                    <div class="col-md-6"><div class="form-floating"><input type="text" class="form-control" id="name_en" name="name_en" value="<?= sanitize($creature['name_en']) ?>"><label for="name_en">Nome (EN)</label></div></div>
                    
                    <div class="col-md-6"><div class="form-floating"><input type="text" class="form-control" id="creature_type" name="creature_type" value="<?= sanitize($creature['creature_type']) ?>"><label for="creature_type">Tipo Creatura</label></div></div>
                    <div class="col-md-6"><div class="form-floating"><input type="text" class="form-control" id="size" name="size" value="<?= sanitize($creature['size']) ?>"><label for="size">Taglia</label></div></div>
                    
                    <div class="col-md-6"><div class="form-floating"><input type="text" class="form-control" id="armor_class" name="armor_class" value="<?= sanitize($creature['armor_class']) ?>"><label for="armor_class">Classe Armatura</label></div></div>
                    <div class="col-md-6"><div class="form-floating"><input type="text" class="form-control" id="hit_points" name="hit_points" value="<?= sanitize($creature['hit_points']) ?>"><label for="hit_points">Punti Ferita</label></div></div>
                    
                    <div class="col-md-4"><div class="form-floating"><input type="number" class="form-control" id="speed_ground" name="speed_ground" value="<?= (int)$creature['speed_ground'] ?>"><label for="speed_ground">Velocità Terra</label></div></div>
                    <div class="col-md-4"><div class="form-floating"><input type="number" class="form-control" id="speed_fly" name="speed_fly" value="<?= (int)$creature['speed_fly'] ?>"><label for="speed_fly">Velocità Volo</label></div></div>
                    <div class="col-md-4"><div class="form-floating"><input type="number" class="form-control" id="speed_swim" name="speed_swim" value="<?= (int)$creature['speed_swim'] ?>"><label for="speed_swim">Velocità Nuoto</label></div></div>
                    
                    <div class="col-md-2"><div class="form-floating"><input type="number" class="form-control" id="str" name="str" value="<?= (int)$creature['str'] ?>"><label for="str">FOR</label></div></div>
                    <div class="col-md-2"><div class="form-floating"><input type="number" class="form-control" id="dex" name="dex" value="<?= (int)$creature['dex'] ?>"><label for="dex">DES</label></div></div>
                    <div class="col-md-2"><div class="form-floating"><input type="number" class="form-control" id="con" name="con" value="<?= (int)$creature['con'] ?>"><label for="con">COS</label></div></div>
                    <div class="col-md-2"><div class="form-floating"><input type="number" class="form-control" id="int" name="int" value="<?= (int)$creature['int'] ?>"><label for="int">INT</label></div></div>
                    <div class="col-md-2"><div class="form-floating"><input type="number" class="form-control" id="wis" name="wis" value="<?= (int)$creature['wis'] ?>"><label for="wis">SAG</label></div></div>
                    <div class="col-md-2"><div class="form-floating"><input type="number" class="form-control" id="cha" name="cha" value="<?= (int)$creature['cha'] ?>"><label for="cha">CAR</label></div></div>

                    <div class="col-12"><label for="skills" class="form-label">Abilità (Skills)</label><textarea class="form-control" id="skills" name="skills" rows="2"><?= sanitize($creature['skills']) ?></textarea></div>
                    <div class="col-12"><label for="senses" class="form-label">Sensi</label><input type="text" class="form-control" id="senses" name="senses" value="<?= sanitize($creature['senses']) ?>"></div>
                    <div class="col-md-6"><label for="languages" class="form-label">Linguaggi</label><input type="text" class="form-control" id="languages" name="languages" value="<?= sanitize($creature['languages']) ?>"></div>
                    <div class="col-md-6"><label for="challenge_rating" class="form-label">Grado Sfida (GS)</label><input type="text" class="form-control" id="challenge_rating" name="challenge_rating" value="<?= sanitize($creature['challenge_rating']) ?>"></div>
                    
                    <div class="col-12"><label for="special_abilities" class="form-label">Abilità Speciali</label><textarea class="form-control" id="special_abilities" name="special_abilities" rows="5"><?= sanitize($creature['special_abilities']) ?></textarea></div>
                    <div class="col-12"><label for="actions" class="form-label">Azioni</label><textarea class="form-control" id="actions" name="actions" rows="5"><?= sanitize($creature['actions']) ?></textarea></div>
                    <div class="col-12"><label for="description_it" class="form-label">Descrizione</label><textarea class="form-control" id="description_it" name="description_it" rows="3"><?= sanitize($creature['description_it']) ?></textarea></div>
                </div>
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill"></i> Salva Modifiche</button>
            </div>
        </div>
    </form>
</div>