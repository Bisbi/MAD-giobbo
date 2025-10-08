<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="bi bi-upload"></i> Import Incantesimi CSV</h5></div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="import_spells">
                    <div class="mb-3"><label for="csv_spells" class="form-label">Seleziona il file .csv</label><input class="form-control" type="file" id="csv_spells" name="csv_file" accept=".csv" required></div>
                    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="skip_header" id="skip_header_spells" checked><label class="form-check-label" for="skip_header_spells">Salta la prima riga</label></div>
                    <button type="submit" class="btn btn-primary">Importa Incantesimi</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-success text-white"><h5 class="mb-0"><i class="bi bi-upload"></i> Import Creature CSV</h5></div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="import_creatures">
                    <div class="mb-3"><label for="csv_creatures" class="form-label">Seleziona il file .csv</label><input class="form-control" type="file" id="csv_creatures" name="csv_file" accept=".csv" required></div>
                    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="skip_header" id="skip_header_creatures" checked><label class="form-check-label" for="skip_header_creatures">Salta la prima riga</label></div>
                    <button type="submit" class="btn btn-success">Importa Creature</button>
                </form>
            </div>
        </div>
    </div>
</div>