<section id="contact" style="background-color: #F8F9FA; padding: 100px 0; font-family: 'Inter', sans-serif;">
    <div class="container">
        <div class="row g-5">

            <div class="col-lg-5">
                
                <div class="text-uppercase fw-bold mb-2" style="color: #0F3460; letter-spacing: 1px; font-size: 0.85rem;">
                    Get in Touch
                </div>

                <h2 class="fw-bold mb-4" style="font-family: 'Merriweather', serif; color: #111; font-size: 2.5rem;">
                    Start Your Growth <br> Journey With Us.
                </h2>

                <p class="mb-5 text-muted" style="font-size: 1.1rem; line-height: 1.6;">
                    Our counsellors are ready to help you map out your future. 
                    Reach out directly or schedule a call.
                </p>

                <div class="d-flex align-items-center mb-4">
                    <div class="d-flex align-items-center justify-content-center rounded-circle shadow-sm" 
                         style="width: 50px; height: 50px; background: #fff; color: #0F3460;">
                        <i class="bi bi-geo-alt-fill fs-5"></i>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-0 fw-bold text-dark">Headquarters</h6>
                        <small class="text-muted">New Delhi, India</small>
                    </div>
                </div>

                <div class="d-flex align-items-center mb-4">
                    <div class="d-flex align-items-center justify-content-center rounded-circle shadow-sm" 
                         style="width: 50px; height: 50px; background: #fff; color: #0F3460;">
                        <i class="bi bi-envelope-fill fs-5"></i>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-0 fw-bold text-dark">Email Support</h6>
                        <a href="mailto:info@pharoseducation.in" class="text-decoration-none text-muted">
                            info@pharoseducation.in <br><!-- comment -->
                            support@pharoseducation.in
                        </a>
                    </div>
                </div>

                <div class="d-flex align-items-center">
                    <div class="d-flex align-items-center justify-content-center rounded-circle shadow-sm" 
                         style="width: 50px; height: 50px; background: #fff; color: #0F3460;">
                        <i class="bi bi-telephone-fill fs-5"></i>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-0 fw-bold text-dark">Call Us</h6>
                        <small class="text-muted">+91 xxxxx xxxxx</small>
                    </div>
                </div>

            </div>

            <div class="col-lg-7">
                <div class="bg-white p-5 rounded-3 shadow-sm border">
                    
                    <h3 class="mb-4 fw-bold" style="font-family: 'Merriweather', serif; color: #0F3460;">
                        Book a Consultation
                    </h3>

                    <form id="contactForm">
                        <div class="row g-3">
                            
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">FULL NAME</label>
                                <input type="text" name="name" class="form-control p-3 bg-light border-0" placeholder="John Doe">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">EMAIL ADDRESS</label>
                                <input type="email" name="email" class="form-control p-3 bg-light border-0" placeholder="name@example.com">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">PHONE</label>
                                <input type="text" name="phone" class="form-control p-3 bg-light border-0" placeholder="+91...">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">I AM A...</label>
                                <select name="role" class="form-select p-3 bg-light border-0">
                                    <option value="">Select Role</option>
                                    <option value="Student">Student</option>
                                    <option value="Parent">Parent</option>
                                    <option value="School Admin">School Admin</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">MESSAGE</label>
                                <textarea name="message" rows="4" class="form-control p-3 bg-light border-0" placeholder="How can we help you?"></textarea>
                            </div>

                            <div class="col-12 mt-3">
                                <button type="submit" class="btn w-100 py-3 fw-bold rounded-2 text-white shadow-sm" 
                                        style="background-color: #0F3460; transition: all 0.3s;">
                                    Submit Request
                                </button>
                            </div>

                        </div>
                    </form>
                    
                    <div id="successMsg" class="mt-3 alert alert-success text-center" style="display:none;">
                        <i class="bi bi-check-circle me-2"></i> Message sent successfully!
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

<style>
    .form-control:focus, .form-select:focus {
        background-color: #fff !important;
        box-shadow: 0 0 0 2px rgba(15, 52, 96, 0.1) !important; /* Subtle Blue glow */
    }
    
    /* Button Hover */
    button[type="submit"]:hover {
        background-color: #162447 !important; /* Darker Blue */
        transform: translateY(-2px);
    }
</style>
<script>
    $(document).ready(function() {
        
        $('#contactForm').on('submit', function(e) {
            e.preventDefault();
            
            // Clear previous errors
            $('.error-msg').remove();
            let btn = $(this).find('button[type="submit"]');
            let originalText = btn.html();
            
            // Show loading state
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Sending...');

            $.ajax({
                url: "<?= base_url('contact-submit') ?>",
                method: "POST",
                data: $(this).serialize(),
                dataType: "json",
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Ensure you have this meta tag!
                },
                success: function(response) {
                    btn.prop('disabled', false).html(originalText);

                    if (response.status === 'success') {
                        // Success: Reset form and show message
                        $('#contactForm')[0].reset();
                        $('#contactForm').slideUp();
                        $('#successMsg').html('<i class="bi bi-check-circle-fill me-2"></i> ' + response.message).fadeIn();
                    } else {
                        // Error: Show validation messages
                        $.each(response.errors, function(field, message) {
                            $('[name="' + field + '"]').after('<div class="text-danger small error-msg mt-1">' + message + '</div>');
                        });
                    }
                },
                error: function() {
                    btn.prop('disabled', false).html(originalText);
                    alert('Something went wrong. Please try again.');
                }
            });
        });

    });
</script>