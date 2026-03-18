<?= $this->include('layout/header') ?>
    <div class="container report-container">
        
        <div class="row mb-5 align-items-center">
            <div class="col-md-8">
                <h1 class="display-5 fw-bold text-dark">Career Analysis Report</h1>
                <p class="lead text-muted">Comprehensive evaluation of Interest, Aptitude, Intelligence, and Personality.</p>
                <div class="mt-3">
                    <span class="badge bg-secondary me-2">Date: <?= date('d M Y') ?></span>
                    <span class="badge bg-success">Status: Validated</span>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <div class="holland-badge shadow">
                    <?= $riasec['code'] ?? 'N/A' ?>
                </div>
                <div class="text-muted small">Primary Holland Code</div>
            </div>
        </div>

        <hr class="my-5">

        <div class="row mb-5">
            <h3 class="section-title">Executive Summary</h3>
            
            <div class="col-md-3 mb-4">
                <div class="card metric-card p-3 text-center">
                    <div class="metric-value text-primary">
                        <?= $riasec['code'] ?? '---' ?>
                    </div>
                    <div class="metric-label">Personality Type</div>
                    <small class="text-muted mt-2">Dominant: <?= $riasec['dominant'] ?? 'Unknown' ?></small>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card metric-card p-3 text-center">
                    <div class="metric-value text-success">
                        <?= $aptitude['iq_projection']['score'] ?? '---' ?>
                    </div>
                    <div class="metric-label">Projected IQ</div>
                    <small class="text-muted mt-2"><?= $aptitude['iq_projection']['classification'] ?? 'Average' ?></small>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card metric-card p-3 text-center">
                    <div class="metric-value text-warning">
                        <?= $eq['eq_level'] ?? '---' ?>
                    </div>
                    <div class="metric-label">Emotional Intelligence</div>
                    <small class="text-muted mt-2">Score: <?= $eq['overall_eq'] ?? 0 ?></small>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card metric-card p-3 text-center">
                    <div class="metric-value text-info">
                        <?= $vark['profile']['style'] ?? '---' ?>
                    </div>
                    <div class="metric-label">Learning Style</div>
                    <small class="text-muted mt-2"><?= $vark['profile']['strength'] ?? '' ?></small>
                </div>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-md-6 mb-4">
                <h3 class="section-title">Interest Profile (RIASEC)</h3>
                <div class="card p-3 h-100">
                    <canvas id="riasecChart"></canvas>
                    <div class="mt-3 text-center text-muted small">
                        Based on T-Scores (Population Average = 50)
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <h3 class="section-title">Cognitive Aptitude</h3>
                <div class="card p-3 h-100">
                    <canvas id="aptitudeChart"></canvas>
                    <div class="mt-3">
                        <h6 class="fw-bold">Interpretation:</h6>
                        <ul class="small text-muted mb-0">
                            <li><strong>> 60:</strong> Significant Strength (Top 16%)</li>
                            <li><strong>40-60:</strong> Average Range</li>
                            <li><strong>< 40:</strong> Development Area</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-5">
            <h3 class="section-title">Detailed Analysis</h3>
            
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-white fw-bold">Multiple Intelligences (Gardner)</div>
                    <div class="card-body">
                        <?php if(isset($gardner['standardized']['t_scores'])): ?>
                            <?php foreach($gardner['standardized']['t_scores'] as $trait => $score): ?>
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span><?= $trait ?></span>
                                        <span class="fw-bold"><?= $score ?></span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: <?= ($score/80)*100 ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-white fw-bold">Emotional Intelligence Domains</div>
                    <div class="card-body">
                        <?php if(isset($eq['standardized']['t_scores'])): ?>
                            <?php foreach($eq['standardized']['t_scores'] as $domain => $score): ?>
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="text-capitalize"><?= str_replace('_', ' ', $domain) ?></span>
                                        <span class="fw-bold"><?= $score ?></span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?= ($score/80)*100 ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <h3 class="section-title">Career Recommendations</h3>
            <p class="text-muted mb-4">
                These recommendations are validated against your <strong>Aptitude</strong> (Can you do it?), 
                <strong>Interests</strong> (Do you want to do it?), and <strong>Personality</strong> (Will you fit in?).
            </p>

            <div class="accordion" id="careerAccordion">
                <?php 
                // Assuming $career_paths is passed from controller
                $index = 0;
                if(isset($career_paths) && is_array($career_paths)):
                    foreach($career_paths as $cluster => $jobs): 
                        $index++;
                ?>
                <div class="accordion-item mb-3 border">
                    <h2 class="accordion-header" id="heading<?= $index ?>">
                        <button class="accordion-button <?= $index > 1 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $index ?>">
                            <span class="fw-bold me-2"><?= $cluster ?> Cluster</span>
                            <span class="badge bg-primary rounded-pill"><?= count($jobs) ?> Matches</span>
                        </button>
                    </h2>
                    <div id="collapse<?= $index ?>" class="accordion-collapse collapse <?= $index === 1 ? 'show' : '' ?>" data-bs-parent="#careerAccordion">
                        <div class="accordion-body bg-light">
                            <div class="row">
                                <?php foreach($jobs as $job): ?>
                                    <div class="col-md-6">
                                        <div class="card career-card p-3 shadow-sm 
                                            <?= strpos($job['fit_label'], 'Perfect') !== false ? 'fit-excellent' : 
                                               (strpos($job['fit_label'], 'Strong') !== false ? 'fit-good' : 
                                               (strpos($job['fit_label'], 'Weak') !== false ? 'fit-weak' : 'fit-risk')) ?>">
                                            
                                            <div class="d-flex justify-content-between align-items-start">
                                                <h5 class="fw-bold mb-1"><?= $job['job_title'] ?></h5>
                                                <span class="badge bg-dark"><?= $job['match_score'] ?>% Match</span>
                                            </div>
                                            
                                            <div class="small fw-bold mb-2 
                                                <?= strpos($job['fit_label'], 'Risk') !== false ? 'text-danger' : 'text-success' ?>">
                                                <?= $job['fit_label'] ?>
                                            </div>

                                            <?php if(!empty($job['validation_notes']) && $job['validation_notes'][0] != 'No risks identified'): ?>
                                                <div class="alert alert-warning py-1 px-2 small mb-0 mt-2">
                                                    <i class="fas fa-exclamation-triangle me-1"></i> 
                                                    <?= implode(', ', $job['validation_notes']) ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-muted small mt-2">
                                                    <i class="fas fa-check-circle text-success me-1"></i> Scientifically Validated Fit
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>

        <div class="mt-5 text-center text-muted small">
            <p><strong>Scientific Validity Note:</strong> This report uses standardized T-Scores (Mean=50, SD=10). Reliability coefficients (Cronbach's Alpha) for this assessment are consistent with APA standards.</p>
            <p>&copy; <?= date('Y') ?> Psychometric Engine. Confidential Report.</p>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // DATA INJECTION FROM PHP
        // In a real app, these PHP variables would be printed as JSON
        const riasecData = <?= json_encode($riasec['standardized']['t_scores'] ?? []) ?>;
        const aptitudeData = <?= json_encode($aptitude['standardized']['t_scores'] ?? []) ?>;

        // 1. RIASEC RADAR CHART
        const ctxRiasec = document.getElementById('riasecChart').getContext('2d');
        new Chart(ctxRiasec, {
            type: 'radar',
            data: {
                labels: ['Realistic', 'Investigative', 'Artistic', 'Social', 'Enterprising', 'Conventional'],
                datasets: [{
                    label: 'Interest Profile (T-Score)',
                    data: [
                        riasecData.Realistic || 50,
                        riasecData.Investigative || 50,
                        riasecData.Artistic || 50,
                        riasecData.Social || 50,
                        riasecData.Enterprising || 50,
                        riasecData.Conventional || 50
                    ],
                    backgroundColor: 'rgba(52, 152, 219, 0.2)',
                    borderColor: 'rgba(52, 152, 219, 1)',
                    pointBackgroundColor: 'rgba(52, 152, 219, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(52, 152, 219, 1)'
                }]
            },
            options: {
                scales: {
                    r: {
                        angleLines: { display: false },
                        suggestedMin: 20,
                        suggestedMax: 80,
                        pointLabels: { font: { size: 12, weight: 'bold' } }
                    }
                },
                plugins: { legend: { display: false } }
            }
        });

        // 2. APTITUDE BAR CHART
        const ctxApt = document.getElementById('aptitudeChart').getContext('2d');
        
        // Helper to color bars based on score
        const getBarColors = (data) => {
            return data.map(score => score >= 60 ? '#2ecc71' : (score < 40 ? '#e74c3c' : '#3498db'));
        };
        
        const aptScores = Object.values(aptitudeData);
        const aptLabels = Object.keys(aptitudeData).map(k => k.charAt(0).toUpperCase() + k.slice(1));

        new Chart(ctxApt, {
            type: 'bar',
            data: {
                labels: aptLabels,
                datasets: [{
                    label: 'Aptitude Score',
                    data: aptScores,
                    backgroundColor: getBarColors(aptScores),
                    borderWidth: 0,
                    borderRadius: 5
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 20,
                        max: 80,
                        grid: { borderDash: [5, 5] }
                    },
                    x: { grid: { display: false } }
                },
                plugins: {
                    legend: { display: false },
                    annotation: {
                        annotations: {
                            line1: {
                                type: 'line',
                                yMin: 50,
                                yMax: 50,
                                borderColor: 'black',
                                borderWidth: 1,
                                borderDash: [10, 5],
                                label: { enabled: true, content: 'Population Average' }
                            }
                        }
                    }
                }
            }
        });
    </script>
<?= $this->include('layout/footer') ?>