</div>
    <!-- End Main Content Wrapper -->

    <!-- Footer -->
    <footer class="bg-dark text-light py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="mb-3">
                        <i class="fas fa-brain"></i> Career Analysis System
                    </h5>
                    <p class="text-muted">
                        Industry-standard psychometric assessments for career guidance. 
                        Helping students discover their perfect career path through validated science.
                    </p>
                    <div class="mt-3">
                        <span class="badge bg-primary me-2">APA Compliant</span>
                        <span class="badge bg-success me-2">BPS Certified</span>
                        <span class="badge bg-info">EFPA Standard</span>
                    </div>
                </div>

                <div class="col-md-2 mb-4">
                    <h6 class="mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?= base_url('/') ?>" class="text-muted text-decoration-none"><i class="fas fa-angle-right"></i> Home</a></li>
                        <li><a href="<?= base_url('about') ?>" class="text-muted text-decoration-none"><i class="fas fa-angle-right"></i> About Us</a></li>
                        <li><a href="<?= base_url('careers') ?>" class="text-muted text-decoration-none"><i class="fas fa-angle-right"></i> Browse Careers</a></li>
                        <li><a href="<?= base_url('faq') ?>" class="text-muted text-decoration-none"><i class="fas fa-angle-right"></i> FAQ</a></li>
                    </ul>
                </div>

                <div class="col-md-2 mb-4">
                    <h6 class="mb-3">Assessments</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?= base_url('assessments/riasec') ?>" class="text-muted text-decoration-none"><i class="fas fa-angle-right"></i> RIASEC Test</a></li>
                        <li><a href="<?= base_url('assessments/vark') ?>" class="text-muted text-decoration-none"><i class="fas fa-angle-right"></i> VARK Test</a></li>
                        <li><a href="<?= base_url('assessments/mbti') ?>" class="text-muted text-decoration-none"><i class="fas fa-angle-right"></i> MBTI Test</a></li>
                        <li><a href="<?= base_url('assessments/gardner') ?>" class="text-muted text-decoration-none"><i class="fas fa-angle-right"></i> Gardner Test</a></li>
                    </ul>
                </div>

                <div class="col-md-2 mb-4">
                    <h6 class="mb-3">Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?= base_url('help') ?>" class="text-muted text-decoration-none"><i class="fas fa-angle-right"></i> Help Center</a></li>
                        <li><a href="<?= base_url('contact') ?>" class="text-muted text-decoration-none"><i class="fas fa-angle-right"></i> Contact Us</a></li>
                        <li><a href="<?= base_url('privacy') ?>" class="text-muted text-decoration-none"><i class="fas fa-angle-right"></i> Privacy Policy</a></li>
                        <li><a href="<?= base_url('terms') ?>" class="text-muted text-decoration-none"><i class="fas fa-angle-right"></i> Terms of Service</a></li>
                    </ul>
                </div>

                <div class="col-md-2 mb-4">
                    <h6 class="mb-3">Connect</h6>
                    <div class="social-links">
                        <a href="#" class="btn btn-outline-light btn-sm me-2 mb-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="btn btn-outline-light btn-sm me-2 mb-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="btn btn-outline-light btn-sm me-2 mb-2"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="btn btn-outline-light btn-sm mb-2"><i class="fab fa-instagram"></i></a>
                    </div>
                    <div class="mt-3">
                        <p class="text-muted small mb-1"><i class="fas fa-envelope"></i> support@careeranalysis.com</p>
                        <p class="text-muted small mb-0"><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                    </div>
                </div>
            </div>

            <hr class="border-secondary my-4">

            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="text-muted small mb-0">
                        &copy; <?= date('Y') ?> Career Analysis System. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="text-muted small mb-0">
                        Built with <i class="fas fa-heart text-danger"></i> using CodeIgniter 4 & Bootstrap 5
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- jQuery 3.7 -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Global JavaScript -->
    <script>
        // Show loading spinner
        function showLoading() {
            document.getElementById('loadingSpinner').style.display = 'flex';
        }

        // Hide loading spinner
        function hideLoading() {
            document.getElementById('loadingSpinner').style.display = 'none';
        }

        // Auto-hide alerts after 5 seconds
        $(document).ready(function() {
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });

        // Confirm before leaving page with unsaved changes
        var formChanged = false;
        
        $('input, textarea, select').on('change', function() {
            formChanged = true;
        });

        $('form').on('submit', function() {
            formChanged = false;
        });

        window.addEventListener('beforeunload', function (e) {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // AJAX setup for CSRF token
        $.ajaxSetup({
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            }
        });

        // Global error handler
        window.addEventListener('error', function(e) {
            console.error('Global error:', e.error);
        });

        // Show loading on AJAX requests
        $(document).ajaxStart(function() {
            showLoading();
        }).ajaxStop(function() {
            hideLoading();
        });
    </script>

    <!-- Additional JavaScript -->
    <?php if (isset($additional_js)): ?>
        <?= $additional_js ?>
    <?php endif; ?>

</body>
</html>