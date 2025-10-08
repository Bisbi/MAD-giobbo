    <script type="module" src="<?= BASE_PATH ?>/assets/js/chatapp-bundle.php"></script>
    <!-- Footer -->
    <footer class="bg-dark text-white py-3 mt-auto">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4 text-center text-md-start">
                    <img src="<?= BASE_PATH ?>/icons/android/android-launchericon-48-48.png" alt="Logo" height="32" class="me-2"> 
                    <span class="footer-copyright"><?= APP_NAME ?> © <?= date('Y') ?></span>
                </div>
				<div class="col-md-4 text-center">
				<script type='text/javascript' src='https://storage.ko-fi.com/cdn/widget/Widget_2.js'></script><script type='text/javascript'>kofiwidget2.init('Supporta Il Progetto', '#72a4f2', 'T6T01MFWQM');kofiwidget2.draw();</script>
				</div>	
                <div class="col-md-4 text-center text-md-end mt-2 mt-md-0">
                    <small class="footer-credits">Creato da Giobbo</small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php 
    // Versioning automatico basato su timestamp ultima modifica
    $js_version = filemtime(__DIR__ . '/../assets/js/app.js') ?: time();
    ?>
    
    <!-- Custom JS - CON VERSIONING -->
    <script src="<?= BASE_PATH ?>/assets/js/app.js?v=<?= $js_version ?>"></script>
    
    <!-- Toast Container - Posizionato alla fine per non interferire con il layout -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>
    
</body>
</html>
