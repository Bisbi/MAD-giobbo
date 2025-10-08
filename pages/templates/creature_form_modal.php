<div class="modal fade" id="creatureFormModal" tabindex="-1" aria-labelledby="creatureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="save_creature">
                <input type="hidden" id="creature_id" name="creature_id" value="">

                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="creatureModalLabel">
                        <i class="bi bi-plus-circle-fill"></i> Nuova Creatura
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Nomi -->
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
                        
                        <!-- Tipo e Taglia -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="creature_type" name="creature_type">
                                <label>Tipo Creatura</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select class="form-select" id="size" name="size">
                                    <option value="Minuscola">Minuscola</option>
                                    <option value="Piccola">Piccola</option>
                                    <option value="Media" selected>Media</option>
                                    <option value="Grande">Grande</option>
                                    <option value="Enorme">Enorme</option>
                                    <option value="Mastodontica">Mastodontica</option>
                                </select>
                                <label>Taglia</label>
                            </div>
                        </div>
                        
                        <!-- CA e PF -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="armor_class" name="armor_class">
                                <label>Classe Armatura (CA)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="hit_points" name="hit_points">
                                <label>Punti Ferita (PF)</label>
                            </div>
                        </div>
                        
                        <!-- Velocità -->
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="speed_ground" name="speed_ground" value="30">
                                <label>Velocità Terra (m)</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="speed_fly" name="speed_fly" value="0">
                                <label>Velocità Volo (m)</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="speed_swim" name="speed_swim" value="0">
                                <label>Velocità Nuoto (m)</label>
                            </div>
                        </div>

                        <!-- Caratteristiche -->
                        <div class="col-12"><hr><h6>Caratteristiche</h6></div>
                        <div class="col-md-2">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="str" name="str" value="10" min="1" max="30">
                                <label>FOR</label>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="dex" name="dex" value="10" min="1" max="30">
                                <label>DES</label>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="con" name="con" value="10" min="1" max="30">
                                <label>COS</label>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="int" name="int" value="10" min="1" max="30">
                                <label>INT</label>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="wis" name="wis" value="10" min="1" max="30">
                                <label>SAG</label>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="cha" name="cha" value="10" min="1" max="30">
                                <label>CAR</label>
                            </div>
                        </div>

                        <!-- Info Aggiuntive -->
                        <div class="col-12"><hr></div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="senses" name="senses">
                                <label>Sensi</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="languages" name="languages">
                                <label>Linguaggi</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="challenge_rating" name="challenge_rating" value="0">
                                <label>Grado Sfida (GS)</label>
                            </div>
                        </div>
                        
                        <!-- Campi Testuali -->
                        <div class="col-12">
                            <label class="form-label">Abilità (Skills)</label>
                            <textarea class="form-control" id="skills" name="skills" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Abilità Speciali</label>
                            <textarea class="form-control" id="special_abilities" name="special_abilities" rows="4"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Azioni</label>
                            <textarea class="form-control" id="actions" name="actions" rows="4"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrizione</label>
                            <textarea class="form-control" id="description_it" name="description_it" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save-fill"></i> Salva
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
