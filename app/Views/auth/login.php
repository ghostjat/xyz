<?= $this->include('layouts/header') ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg mt-5">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-circle fa-4x text-primary mb-3"></i>
                        <h2 class="fw-bold">Welcome Back</h2>
                        <p class="text-muted">Sign in to continue your career journey</p>
                    </div>

                    <?php if (isset($validation)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($validation->getErrors() as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach ?>
                            </ul>
                        </div>
                    <?php endif ?>

                    <form action="<?= base_url('api/auth/login') ?>" method="post" id="loginForm">
                        <?= csrf_field() ?>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i> Email Address
                            </label>
                            <input 
                                type="email" 
                                class="form-control form-control-lg" 
                                id="email" 
                                name="email" 
                                placeholder="Enter your email"
                                value="<?= old('email') ?>"
                                required
                                autofocus
                            >
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i> Password
                            </label>
                            <div class="input-group">
                                <input 
                                    type="password" 
                                    class="form-control form-control-lg" 
                                    id="password" 
                                    name="password" 
                                    placeholder="Enter your password"
                                    required
                                >
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input 
                                type="checkbox" 
                                class="form-check-input" 
                                id="remember_me" 
                                name="remember_me"
                                value="1"
                            >
                            <label class="form-check-label" for="remember_me">
                                Remember me for 30 days
                            </label>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Sign In
                            </button>
                        </div>

                        <div class="text-center">
                            <a href="<?= base_url('forgot-password') ?>" class="text-decoration-none">
                                <i class="fas fa-question-circle"></i> Forgot your password?
                            </a>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="text-muted mb-0">
                            Don't have an account? 
                            <a href="<?= base_url('register') ?>" class="fw-bold text-decoration-none">
                                Sign Up Now
                            </a>
                        </p>
                    </div>

                    <!-- Social Login (Optional) -->
                    <!--
                    <div class="text-center mt-4">
                        <p class="text-muted small">Or sign in with</p>
                        <button class="btn btn-outline-primary me-2">
                            <i class="fab fa-google"></i> Google
                        </button>
                        <button class="btn btn-outline-dark">
                            <i class="fab fa-microsoft"></i> Microsoft
                        </button>
                    </div>
                    -->
                </div>
            </div>

            <!-- Info Card -->
            <div class="card mt-4 bg-light">
                <div class="card-body text-center">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-shield-alt text-success"></i> Your Data is Safe
                    </h6>
                    <p class="text-muted small mb-0">
                        We use industry-standard encryption to protect your information. 
                        Your assessment results are confidential and secure.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Toggle password visibility
    $('#togglePassword').click(function() {
        const passwordField = $('#password');
        const icon = $(this).find('i');
        
        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Form submission with AJAX
    $('#loginForm').submit(function(e) {
        e.preventDefault();
        
        showLoading();
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                hideLoading();
                if (response.success) {
                    // Show success message
                    const alert = `
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> ${response.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    $('#loginForm').before(alert);
                    
                    // Redirect to dashboard
                    setTimeout(function() {
                        window.location.href = '<?= base_url('dashboard') ?>';
                    }, 1000);
                } else {
                    // Show error message
                    const alert = `
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle"></i> ${response.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    $('#loginForm').before(alert);
                }
            },
            error: function(xhr) {
                hideLoading();
                const response = xhr.responseJSON;
                const message = response?.message || 'An error occurred. Please try again.';
                
                const alert = `
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle"></i> ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                $('#loginForm').before(alert);
            }
        });
    });

    // Enter key to submit
    $('#email, #password').keypress(function(e) {
        if (e.which == 13) {
            $('#loginForm').submit();
        }
    });
});
</script>

<?= $this->include('layouts/footer') ?>