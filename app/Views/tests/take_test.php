<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?= strtoupper(esc($module)) ?> Assessment</title>
        <?= csrf_meta();?>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <style>
            body { background-color: #f8fafc; }
            .question-card { display: none; }
            .question-card.active { display: block; animation: fadeIn 0.5s; }
            @keyframes fadeIn { from { opacity:0; transform: translateY(10px); } to { opacity:1; transform: translateY(0); } }
            
            /* Strict Warning Bar */
            .strict-warning { background-color: #fff3cd; color: #856404; border-bottom: 1px solid #ffeeba; font-size: 0.9rem; font-weight: 600; }
            
            /* New Aptitude Instruction Bar */
            .aptitude-instruction { background-color: #cff4fc; color: #055160; border-bottom: 1px solid #b6effb; font-size: 0.95rem; font-weight: 700; }
            
            /* Timer Styling */
            .timer-display { font-family: 'Courier New', monospace; font-size: 1.4rem; letter-spacing: 1px; }
        </style>
    </head>
    <body>

        <div class="strict-warning py-2 text-center shadow-sm">
            <i class="fas fa-exclamation-triangle me-2"></i> 
            <strong>Attention:</strong> You must complete this assessment in one sitting. Do not refresh or leave this page.
        </div>

        <?php if ($module === 'aptitude'): ?>
        <div class="aptitude-instruction py-3 text-center shadow-sm">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-12">
                        <i class="fas fa-stopwatch me-2 text-danger"></i>
                        SPEED CHALLENGE ENABLED: You have exactly <span class="text-danger text-decoration-underline">60 SECONDS</span> per question.
                        <span class="d-block d-md-inline text-muted small fw-normal ms-md-2">
                            (Questions auto-submit as WRONG if time runs out)
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="container mt-4">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-dark text-white py-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h4 class="mb-0 fw-bold"><?= strtoupper(esc($module)) ?> Assessment</h4>
                        <span class="badge bg-warning text-dark fs-6">Step <span id="currStep">1</span> of <?= count(esc($questions)) ?></span>
                    </div>
                    
                    <?php if ($module === 'aptitude'): ?>
                    <div class="bg-secondary rounded p-2 mt-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small font-weight-bold text-light text-uppercase"><i class="fas fa-clock"></i> Time Remaining</span>
                            <span class="fw-bold text-warning timer-display" id="timerText">60s</span>
                        </div>
                        <div class="progress" style="height: 12px; background-color: #444;">
                            <div class="progress-bar bg-success" id="timerBar" style="width: 100%; transition: width 1s linear;"></div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="progress mt-3 bg-secondary" style="height: 6px;">
                        <div class="progress-bar bg-success transition-all" id="progressBar" style="width: 0%"></div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="card-body p-5">
                    <form id="testForm">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="module_code" value="<?= esc($module) ?>">

                        <?php foreach ($questions as $index => $q): 
                            $options = $q['options_json'] ? json_decode($q['options_json'], true) : null;
                        ?>
                            <div class="question-card" data-index="<?= esc($index) ?>" data-qid="<?= esc($q['id']) ?>" style="display: <?= esc($index) === 0 ? 'block' : 'none' ?>;">
                                
                                <?php if ($module === 'aptitude'): ?>
                                    <input type="hidden" name="time_taken[<?= esc($q['id']) ?>]" class="time-input" value="0">
                                <?php endif; ?>

                                <h3 class="fw-light text-center mb-5"><?= esc($q['question_text']) ?></h3>

                                <div class="row justify-content-center">
                                    <div class="col-md-10">
                                        <?php if ($q['input_type'] == 'forced_choice'): ?>
                                            <div class="row g-4">
                                                <?php
                                                if (is_array($options)):
                                                    foreach ($options as $optKey => $opt):
                                                        $inputValue = $opt['label'] ?? $opt['val'] ?? $optKey;
                                                        $displayText = $opt['text'] ?? (is_string($opt) ? $opt : 'Option');
                                                        $displayLabel = $opt['label'] ?? strtoupper($optKey);
                                                        $labelPrefix = is_numeric($displayLabel) ? '' : esc($displayLabel) . '. ';
                                                ?>
                                                        <div class="col-md-6">
                                                            <input type="radio" class="btn-check" name="answers[<?= esc($q['id']) ?>]" id="q<?= esc($q['id']) ?>_<?= esc($optKey) ?>" value="<?= esc($inputValue) ?>" required>
                                                            <label class="btn btn-outline-primary w-100 p-4 h-100 d-flex align-items-center justify-content-center shadow-sm" for="q<?= esc($q['id']) ?>_<?= esc($optKey) ?>">
                                                                <?php if ($labelPrefix): ?>
                                                                    <span class="fs-5 fw-bold me-2"><?= esc($labelPrefix) ?></span>
                                                                <?php endif; ?>
                                                                <span class="fs-5"><?= esc($displayText) ?></span>
                                                            </label>
                                                        </div>
                                                <?php endforeach; endif; ?>
                                            </div>
                                        <?php elseif ($q['input_type'] == 'likert_3'): ?>
                                            <div class="d-flex justify-content-center gap-3">
                                                <input type="radio" class="btn-check" name="answers[<?= esc($q['id']) ?>]" id="q<?= esc($q['id']) ?>_dislike" value="0" required>
                                                <label class="btn btn-outline-danger px-4 py-3 shadow-sm" for="q<?= esc($q['id']) ?>_dislike"><i class="fas fa-thumbs-down"></i> Dislike</label>
                                                
                                                <input type="radio" class="btn-check" name="answers[<?= esc($q['id']) ?>]" id="q<?= esc($q['id']) ?>_neutral" value="1" required>
                                                <label class="btn btn-outline-secondary px-4 py-3 shadow-sm" for="q<?= esc($q['id']) ?>_neutral">Neutral</label>

                                                <input type="radio" class="btn-check" name="answers[<?= esc($q['id']) ?>]" id="q<?= esc($q['id']) ?>_like" value="2" required>
                                                <label class="btn btn-outline-success px-4 py-3 shadow-sm" for="q<?= esc($q['id']) ?>_like"><i class="fas fa-thumbs-up"></i> Like</label>
                                            </div>
                                        <?php else: ?>
                                            <div class="d-flex justify-content-between bg-white border p-4 rounded shadow-sm">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <div class="form-check text-center mx-2">
                                                        <input class="form-check-input" type="radio" name="answers[<?= esc($q['id']) ?>]" id="q<?= esc($q['id']) ?>_<?= esc($i) ?>" value="<?= esc($i) ?>" required style="transform: scale(1.5);">
                                                        <label class="d-block mt-3 fw-bold fs-5 text-dark" for="q<?= esc($q['id']) ?>_<?= esc($i) ?>"><?= esc($i) ?></label>
                                                    </div>
                                                <?php endfor; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="mt-5 d-flex justify-content-between">
                                    <button type="button" class="btn btn-link text-muted prev-btn text-decoration-none" <?= esc($index) == 0 ? 'disabled' : '' ?>>&larr; Previous</button>
                                    <button type="button" class="btn btn-dark px-5 py-2 next-btn rounded-pill shadow">
                                        <?= (esc($index) < count(esc($questions)) - 1) ? 'Next Question <i class="fas fa-arrow-right ms-2"></i>' : 'Submit Assessment <i class="fas fa-check ms-2"></i>' ?>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </form>
                    <div id="reviewScreen" style="display: none;">
                        <div class="text-center mb-4">
                            <i class="fas fa-clipboard-check fa-3x text-success mb-3"></i>
                            <h3 class="fw-bold">Review Your Answers</h3>
                            <p class="text-muted">Please review your selections before final submission.</p>
                        </div>
                        
                        <div class="table-responsive shadow-sm rounded border">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="5%" class="text-center">#</th>
                                        <th width="65%">Question</th>
                                        <th width="30%" class="text-center">Your Answer</th>
                                    </tr>
                                </thead>
                                <tbody id="reviewTableBody">
                                    </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-5 d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-outline-secondary px-4 py-2" id="btnEditAnswers">
                                <i class="fas fa-edit me-2"></i> Edit Answers
                            </button>
                            <button type="button" class="btn btn-success px-5 py-2 shadow-lg" id="btnFinalSubmit">
                                Confirm & Submit <i class="fas fa-paper-plane ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // GLOBAL AJAX SECURITY: Attach CSRF Token to every POST request automatically
$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="X-CSRF-TOKEN"]').attr('content') }
});
            $(function () {
                let currentStep = 0;
                const totalSteps = <?= count(esc($questions)) ?>;
                const $questions = $('.question-card');
                let isSubmitting = false; 
                let isAutoAdvancing = false;

                <?php if ($module === 'aptitude'): ?>
                // ==========================================
                // APTITUDE STRICT TIMING ENGINE
                // ==========================================
                let timeLimit = 60; 
                let timeLeft = timeLimit;
                let timerInterval;

                function startQuestionTimer() {
                    timeLeft = timeLimit;
                    $('#timerText').text(timeLeft + 's');
                    $('#timerBar').css('width', '100%').removeClass('bg-danger bg-warning').addClass('bg-success');
                    clearInterval(timerInterval);

                    timerInterval = setInterval(function () {
                        timeLeft--;
                        $('#timerText').text(timeLeft + 's');
                        let percentage = (timeLeft / timeLimit) * 100;
                        $('#timerBar').css('width', percentage + '%');

                        // Color Coding for Urgency
                        if (timeLeft <= 10) { 
                            $('#timerBar').removeClass('bg-warning').addClass('bg-danger'); 
                            $('#timerText').addClass('text-danger').removeClass('text-warning');
                        } 
                        else if (timeLeft <= 30) { 
                            $('#timerBar').removeClass('bg-success').addClass('bg-warning'); 
                        }

                        // Auto-Advance Logic
                        if (timeLeft <= 0) {
                            clearInterval(timerInterval);
                            autoAdvance();
                        }
                    }, 1000);
                }

                function autoAdvance() {
                    isAutoAdvancing = true; // Flag to bypass "Please select an option" check
                    let currentQ = $questions.eq(currentStep);
                    currentQ.find('.time-input').val(timeLimit); // Log max time

                    // If no answer selected, inject hidden TIMEOUT value so PHP grades it as 0
                    if (currentQ.find('input[type="radio"]:checked').length === 0) {
                        currentQ.append('<input type="hidden" name="answers[' + currentQ.data('qid') + ']" value="TIMEOUT">');
                    }
                    
                    // Trigger the Next button click programmatically
                    $('.next-btn').eq(currentStep).trigger('click');
                }

                // Start timer immediately
                startQuestionTimer();
                <?php endif; ?>

                // Prevent accidentally leaving
                window.addEventListener('beforeunload', function (e) {
                    if (!isSubmitting) {
                        e.preventDefault(); e.returnValue = '';
                    }
                });

                // Auto-advance on radio selection (for UX)
                $('input[type=radio]').change(function () {
                    // Only auto-advance if it's NOT the last question
                    let $nextButton = $(this).closest('.question-card').find('.next-btn');
                    //if ($(this).closest('.row').find('.btn-outline-primary').length > 0 && currentStep < totalSteps - 1) {
                        setTimeout(() => {
                             if (currentStep < totalSteps - 1) {
                            $nextButton.trigger('click');
                        }
                        }, 300);
                    //}
                });

                $('.next-btn').click(function () {
                    let currentQ = $questions.eq(currentStep);

                    // VALIDATION: If manual click AND no selection AND not auto-advancing -> Stop user
                    if (!isAutoAdvancing && currentQ.find('input:checked').length === 0) {
                        alert('Please select an option before proceeding.');
                        return;
                    }

                    <?php if ($module === 'aptitude'): ?>
                    // If manually clicked, record actual time spent
                    if (!isAutoAdvancing) {
                        let timeSpent = timeLimit - timeLeft;
                        currentQ.find('.time-input').val(timeSpent);
                    }
                    clearInterval(timerInterval);
                    <?php endif; ?>

                    // Handle Submit or Next
                    if (currentStep === totalSteps - 1) {
                        //$('#testForm').submit();
                        generateReviewTable();
                        return;
                    }

                    currentQ.fadeOut(200, function () {
                        currentStep++;
                        $questions.eq(currentStep).fadeIn(200);
                        updateProgress();
                        
                        // Reset flags and restart timer for next question
                        isAutoAdvancing = false; 
                        
                        <?php if ($module === 'aptitude'): ?>
                        startQuestionTimer();
                        <?php endif; ?>
                    });
                });
                
                

                function updateProgress() {
                    let pct = ((currentStep + 1) / totalSteps) * 100;
                    <?php if ($module !== 'aptitude'): ?>
                        $('#progressBar').css('width', pct + '%');
                    <?php endif; ?>
                    $('#currStep').text(currentStep + 1);
                }

                $('#testForm').submit(function (e) {
                    e.preventDefault();
                    
                    // Add a tiny 100ms delay to ensure the last radio button click is fully registered
                    setTimeout(() => {
                        let formData = $(this).serialize();
                        
                        // DEBUGGER: This will print the exact data being sent to your browser's F12 Console!
                        console.log("SENDING THIS TO SERVER:", formData); 
                        
                        isSubmitting = true;
                        let submitBtn = $('.next-btn').last();
                        submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i> Analyzing...').prop('disabled', true);

                        $.ajax({
                            url: '<?= base_url("tests/submit") ?>',
                            method: 'POST',
                            data: formData, // Use the serialized data from above
                            dataType: 'json',
                            success: function (res) {
                                if (res.status === 'success') { window.location.href = res.redirect; } 
                                else { alert(res.msg); submitBtn.prop('disabled', false); isSubmitting = false; }
                            },
                            error: function() {
                                alert('Submission failed. Please check your connection.');
                                submitBtn.prop('disabled', false);
                                isSubmitting = false;
                            }
                        });
                    }, 100); // 100ms delay
                });
                
                // ==========================================
                // REVIEW SCREEN GENERATOR
                // ==========================================
                function generateReviewTable() {
                    $('#reviewTableBody').empty(); // Clear out any old rows
                    
                    // Loop through every question card on the page
                    $('.question-card').each(function(index) {
                        let qNum = index + 1;
                        let qText = $(this).find('h3').text();
                        let selectedInput = $(this).find('input[type="radio"]:checked');
                        let answerText = '<span class="badge bg-danger">Not Answered</span>';

                        if (selectedInput.length > 0) {
                            // Find the label attached to this radio button to get the human-readable text
                            let inputId = selectedInput.attr('id');
                            let label = $(this).find('label[for="' + inputId + '"]');
                            
                            // .text().trim() grabs just the words (e.g., "Neutral" or "Option A") ignoring the icons
                            answerText = '<span class="text-primary fw-bold">' + label.text().trim() + '</span>';
                        } else if ($(this).find('input[type="hidden"][value="TIMEOUT"]').length > 0) {
                            // Specifically for the Aptitude test if time ran out
                            answerText = '<span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> Time Out</span>';
                        }

                        // Inject the row into the table
                        $('#reviewTableBody').append(`
                            <tr>
                                <td class="text-center fw-bold">${qNum}</td>
                                <td>${qText}</td>
                                <td class="text-center">${answerText}</td>
                            </tr>
                        `);
                    });

                    // Hide the active test interface and show the review screen
                    $('#testForm').hide();
                    $('.card-header').hide(); // Hides the progress bar and timer
                    $('#reviewScreen').fadeIn();
                }

                // ==========================================
                // REVIEW SCREEN BUTTON CONTROLS
                // ==========================================
                $('#btnEditAnswers').click(function() {
                    // Send them back to the form if they want to make changes
                    $('#reviewScreen').hide();
                    $('.card-header').fadeIn();
                    $('#testForm').fadeIn();
                });

                $('#btnFinalSubmit').click(function() {
                    // Disable the button immediately to prevent double clicks
                    $(this).html('<i class="fas fa-spinner fa-spin me-2"></i> Submitting...').prop('disabled', true);
                    $('#btnEditAnswers').prop('disabled', true);
                    
                    // Trigger the actual AJAX form submission
                    $('#testForm').submit(); 
                });
            });
        </script>
    </body>
</html>