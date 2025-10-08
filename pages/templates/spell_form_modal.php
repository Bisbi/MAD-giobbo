<div class="modal fade" id="spellFormModal" tabindex="-1" aria-labelledby="spellModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="save_spell">
                <input type="hidden" id="spell_id" name="spell_id" value="">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="spellModalLabel">
                        <i class="bi bi-plus-circle-fill"></i> Nuovo Incantesimo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="name_it" name="name_it" required>
                                <label>Nome (IT) *</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="name_en" name="name_en">
                                <label>Nome (EN)</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="level" name="level" value="0" min="0" max="9">
                                <label>Livello (0-9)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="school" name="school">
                                <label>Scuola</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="classes" name="classes">
                                <label>Classi (es: Mago, Chierico)</label>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="casting_time" name="casting_time">
                                <label>Tempo di Lancio</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="range_distance" name="range_distance">
                                <label>Gittata</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="components" name="components">
                                <label>Componenti (V, S, M)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="duration" name="duration">
                                <label>Durata</label>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Descrizione *</label>
                            <textarea class="form-control" id="description_it" name="description_it" rows="5" required></textarea>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">A Livelli Superiori</label>
                            <textarea class="form-control" id="higher_levels" name="higher_levels" rows="3"></textarea>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="ritual" name="ritual" value="1">
                                <label class="form-check-label" for="ritual">Rituale</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="concentration" name="concentration" value="1">
                                <label class="form-check-label" for="concentration">Concentrazione</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save-fill"></i> Salva
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
