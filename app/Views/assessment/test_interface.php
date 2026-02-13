<?= $this->include('layouts/header') ?>

<div class="container mt-4 mb-5">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            
            <!-- Test Header -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1" id="testTitle">Loading...</h4>
                            <p class="text-muted mb-0" id="testDescription">Please wait...</p>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-primary fs-6" id="questionCounter">0 / 0</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress Bar -->
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

            <!-- Question Card -->
            <div class="card mb-4" id="questionCard">
                <div class="card-body p-5">
                    <h5 class="mb-4" id="questionText">Loading question...</h5>
                    
                    <div id="answerOptions">
                        <!-- Options will be inserted here dynamically -->
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <button class="btn btn-outline-secondary" id="btnPrevious" onclick="previousQuestion()" disabled>
                            <i class="fas fa-arrow-left"></i> Previous
                        </button>
                        
                        <button class="btn btn-outline-warning" id="btnSkip" onclick="skipQuestion()">
                            <i class="fas fa-forward"></i> Skip
                        </button>
                        
                        <button class="btn btn-primary" id="btnNext" onclick="nextQuestion()" disabled>
                            Next <i class="fas fa-arrow-right"></i>
                        </button>
                        
                        <button class="btn btn-success" id="btnFinish" onclick="finishTest()" style="display: none;">
                            <i class="fas fa-check"></i> Finish Test
                        </button>
                    </div>
                </div>
            </div>

            <!-- Timer (Optional) -->
            <div class="text-center mt-3">
                <small class="text-muted">
                    <i class="fas fa-clock"></i> Time: <span id="timer">00:00</span>
                </small>
            </div>

        </div>
    </div>
</div>

<script>
// Test State
let testState = {
    sessionId: <?= $session_id ?? 0 ?>,
    attemptId: null,
    categoryCode: '<?= $category_code ?? 'RIASEC' ?>',
    questions: [],
    currentIndex: 0,
    responses: {},
    startTime: null
};

// Initialize Test
$(document).ready(function() {
    loadQuestions();
    startTimer();
});

// Load Questions
function loadQuestions() {
    showLoading();
    
    $.ajax({
        url: `<?= base_url('api/assessment/questions/') ?>${testState.sessionId}/${testState.categoryCode}`,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            hideLoading();
            if (response.success) {
                testState.questions = response.data.questions;
                testState.attemptId = response.data.attempt_id;
                
                // Update header
                const category = response.data.category;
                $('#testTitle').text(category.category_name);
                $('#testDescription').text(category.description || '');
                
                // Load first question
                displayQuestion(0);
            } else {
                alert('Error loading questions: ' + response.message);
            }
        },
        error: function() {
            hideLoading();
            alert('Failed to load questions. Please try again.');
        }
    });
}

// Display Question
function displayQuestion(index) {
    if (index < 0 || index >= testState.questions.length) return;
    
    testState.currentIndex = index;
    const question = testState.questions[index];
    
    // Update counter
    $('#questionCounter').text(`${index + 1} / ${testState.questions.length}`);
    
    // Update progress
    const progress = ((index + 1) / testState.questions.length) * 100;
    $('#progressBar').css('width', progress + '%');
    $('#progressText').text(Math.round(progress) + '%');
    
    // Display question text
    $('#questionText').text(question.text);
    
    // Display answer options based on type
    displayAnswerOptions(question);
    
    // Load saved response if exists
    if (testState.responses[question.id]) {
        setSelectedAnswer(testState.responses[question.id]);
    }
    
    // Update navigation buttons
    updateNavigationButtons();
}

// Display Answer Options
function displayAnswerOptions(question) {
    const container = $('#answerOptions');
    container.empty();
    
    switch (question.type) {
        case 'likert_5':
            container.html(createLikert5Scale(question.id));
            break;
        case 'likert_7':
            container.html(createLikert7Scale(question.id));
            break;
        case 'yes_no':
            container.html(createYesNoOptions(question.id));
            break;
        case 'multiple_choice':
            container.html(createMultipleChoice(question.id, question.options));
            break;
        default:
            container.html('<p class="text-muted">Unsupported question type</p>');
    }
}

// Create Likert 5-Point Scale
function createLikert5Scale(questionId) {
    const labels = ['Strongly Disagree', 'Disagree', 'Neutral', 'Agree', 'Strongly Agree'];
    let html = '<div class="likert-scale">';
    
    for (let i = 1; i <= 5; i++) {
        html += `
            <div class="likert-option">
                <input type="radio" name="answer" id="answer_${i}" value="${i}" onchange="selectAnswer(${questionId}, ${i})">
                <label for="answer_${i}">${i}<br><small>${labels[i-1]}</small></label>
            </div>
        `;
    }
    
    html += '</div>';
    return html;
}

// Create Likert 7-Point Scale
function createLikert7Scale(questionId) {
    let html = '<div class="likert-scale">';
    
    for (let i = 1; i <= 7; i++) {
        html += `
            <div class="likert-option">
                <input type="radio" name="answer" id="answer_${i}" value="${i}" onchange="selectAnswer(${questionId}, ${i})">
                <label for="answer_${i}">${i}</label>
            </div>
        `;
    }
    
    html += '</div>';
    return html;
}

// Create Yes/No Options
function createYesNoOptions(questionId) {
    return `
        <div class="d-grid gap-3">
            <button class="btn btn-outline-success btn-lg" onclick="selectAnswer(${questionId}, 1)">
                <i class="fas fa-check"></i> Yes
            </button>
            <button class="btn btn-outline-danger btn-lg" onclick="selectAnswer(${questionId}, 0)">
                <i class="fas fa-times"></i> No
            </button>
        </div>
    `;
}

// Create Multiple Choice
function createMultipleChoice(questionId, options) {
    let html = '<div class="d-grid gap-2">';
    
    options.forEach((option, index) => {
        html += `
            <button class="btn btn-outline-primary text-start" onclick="selectAnswer(${questionId}, ${index})">
                <strong>${String.fromCharCode(65 + index)}.</strong> ${option}
            </button>
        `;
    });
    
    html += '</div>';
    return html;
}

// Select Answer
function selectAnswer(questionId, value) {
    const startTime = testState.responses[questionId]?.startTime || Date.now();
    const timeTaken = Math.round((Date.now() - startTime) / 1000);
    
    testState.responses[questionId] = {
        value: value,
        startTime: startTime,
        timeTaken: timeTaken
    };
    
    // Save response to server
    saveResponse(questionId, value, timeTaken);
    
    // Enable next button
    $('#btnNext').prop('disabled', false);
}

// Set Selected Answer (for loading saved responses)
function setSelectedAnswer(response) {
    if (response && response.value !== undefined) {
        $(`input[name="answer"][value="${response.value}"]`).prop('checked', true);
        $('#btnNext').prop('disabled', false);
    }
}

// Save Response to Server
function saveResponse(questionId, value, timeTaken) {
    $.ajax({
        url: '<?= base_url('api/assessment/response') ?>',
        method: 'POST',
        data: {
            attempt_id: testState.attemptId,
            question_id: questionId,
            response_value: value,
            time_taken: timeTaken,
            is_skipped: false
        },
        dataType: 'json',
        success: function(response) {
            if (!response.success) {
                console.error('Failed to save response:', response.message);
            }
        }
    });
}

// Navigation Functions
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
    const questionId = testState.questions[testState.currentIndex].id;
    
    // Mark as skipped
    $.ajax({
        url: '<?= base_url('api/assessment/response') ?>',
        method: 'POST',
        data: {
            attempt_id: testState.attemptId,
            question_id: questionId,
            is_skipped: true
        }
    });
    
    nextQuestion();
}

function updateNavigationButtons() {
    // Previous button
    $('#btnPrevious').prop('disabled', testState.currentIndex === 0);
    
    // Next button visibility
    if (testState.currentIndex === testState.questions.length - 1) {
        $('#btnNext').hide();
        $('#btnFinish').show();
    } else {
        $('#btnNext').show();
        $('#btnFinish').hide();
    }
}

// Finish Test
function finishTest() {
    if (!confirm('Are you sure you want to finish this test?')) {
        return;
    }
    
    showLoading();
    
    $.ajax({
        url: '<?= base_url('api/assessment/complete') ?>',
        method: 'POST',
        data: { attempt_id: testState.attemptId },
        dataType: 'json',
        success: function(response) {
            hideLoading();
            if (response.success) {
                alert('Test completed successfully!');
                
                // Check if all tests are complete
                // If yes, redirect to report
                // If no, redirect to next test or dashboard
                window.location.href = '<?= base_url('dashboard') ?>';
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            hideLoading();
            alert('Failed to complete test. Please try again.');
        }
    });
}

// Timer
let timerInterval;
let seconds = 0;

function startTimer() {
    testState.startTime = Date.now();
    
    timerInterval = setInterval(function() {
        seconds++;
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        $('#timer').text(`${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`);
    }, 1000);
}

// Prevent accidental page leave
window.addEventListener('beforeunload', function (e) {
    e.preventDefault();
    e.returnValue = '';
});
</script>

<style>
.likert-scale {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    margin: 20px 0;
}

.likert-option {
    flex: 1;
    text-align: center;
}

.likert-option input[type="radio"] {
    display: none;
}

.likert-option label {
    display: block;
    padding: 20px 10px;
    background: #f8f9fa;
    border: 3px solid #dee2e6;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s;
    font-weight: 600;
}

.likert-option input[type="radio"]:checked + label {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-color: var(--primary-color);
    transform: scale(1.05);
}

.likert-option label:hover {
    border-color: var(--primary-color);
    background: #e9ecef;
}

#questionCard {
    min-height: 300px;
}

@media (max-width: 768px) {
    .likert-scale {
        flex-direction: column;
    }
}
</style>

<?= $this->include('layouts/footer') ?>