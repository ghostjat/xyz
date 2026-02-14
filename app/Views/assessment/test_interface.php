<?= $this->include('layouts/header') ?>

<div class="container mt-4 mb-5">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            
            <div id="debugAlert" class="alert alert-danger" style="display:none;"></div>

            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1" id="testTitle">
                                <?= esc($category['name'] ?? 'Loading Assessment...') ?>
                            </h4>
                            <p class="text-muted mb-0" id="testDescription">
                                <?= esc($category['description'] ?? 'Please wait while we load your questions.') ?>
                            </p>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-primary fs-6" id="questionCounter">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body p-2">
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" 
                             id="progressBar"
                             style="width: 0%">
                            <span id="progressText">0%</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4" id="questionCard">
                <div class="card-body p-5">
                    <div id="statusMessage" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h5 class="mt-3 text-muted">Loading questions...</h5>
                    </div>

                    <div id="questionContent" style="display:none;">
                        <h5 class="mb-4" id="questionText"></h5>
                        <div id="answerOptions"></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <button class="btn btn-outline-secondary" id="btnPrevious" onclick="previousQuestion()" disabled>
                            <i class="fas fa-arrow-left"></i> Previous
                        </button>
                        
                        <button class="btn btn-outline-warning" id="btnSkip" onclick="skipQuestion()">
                            Skip <i class="fas fa-forward"></i>
                        </button>
                        
                        <button class="btn btn-primary" id="btnNext" onclick="nextQuestion()" disabled style="display:none;">
                            Next <i class="fas fa-arrow-right"></i>
                        </button>
                        
                        <button class="btn btn-success" id="btnFinish" onclick="finishTest()" style="display: none;">
                            <i class="fas fa-check"></i> Finish Test
                        </button>
                    </div>
                </div>
            </div>

            <div class="text-center mt-3">
                <small class="text-muted">
                    <i class="fas fa-clock"></i> Time: <span id="timer">00:00</span>
                </small>
            </div>

        </div>
    </div>
</div>

<script>
    const csrfName = '<?= csrf_token() ?>';
    const csrfHash = '<?= csrf_hash() ?>';
</script>

<script>
// Test State
let testState = {
    // Safely inject PHP variables
    sessionId: '<?= $session['id'] ?? '' ?>',
    categoryCode: '<?= $category['category_code'] ?? '' ?>',
    attemptId: '<?= $attempt['id'] ?? '' ?>',
    questions: [],
    currentIndex: 0,
    responses: {},
    startTime: null
};

$(document).ready(function() {
    // 1. Check for missing IDs immediately
    if (!testState.sessionId || !testState.categoryCode) {
        showError('Session ID or Category Code is missing. Please return to dashboard.');
        return;
    }

    // 2. Setup CSRF for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': csrfHash,
            'X-Requested-With': 'XMLHttpRequest'
        }
    });

    // 3. Start Loading
    loadQuestions();
    startTimer();
});

function loadQuestions() {
    const url = `<?= base_url('api/assessment/questions') ?>/${testState.sessionId}/${testState.categoryCode}`;
    
    console.log("Fetching questions from:", url); // Debug log

    $.ajax({
        url: url,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log("API Response:", response); // Debug log

            if (response.success && response.data) {
                testState.questions = response.data.questions || [];
                
                // Update Attempt ID if provided
                if(response.data.attempt_id) {
                    testState.attemptId = response.data.attempt_id;
                }

                // Check if questions exist
                if (testState.questions.length > 0) {
                    $('#statusMessage').hide(); // Hide loader
                    $('#questionContent').fadeIn(); // Show question area
                    displayQuestion(0);
                } else {
                    showError('No questions found for this category.');
                }
            } else {
                showError(response.message || 'Failed to load assessment data.');
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", error);
            console.error("Response Text:", xhr.responseText);
            
            let msg = 'Connection error.';
            if (xhr.status === 404) msg = 'API Endpoint not found (404). Check Routes.';
            if (xhr.status === 500) msg = 'Server Error (500). Check Logs.';
            
            showError(`${msg} (${error})`);
        }
    });
}

function displayQuestion(index) {
    if (index < 0 || index >= testState.questions.length) return;
    
    testState.currentIndex = index;
    const question = testState.questions[index];
    
    // Update counter
    $('#questionCounter').text(`${index + 1} / ${testState.questions.length}`);
    
    // Update progress
    const progress = ((index + 1) / testState.questions.length) * 100;
    $('#progressBar').css('width', progress + '%');
    
    // Update Text
    $('#questionText').html(question.text); 
    
    // Render Options
    const container = $('#answerOptions');
    container.empty();
    
    // Check local saved response
    const existingValue = testState.responses[question.id]?.value;

    let html = '';
    try {
        switch (question.type) {
            case 'likert_5':
                html = createLikertScale(question.id, 5, ['Strongly Disagree', 'Disagree', 'Neutral', 'Agree', 'Strongly Agree'], existingValue);
                break;
            case 'likert_7':
                html = createLikertScale(question.id, 7, [], existingValue); // Empty labels for generic 1-7
                break;
            case 'yes_no':
                html = createYesNoOptions(question.id, existingValue);
                break;
            case 'multiple_choice':
                html = createMultipleChoice(question.id, question.options, existingValue);
                break;
            default:
                html = `<div class="alert alert-warning">Unknown question type: ${question.type}</div>`;
        }
    } catch (e) {
        console.error("Render Error:", e);
        html = `<div class="alert alert-danger">Error rendering options: ${e.message}</div>`;
    }
    
    container.html(html);
    updateNavigationButtons();
}

function showError(msg) {
    $('#statusMessage').html(`<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ${msg}</div>`);
    $('#questionCard').addClass('border-danger');
    $('#btnNext, #btnSkip, #btnPrevious, #btnFinish').prop('disabled', true);
    
    // Also show top debug alert
    $('#debugAlert').text("Debug: " + msg).show();
}

// --- Option Renderers ---

function createLikertScale(questionId, count, labels, selectedValue) {
    let html = '<div class="likert-scale">';
    for (let i = 1; i <= count; i++) {
        const isChecked = selectedValue == i ? 'checked' : '';
        const activeClass = selectedValue == i ? 'active-option' : '';
        const labelText = (labels && labels[i-1]) ? `<br><small>${labels[i-1]}</small>` : '';
        
        html += `
            <div class="likert-option ${activeClass}">
                <input type="radio" name="q_${questionId}" id="opt_${questionId}_${i}" value="${i}" ${isChecked} onchange="selectAnswer(${questionId}, ${i})">
                <label for="opt_${questionId}_${i}">${i}${labelText}</label>
            </div>
        `;
    }
    html += '</div>';
    return html;
}

function createYesNoOptions(questionId, selectedValue) {
    const yesActive = selectedValue == 1 ? 'active' : '';
    const noActive = selectedValue === 0 || selectedValue === '0' ? 'active' : '';

    return `
        <div class="d-grid gap-3 col-md-8 mx-auto">
            <button class="btn btn-outline-success btn-lg ${yesActive}" onclick="selectAnswer(${questionId}, 1)">Yes</button>
            <button class="btn btn-outline-danger btn-lg ${noActive}" onclick="selectAnswer(${questionId}, 0)">No</button>
        </div>
    `;
}

function createMultipleChoice(questionId, options, selectedValue) {
    let html = '<div class="d-grid gap-2 col-md-8 mx-auto">';
    const opts = Array.isArray(options) ? options : [];
    
    opts.forEach((option, index) => {
        const isActive = selectedValue == index ? 'active' : '';
        html += `
            <button class="btn btn-outline-primary text-start p-3 ${isActive}" onclick="selectAnswer(${questionId}, ${index})">
                <strong>${String.fromCharCode(65 + index)}.</strong> ${option}
            </button>
        `;
    });
    
    html += '</div>';
    return html;
}

// --- Interaction Logic ---

function selectAnswer(questionId, value) {
    // 1. Update State
    testState.responses[questionId] = {
        value: value,
        timeTaken: 0 // Simplification for now
    };
    
    // 2. Save to Server (Silent)
    $.ajax({
        url: '<?= base_url('api/assessment/response') ?>',
        method: 'POST',
        data: {
            attempt_id: testState.attemptId,
            question_id: questionId,
            response_value: value
        }
    });

    // 3. UI Updates
    $('#btnNext').show().prop('disabled', false);
    $('#btnSkip').hide();
    updateNavigationButtons();
}

function nextQuestion() {
    if (testState.currentIndex < testState.questions.length - 1) {
        displayQuestion(testState.currentIndex + 1);
    }
}

function previousQuestion() {
    if (testState.currentIndex > 0) {
        displayQuestion(testState.currentIndex - 1);
    }
}

function skipQuestion() {
    const q = testState.questions[testState.currentIndex];
    
    // Mark skipped
    $.ajax({
        url: '<?= base_url('api/assessment/response') ?>',
        method: 'POST',
        data: {
            attempt_id: testState.attemptId,
            question_id: q.id,
            is_skipped: 1
        }
    });
    
    nextQuestion();
}

function updateNavigationButtons() {
    $('#btnPrevious').prop('disabled', testState.currentIndex === 0);
    
    const isLast = testState.currentIndex === testState.questions.length - 1;
    const currentQ = testState.questions[testState.currentIndex];
    const hasAnswer = testState.responses[currentQ.id]?.value !== undefined;

    if (isLast) {
        $('#btnNext').hide();
        $('#btnSkip').hide();
        $('#btnFinish').show(); 
    } else {
        $('#btnFinish').hide();
        if (hasAnswer) {
            $('#btnNext').show();
            $('#btnSkip').hide();
        } else {
            $('#btnNext').hide();
            $('#btnSkip').show();
        }
    }
}

function finishTest() {
    if (!confirm('Are you sure you want to finish this section?')) return;
    
    // Disable button and show loading state
    $('#btnFinish').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');

    $.ajax({
        url: '<?= base_url('api/assessment/complete') ?>',
        method: 'POST',
        data: { attempt_id: testState.attemptId },
        success: function(response) {
            if (response.success) {
                // Show success message briefly
                // Check what to do next
                if (response.data.next_test_url) {
                    // Case 1: More tests exist -> Redirect to next test
                    // alert(response.message); // Optional: Remove alert for smoother flow
                    window.location.href = response.data.next_test_url;
                } 
                else if (response.data.report_url) {
                    // Case 2: All done -> Redirect to report
                    window.location.href = response.data.report_url;
                } 
                else {
                    // Case 3: Fallback
                    window.location.href = '<?= base_url('dashboard') ?>';
                }
            } else {
                 alert('Error: ' + response.message);
                 $('#btnFinish').prop('disabled', false).text('Finish Test');
            }
        },
        error: function(xhr) {
            alert('Server Error: ' + xhr.responseText);
            $('#btnFinish').prop('disabled', false).text('Finish Test');
        }
    });
}

function startTimer() {
    let seconds = 0;
    setInterval(function() {
        seconds++;
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        $('#timer').text(`${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`);
    }, 1000);
}
</script>

<style>
.likert-scale { display: flex; gap: 10px; margin: 20px 0; }
.likert-option { flex: 1; text-align: center; }
.likert-option input { display: none; }
.likert-option label { 
    display: block; padding: 15px; background: #fff; 
    border: 2px solid #e9ecef; border-radius: 8px; cursor: pointer; 
}
.likert-option input:checked + label {
    background: #0d6efd; color: white; border-color: #0d6efd;
}
</style>

<?= $this->include('layouts/footer') ?>