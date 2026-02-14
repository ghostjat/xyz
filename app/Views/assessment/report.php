<?= $this->include('layouts/header') ?>

<?php
/**
 * Data Normalization Block
 * Fixes structure mismatch between Engine (nested) and View (flat)
 */
// 1. Flatten the 'profile' array into the main report array if it exists
if (isset($report['profile']) && is_array($report['profile'])) {
    $report = array_merge($report, $report['profile']);
}

// 2. Ensure array fields are actually arrays (handle strings if they were json_encoded)
$arrayFields = [
    'riasec_profile', 'vark_profile', 'mbti_scores', 'gardner_profile', 
    'eq_breakdown', 'aptitude_scores', 'strengths', 'development_areas', 'motivators'
];

foreach ($arrayFields as $field) {
    if (isset($report[$field]) && is_string($report[$field])) {
        $report[$field] = json_decode($report[$field], true);
    }
    // Ensure it's an array to prevent array_keys/array_values errors later
    if (!isset($report[$field]) || !is_array($report[$field])) {
        $report[$field] = [];
    }
}

// 3. Set defaults for missing scalars
$report['mbti_type'] = $report['mbti_type'] ?? 'N/A';
$report['eq_score'] = $report['eq_score'] ?? 0;
$report['iq_estimate'] = $report['iq_estimate'] ?? 'N/A';
$report['learning_style_analysis'] = $report['learning_style_analysis'] ?? 'Analysis pending...';
$report['report_code'] = $report['report_code'] ?? '---';
// Fake confidence score if missing (average of available reliability scores or static)
$report['confidence_score'] = $report['confidence_score'] ?? 85; 
?>

<div class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1><i class="fas fa-file-alt"></i> Career Analysis Report</h1>
                <p class="lead mb-0">Report Code: <code><?= esc($report['report_code']) ?></code></p>
            </div>
            <div class="col-md-4 text-end">
                <a href="<?= base_url('report/download/' . $report['report_code']) ?>" 
                   class="btn btn-light btn-lg">
                    <i class="fas fa-download"></i> Download PDF
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-chart-pie"></i> Executive Summary</h4>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h6 class="text-muted">Personality Type</h6>
                                <h2 class="text-primary fw-bold"><?= esc($report['mbti_type']) ?></h2>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h6 class="text-muted">Emotional Intelligence</h6>
                                <h2 class="text-success fw-bold"><?= round($report['eq_score'], 1) ?>%</h2>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h6 class="text-muted">IQ Estimate</h6>
                                <h2 class="text-info fw-bold"><?= esc($report['iq_estimate']) ?></h2>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h6 class="text-muted">Report Confidence</h6>
                                <h2 class="text-warning fw-bold"><?= round($report['confidence_score'], 1) ?>%</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-briefcase"></i> Top Career Matches</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($report['career_matches']) && is_array($report['career_matches'])): ?>
                        <?php foreach (array_slice($report['career_matches'], 0, 5) as $index => $match): ?>
                            <div class="mb-3 p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="mb-0">
                                        <span class="badge bg-primary me-2"><?= $index + 1 ?></span>
                                        <?= esc($match['career_title'] ?? 'Unknown Career') ?>
                                    </h5>
                                    <h4 class="mb-0 text-success fw-bold">
                                        <?= round($match['match_percentage'] ?? 0, 1) ?>%
                                    </h4>
                                </div>
                                <div class="progress mb-2" style="height: 8px;">
                                    <div class="progress-bar bg-success" 
                                         style="width: <?= $match['match_percentage'] ?? 0 ?>%"></div>
                                </div>
                                <p class="text-muted mb-0"><strong>Why Suitable:</strong> <?= esc($match['why_suitable'] ?? '') ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-info">No career matches found based on current data.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">RIASEC Profile (Holland Code)</h5>
                </div>
                <div class="card-body">
                    <?php if(!empty($report['riasec_profile'])): ?>
                        <canvas id="riasecChart"></canvas>
                    <?php else: ?>
                        <p class="text-center text-muted py-5">No RIASEC data available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Learning Style (VARK)</h5>
                </div>
                <div class="card-body">
                    <?php if(!empty($report['vark_profile'])): ?>
                        <canvas id="varkChart"></canvas>
                    <?php else: ?>
                        <p class="text-center text-muted py-5">No VARK data available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-purple text-white">
                    <h5 class="mb-0">Multiple Intelligences</h5>
                </div>
                <div class="card-body">
                     <?php if(!empty($report['gardner_profile'])): ?>
                        <canvas id="gardnerChart"></canvas>
                     <?php else: ?>
                        <p class="text-center text-muted py-5">No Intelligence data available</p>
                     <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Emotional Intelligence</h5>
                </div>
                <div class="card-body">
                     <?php if(!empty($report['eq_breakdown'])): ?>
                        <canvas id="eqChart"></canvas>
                     <?php else: ?>
                        <p class="text-center text-muted py-5">No EQ data available</p>
                     <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-star"></i> Your Strengths</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <?php foreach ($report['strengths'] as $strength): ?>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success"></i> 
                                <?= esc($strength) ?>
                            </li>
                        <?php endforeach; ?>
                        <?php if(empty($report['strengths'])): ?>
                            <li class="text-muted">No specific strengths identified yet.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-arrow-up"></i> Development Areas</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <?php foreach ($report['development_areas'] as $area): ?>
                            <li class="mb-2">
                                <i class="fas fa-arrow-circle-up text-warning"></i> 
                                <?= esc($area) ?>
                            </li>
                        <?php endforeach; ?>
                        <?php if(empty($report['development_areas'])): ?>
                            <li class="text-muted">No development areas identified yet.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-bolt"></i> Key Motivators</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <?php foreach ($report['motivators'] as $motivator): ?>
                            <li class="mb-2">
                                <i class="fas fa-star text-info"></i> 
                                <?= esc($motivator) ?>
                            </li>
                        <?php endforeach; ?>
                        <?php if(empty($report['motivators'])): ?>
                            <li class="text-muted">No motivators identified yet.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-graduation-cap"></i> Learning Style Analysis</h5>
                </div>
                <div class="card-body">
                    <p><?= nl2br(esc($report['learning_style_analysis'])) ?></p>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
// Chart Configuration
const chartColors = {
    primary: '#4A90E2',
    success: '#50C878',
    info: '#5BC0DE',
    warning: '#F0AD4E',
    danger: '#D9534F',
    purple: '#9B59B6'
};

// Check if elements exist before creating charts to avoid JS errors
document.addEventListener('DOMContentLoaded', function() {
    
    // RIASEC Chart
    if (document.getElementById('riasecChart')) {
        const riasecData = <?= json_encode(array_values($report['riasec_profile'])) ?>;
        const riasecLabels = <?= json_encode(array_keys($report['riasec_profile'])) ?>;

        new Chart(document.getElementById('riasecChart'), {
            type: 'radar',
            data: {
                labels: riasecLabels,
                datasets: [{
                    label: 'Your Profile',
                    data: riasecData,
                    backgroundColor: 'rgba(74, 144, 226, 0.2)',
                    borderColor: chartColors.primary,
                    pointBackgroundColor: chartColors.primary,
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: chartColors.primary
                }]
            },
            options: { scales: { r: { beginAtZero: true, max: 100 } } }
        });
    }

    // VARK Chart
    if (document.getElementById('varkChart')) {
        const varkData = <?= json_encode(array_values($report['vark_profile'])) ?>;
        const varkLabels = <?= json_encode(array_keys($report['vark_profile'])) ?>;

        new Chart(document.getElementById('varkChart'), {
            type: 'pie',
            data: {
                labels: varkLabels,
                datasets: [{
                    data: varkData,
                    backgroundColor: [chartColors.primary, chartColors.success, chartColors.warning, chartColors.danger]
                }]
            }
        });
    }

    // Gardner Chart
    if (document.getElementById('gardnerChart')) {
        const gardnerData = <?= json_encode(array_values($report['gardner_profile'])) ?>;
        const gardnerLabels = <?= json_encode(array_keys($report['gardner_profile'])) ?>;

        new Chart(document.getElementById('gardnerChart'), {
            type: 'bar',
            data: {
                labels: gardnerLabels,
                datasets: [{
                    label: 'Intelligence Score',
                    data: gardnerData,
                    backgroundColor: chartColors.purple
                }]
            },
            options: { 
                scales: { y: { beginAtZero: true, max: 100 } },
                indexAxis: 'y'
            }
        });
    }

    // EQ Chart
    if (document.getElementById('eqChart')) {
        const eqData = <?= json_encode(array_values($report['eq_breakdown'])) ?>;
        const eqLabels = <?= json_encode(array_map(function($k) {
            return ucwords(str_replace('_', ' ', $k));
        }, array_keys($report['eq_breakdown']))) ?>;

        new Chart(document.getElementById('eqChart'), {
            type: 'polarArea',
            data: {
                labels: eqLabels,
                datasets: [{
                    data: eqData,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)', 'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)', 'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)'
                    ]
                }]
            },
            options: { scales: { r: { beginAtZero: true, max: 100 } } }
        });
    }
});
</script>

<style>
.bg-purple { background-color: #9B59B6 !important; }
canvas { max-height: 300px; }
</style>

<?= $this->include('layouts/footer') ?>