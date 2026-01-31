    </main>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?= SITE_NAME ?></h5>
                    <p class="mb-0">Organizing world-class academic conferences.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">
                        <i class="bi bi-envelope me-2"></i>
                        <a href="mailto:<?= ADMIN_EMAIL ?>" class="text-light"><?= ADMIN_EMAIL ?></a>
                    </p>
                    <p class="mb-0 mt-2">
                        &copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script src="<?= SITE_URL ?>/public/js/main.js"></script>
</body>
</html>
