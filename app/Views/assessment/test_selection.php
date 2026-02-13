<?= $this->include('layouts/header') ?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-clipboard-list"></i> Start Assessment</h1>
        <p class="lead mb-0">Choose your age group to begin your psychometric assessment</p>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <!-- Age Group Selection -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user-graduate"></i> Select Your Age Group</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card age-group-card" onclick="selectAgeGroup('class_8_10')">
                                <div class="card-body text-center p-4">
                                    <i class="fas fa-school fa-4x text-primary mb-3"></i>
                                    <h4>Class 8-10</h4>
                                    <p class="text-muted">Ages 13-16 years</p>
                                    <p>Assessments designed for students in Class 8, 9, and 10</p>
                                    <button class="btn btn-primary btn-lg mt-3">
                                        <i class="fas fa-arrow-right"></i> Select This Group
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card age-group-card" onclick="selectAgeGroup('class_11_12')">
                                <div class="card-body text-center p-4">
                                    <i class="fas fa-graduation-cap fa-4x text-success mb-3"></i>
                                    <h4>Class 11-12</h4>
                                    <p class="text-muted">Ages 16-18 years</p>
                                    <p>Assessments designed for students in Class 11 and 12</p>
                                    <button class="btn btn-success btn-lg mt-3">
                                        <i class="fas fa-arrow-right"></i> Select This Group
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assessment Overview -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> What to Expect</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6><i class="fas fa-clock text-info"></i> Total Duration</h6>
                            <p class="mb-0">Approximately 90-120 minutes for all 6 tests</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6><i class="fas fa-question-circle text-info"></i> Total Questions</h6>
                            <p class="mb-0">278 carefully designed questions</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6><i class="fas fa-save text-info"></i> Save Progress</h6>
                            <p class="mb-0">Your responses are saved automatically</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6><i class="fas fa-file-alt text-info"></i> Comprehensive Report</h6>
                            <p class="mb-0">Detailed analysis with career recommendations</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tests Included -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-list-check"></i> Tests Included</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-briefcase fa-2x text-primary"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">RIASEC (Holland Code)</h6>
                                <small class="text-muted">60 questions • 20 minutes • Career interests</small>
                            </div>
                            <span class="badge bg-primary">Required</span>
                        </div>

                        <div class="list-group-item d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-graduation-cap fa-2x text-info"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">VARK Learning Style</h6>
                                <small class="text-muted">16 questions • 10 minutes • How you learn best</small>
                            </div>
                            <span class="badge bg-primary">Required</span>
                        </div>

                        <div class="list-group-item d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-user fa-2x text-purple"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">MBTI Personality Type</h6>
                                <small class="text-muted">70 questions • 25 minutes • Personality assessment</small>
                            </div>
                            <span class="badge bg-primary">Required</span>
                        </div>

                        <div class="list-group-item d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-lightbulb fa-2x text-warning"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Gardner's Multiple Intelligences</h6>
                                <small class="text-muted">32 questions • 15 minutes • Intelligence profile</small>
                            </div>
                            <span class="badge bg-primary">Required</span>
                        </div>

                        <div class="list-group-item d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-heart fa-2x text-danger"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Emotional Intelligence (EQ)</h6>
                                <small class="text-muted">40 questions • 15 minutes • Emotional competencies</small>
                            </div>
                            <span class="badge bg-primary">Required</span>
                        </div>

                        <div class="list-group-item d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-chart-line fa-2x text-success"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Aptitude Assessment</h6>
                                <small class="text-muted">60 questions • 30 minutes • Cognitive abilities</small>
                            </div>
                            <span class="badge bg-primary">Required</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Important Instructions -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Important Instructions</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Find a quiet place where you won't be disturbed</li>
                        <li>Answer honestly - there are no right or wrong answers</li>
                        <li>Don't overthink your responses - go with your first instinct</li>
                        <li>Take breaks between tests if needed</li>
                        <li>Your progress is saved automatically</li>
                        <li>You can resume anytime from where you left off</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
.age-group-card {
    cursor: pointer;
    transition: all 0.3s;
    border: 3px solid transparent;
}

.age-group-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.15);
    border-color: var(--primary-color);
}

.list-group-item {
    border-left: none;
    border-right: none;
}

.list-group-item:first-child {
    border-top: none;
}
</style>

<script>
function selectAgeGroup(ageGroup) {
    // Show confirmation
    if (confirm(`You selected ${ageGroup.replace('_', ' ').toUpperCase()}. Ready to start?`)) {
        showLoading();
        
        // Start assessment session
        $.ajax({
            url: '<?= base_url('api/assessment/start') ?>',
            method: 'POST',
            data: { age_group: ageGroup },
            dataType: 'json',
            success: function(response) {
                hideLoading();
                if (response.success) {
                    // Redirect to first test
                    window.location.href = '<?= base_url('assessment/test/') ?>' + response.data.session_id + '/RIASEC';
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr) {
                hideLoading();
                alert('Failed to start assessment. Please try again.');
            }
        });
    }
}
</script>

<?= $this->include('layouts/footer') ?>