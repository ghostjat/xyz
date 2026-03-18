<?php include APPPATH . 'Views/dashboard/layout/header.php'; ?>

<style>
    /* Professional Checkout UI Styles */
    body { background-color: #f3f4f6; }
    .checkout-wrapper { min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 2rem 1rem; }
    .checkout-card { background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.08); max-width: 1000px; width: 100%; display: flex; flex-direction: column; }
    @media (min-width: 992px) { .checkout-card { flex-direction: row; } }
    
    /* Left Sidebar (Order Summary) */
    .checkout-sidebar { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff; padding: 3rem 2rem; position: relative; }
    .checkout-sidebar::after { content: ''; position: absolute; top: 0; right: 0; bottom: 0; width: 4px; background: rgba(255,255,255,0.1); }
    .brand-title { font-size: 1.25rem; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 2rem; display: flex; align-items: center; }
    .brand-title i { color: #38bdf8; margin-right: 10px; font-size: 1.5rem; }
    
    .amount-display { font-size: 2.5rem; font-weight: 800; margin-bottom: 0.5rem; color: #fff; }
    .amount-label { font-size: 0.9rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; }
    
    .module-list { padding-left: 0; margin-top: 1.5rem; }
    .module-list li { list-style: none; font-size: 0.95rem; margin-bottom: 0.75rem; color: #cbd5e1; display: flex; align-items: flex-start; }
    .module-list li i { color: #38bdf8; margin-top: 4px; margin-right: 10px; font-size: 0.85rem; }
    
    .deliverables { background: rgba(255,255,255,0.05); border-radius: 12px; padding: 1.5rem; margin-top: 2rem; border: 1px solid rgba(255,255,255,0.1); }
    .deliverables h6 { color: #f8fafc; font-weight: 700; margin-bottom: 1rem; }
    .deliverables ul li i { color: #4ade80; }

    /* Right Main Area (Payment) */
    .checkout-main { padding: 3rem 2rem; flex: 1; background: #fff; }
    @media (min-width: 992px) { .checkout-main { padding: 3rem 4rem; } }
    
    .qr-container { background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 1.5rem; display: inline-block; position: relative; transition: all 0.3s ease; }
    .qr-container:hover { border-color: #38bdf8; background: #f0f9ff; }
    .qr-image { max-width: 180px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    
    .form-floating > label { color: #64748b; font-weight: 500; }
    .form-control:focus { border-color: #38bdf8; box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.1); }
    
    .btn-pay { background: #0f172a; color: #fff; font-weight: 600; padding: 1rem; border-radius: 8px; transition: all 0.3s; border: none; }
    .btn-pay:hover { background: #1e293b; transform: translateY(-1px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
    
    .support-footer { text-align: center; margin-top: 2rem; font-size: 0.85rem; color: #64748b; }
    .support-footer a { color: #0f172a; font-weight: 600; text-decoration: none; }
</style>

<div class="checkout-wrapper">
    <div class="checkout-card">
        
        <div class="checkout-sidebar col-lg-5">
            <div class="brand-title">
                <i class="fas fa-compass"></i> Pharos Education Consultancy
            </div>
            
            <div class="mb-4">
                <div class="amount-label">Amount to Pay</div>
                <div class="amount-display">₹<?= esc(number_format($amount, 2)) ?></div>
            </div>

            <hr style="border-color: rgba(255,255,255,0.2);">

            <h5 class="fw-bold mt-4 text-white">7D Psychometric Assessments</h5>
            <p class="text-sm text-slate-400 mb-3">Comprehensive Career Profiling Suite</p>
            
            <ul class="module-list">
                <li><i class="fas fa-check-circle"></i> Career Interest</li>
                <li><i class="fas fa-check-circle"></i> Personality Type</li>
                <li><i class="fas fa-check-circle"></i> Emotional Intelligence</li>
                <li><i class="fas fa-check-circle"></i> Multiple Intelligences</li>
                <li><i class="fas fa-check-circle"></i> Professional Aptitude</li>
                <li><i class="fas fa-check-circle"></i> Learning Styles</li>
                <li><i class="fas fa-check-circle"></i> Career Motivators</li>
            </ul>

            <div class="deliverables">
                <h6><i class="fas fa-gift me-2 text-warning"></i> What you will get:</h6>
                <ul class="module-list mb-0 mt-0" style="font-size: 0.9rem;">
                    <li><i class="fas fa-star"></i> Detailed 20-30 Page Career Dossier</li>
                    <li><i class="fas fa-star"></i> Top Matched Career Recommendations</li>
                    <li><i class="fas fa-star"></i> Helpful In Stream Selection</li>
                </ul>
            </div>
        </div>

        <div class="checkout-main col-lg-7">
            
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                <h4 class="fw-bold text-dark mb-0">Secure UPI Payment</h4>
                <div class="text-muted"><i class="fas fa-lock"></i> SSL Secured</div>
            </div>

            <div class="text-center mb-5">
                <p class="text-muted fw-medium mb-3">Scan the QR code using any UPI app</p>
                <div class="qr-container">
                    <img src="<?= base_url('assets/img/receive_upi_image.webp') ?>" alt="Pharos UPI QR Code" class="qr-image img-fluid">
                </div>
            </div>

            <h5 class="fw-bold text-dark mb-3 fs-6 uppercase text-uppercase tracking-wider">Confirm Your Transaction</h5>
            
            <form id="manualPaymentForm">
                <?= csrf_field() ?>
                <input type="hidden" name="payment_id" value="<?= esc($payment_id) ?>">
                
                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <div class="form-floating">
                            <input type="text" name="payer_name" class="form-control" id="payerName" placeholder="Full Name" value="<?= esc($user['full_name']) ?>" required>
                            <label for="payerName">Payer Name</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-floating">
                            <input type="number" name="amount_paid" class="form-control bg-light" id="amountPaid" value="<?= esc($amount) ?>" readonly required>
                            <label for="amountPaid">Amount (₹)</label>
                        </div>
                    </div>
                </div>

                <div class="form-floating mb-4">
                    <input type="text" name="upi_reference" class="form-control" id="upiRef" placeholder="12-digit UPI Reference No." pattern="\d{12}" title="Please enter the exact 12-digit UPI reference number (UTR)" required>
                    <label for="upiRef">12-Digit UPI Reference No. (UTR)</label>
                    <div class="form-text mt-2"><i class="fas fa-info-circle text-primary"></i> Found in your UPI app's transaction history after successful payment.</div>
                </div>

                <button type="submit" id="submitPaymentBtn" class="btn btn-pay w-100 shadow-lg">
                    Verify Payment & Unlock Assessments <i class="fas fa-arrow-right ms-2"></i>
                </button>
            </form>

            <div class="support-footer">
                Having trouble with your payment? <br>
                Contact us at <a href="mailto:support@pharoseducation.in"><i class="fas fa-envelope"></i> support@pharoseducation.in</a>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentForm = document.getElementById('manualPaymentForm');
    const submitBtn = document.getElementById('submitPaymentBtn');

    paymentForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // UX: Change button state
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin me-2"></i> Verifying Transaction...';
        submitBtn.disabled = true;
        
        let formData = new FormData(paymentForm);
        
        fetch('<?= base_url('payment/verify') ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                // Success State
                submitBtn.innerHTML = '<i class="fas fa-check-circle me-2"></i> Verified Successfully!';
                submitBtn.classList.replace('btn-dark', 'btn-success');
                
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1500);
            } else {
                // Error State
                alert(data.msg || 'Verification failed. Please check your Reference Number.');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('A network error occurred. Please try again.');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
});
</script>

<?php include APPPATH . 'Views/dashboard/layout/footer.php'; ?>