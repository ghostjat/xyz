<?= $this->include('layouts/header') ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card shadow-lg mt-4 mb-5">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-plus fa-4x text-primary mb-3"></i>
                        <h2 class="fw-bold">Create Your Account</h2>
                        <p class="text-muted">Start your career discovery journey today</p>
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

                    <form action="<?= base_url('api/auth/register') ?>" method="post" id="registerForm">
                        <?= csrf_field() ?>
                        
                        <div class="row">
                            <!-- Personal Information -->
                            <div class="col-md-6 mb-3">
                                <label for="full_name" class="form-label">
                                    <i class="fas fa-user"></i> Full Name *
                                </label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="full_name" 
                                    name="full_name" 
                                    placeholder="Enter your full name"
                                    value="<?= old('full_name') ?>"
                                    required
                                >
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">
                                    <i class="fas fa-at"></i> Username *
                                </label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="username" 
                                    name="username" 
                                    placeholder="Choose a username"
                                    value="<?= old('username') ?>"
                                    required
                                >
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i> Email Address *
                                </label>
                                <input 
                                    type="email" 
                                    class="form-control" 
                                    id="email" 
                                    name="email" 
                                    placeholder="your@email.com"
                                    value="<?= old('email') ?>"
                                    required
                                >
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">
                                    <i class="fas fa-phone"></i> Phone Number
                                </label>
                                <input 
                                    type="tel" 
                                    class="form-control" 
                                    id="phone" 
                                    name="phone" 
                                    placeholder="+1 (555) 123-4567"
                                    value="<?= old('phone') ?>"
                                >
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i> Password *
                                </label>
                                <input 
                                    type="password" 
                                    class="form-control" 
                                    id="password" 
                                    name="password" 
                                    placeholder="Min 8 characters"
                                    required
                                >
                                <small class="text-muted">Must be at least 8 characters</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password_confirm" class="form-label">
                                    <i class="fas fa-lock"></i> Confirm Password *
                                </label>
                                <input 
                                    type="password" 
                                    class="form-control" 
                                    id="password_confirm" 
                                    name="password_confirm" 
                                    placeholder="Re-enter password"
                                    required
                                >
                            </div>
                        </div>

                        <!-- Demographics -->
                        <hr class="my-4">
                        <h5 class="mb-3"><i class="fas fa-info-circle"></i> Personal Details</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date_of_birth" class="form-label">
                                    <i class="fas fa-calendar"></i> Date of Birth *
                                </label>
                                <input 
                                    type="date" 
                                    class="form-control" 
                                    id="date_of_birth" 
                                    name="date_of_birth" 
                                    value="<?= old('date_of_birth') ?>"
                                    max="<?= date('Y-m-d', strtotime('-13 years')) ?>"
                                    required
                                >
                                <small class="text-muted">Age 13-25 required</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="gender" class="form-label">
                                    <i class="fas fa-venus-mars"></i> Gender *
                                </label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="">Select gender</option>
                                    <option value="male" <?= old('gender') == 'male' ? 'selected' : '' ?>>Male</option>
                                    <option value="female" <?= old('gender') == 'female' ? 'selected' : '' ?>>Female</option>
                                    <option value="other" <?= old('gender') == 'other' ? 'selected' : '' ?>>Other</option>
                                    <option value="prefer_not_to_say" <?= old('gender') == 'prefer_not_to_say' ? 'selected' : '' ?>>Prefer not to say</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="educational_level" class="form-label">
                                <i class="fas fa-graduation-cap"></i> Current Educational Level *
                            </label>
                            <select class="form-select" id="educational_level" name="educational_level" required>
                                <option value="">Select your class/level</option>
                                <option value="class_8" <?= old('educational_level') == 'class_8' ? 'selected' : '' ?>>Class 8</option>
                                <option value="class_9" <?= old('educational_level') == 'class_9' ? 'selected' : '' ?>>Class 9</option>
                                <option value="class_10" <?= old('educational_level') == 'class_10' ? 'selected' : '' ?>>Class 10</option>
                                <option value="class_11" <?= old('educational_level') == 'class_11' ? 'selected' : '' ?>>Class 11</option>
                                <option value="class_12" <?= old('educational_level') == 'class_12' ? 'selected' : '' ?>>Class 12</option>
                                <option value="graduate" <?= old('educational_level') == 'graduate' ? 'selected' : '' ?>>Graduate</option>
                                <option value="postgraduate" <?= old('educational_level') == 'postgraduate' ? 'selected' : '' ?>>Postgraduate</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="school_name" class="form-label">
                                <i class="fas fa-school"></i> School/College Name
                            </label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="school_name" 
                                name="school_name" 
                                placeholder="Your school or college"
                                value="<?= old('school_name') ?>"
                            >
                        </div>

                        <!-- Location -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="country" class="form-label">
                                    <i class="fas fa-globe"></i> Country *
                                </label>
                                <select class="form-select" id="country" name="country" required>
                                    <option value="">Select country</option>
                                    <option value="United States" <?= old('country') == 'United States' ? 'selected' : '' ?>>United States</option>
                                    <option value="United Kingdom" <?= old('country') == 'United Kingdom' ? 'selected' : '' ?>>United Kingdom</option>
                                    <option value="India" <?= old('country') == 'India' ? 'selected' : '' ?>>India</option>
                                    <option value="Canada" <?= old('country') == 'Canada' ? 'selected' : '' ?>>Canada</option>
                                    <option value="Australia" <?= old('country') == 'Australia' ? 'selected' : '' ?>>Australia</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="state" class="form-label">
                                    <i class="fas fa-map-marker-alt"></i> State/Province
                                </label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="state" 
                                    name="state" 
                                    placeholder="Your state"
                                    value="<?= old('state') ?>"
                                >
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label">
                                    <i class="fas fa-city"></i> City
                                </label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="city" 
                                    name="city" 
                                    placeholder="Your city"
                                    value="<?= old('city') ?>"
                                >
                            </div>
                        </div>

                        <!-- Terms and Conditions -->
                        <hr class="my-4">

                        <div class="mb-3 form-check">
                            <input 
                                type="checkbox" 
                                class="form-check-input" 
                                id="terms_accepted" 
                                name="terms_accepted"
                                value="1"
                                required
                            >
                            <label class="form-check-label" for="terms_accepted">
                                I agree to the <a href="<?= base_url('terms') ?>" target="_blank">Terms of Service</a> 
                                and <a href="<?= base_url('privacy') ?>" target="_blank">Privacy Policy</a> *
                            </label>
                        </div>

                        <div class="mb-3 form-check">
                            <input 
                                type="checkbox" 
                                class="form-check-input" 
                                id="marketing_emails" 
                                name="marketing_emails"
                                value="1"
                            >
                            <label class="form-check-label" for="marketing_emails">
                                Send me updates about careers and assessments
                            </label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus"></i> Create Account
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="text-muted mb-0">
                            Already have an account? 
                            <a href="<?= base_url('login') ?>" class="fw-bold text-decoration-none">
                                Sign In
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Password strength indicator
    $('#password').on('input', function() {
        const password = $(this).val();
        const strength = checkPasswordStrength(password);
        
        // Show strength indicator (can be enhanced)
        if (password.length > 0) {
            let color = 'danger';
            let text = 'Weak';
            
            if (strength >= 3) {
                color = 'success';
                text = 'Strong';
            } else if (strength >= 2) {
                color = 'warning';
                text = 'Medium';
            }
            
            const indicator = `<small class="text-${color}">Password strength: ${text}</small>`;
            $('#password').next('.text-muted').html(indicator);
        }
    });

    function checkPasswordStrength(password) {
        let strength = 0;
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]+/)) strength++;
        if (password.match(/[A-Z]+/)) strength++;
        if (password.match(/[0-9]+/)) strength++;
        if (password.match(/[$@#&!]+/)) strength++;
        return strength;
    }

    // Confirm password validation
    $('#password_confirm').on('input', function() {
        const password = $('#password').val();
        const confirm = $(this).val();
        
        if (confirm.length > 0) {
            if (password === confirm) {
                $(this).removeClass('is-invalid').addClass('is-valid');
            } else {
                $(this).removeClass('is-valid').addClass('is-invalid');
            }
        }
    });

    // Age validation
    $('#date_of_birth').on('change', function() {
        const dob = new Date($(this).val());
        const today = new Date();
        const age = Math.floor((today - dob) / (365.25 * 24 * 60 * 60 * 1000));
        
        if (age < 13 || age > 25) {
            alert('You must be between 13 and 25 years old to register.');
            $(this).val('');
        }
    });

    // Form submission with AJAX
    $('#registerForm').submit(function(e) {
        e.preventDefault();
        
        // Validate passwords match
        if ($('#password').val() !== $('#password_confirm').val()) {
            alert('Passwords do not match!');
            return false;
        }
        
        showLoading();
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                hideLoading();
                if (response.success) {
                    const alert = `
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> ${response.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    $('#registerForm').before(alert);
                    
                    // Redirect to login
                    setTimeout(function() {
                        window.location.href = '<?= base_url('login') ?>';
                    }, 2000);
                } else {
                    const alert = `
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle"></i> ${response.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    $('#registerForm').before(alert);
                }
            },
            error: function(xhr) {
                hideLoading();
                const response = xhr.responseJSON;
                
                if (response && response.errors) {
                    let errorHtml = '<div class="alert alert-danger"><ul class="mb-0">';
                    for (let field in response.errors) {
                        errorHtml += `<li>${response.errors[field]}</li>`;
                    }
                    errorHtml += '</ul></div>';
                    $('#registerForm').before(errorHtml);
                } else {
                    const message = response?.message || 'Registration failed. Please try again.';
                    const alert = `
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle"></i> ${message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    $('#registerForm').before(alert);
                }
            }
        });
    });
});
</script>

<?= $this->include('layouts/footer') ?>