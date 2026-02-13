<?= $this->include('layouts/header') ?>

<?php
// Decode JSON fields
$report['riasec_profile'] = json_decode($report['riasec_profile'] ?? '{}', true);
$report['vark_profile'] = json_decode($report['vark_profile'] ?? '{}', true);
$report['mbti_scores'] = json_decode($report['mbti_scores'] ?? '{}', true);
$report['gardner_profile'] = json_decode($report['gardner_profile'] ?? '{}', true);
$report['eq_breakdown'] = json_decode($report['eq_breakdown'] ?? '{}', true);
$report['aptitude_scores'] = json_decode($report['aptitude_scores'] ?? '{}', true);
$report['strengths'] = json_decode($report['strengths'] ?? '[]', true);
$report['development_areas'] = json_decode($report['development_areas'] ?? '[]', true);
$report['motivators'] = json_decode($report['motivators'] ?? '[]', true);
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
    
    <!-- Executive Summary -->
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
                                <h2 class="text-info fw-bold"><?= $report['iq_estimate'] ?></h2>
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

    <!-- Top Career Matches -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-briefcase"></i> Top Career Matches</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($report['career_matches'])): ?>
                        <?php foreach (array_slice($report['career_matches'], 0, 5) as $index => $match): ?>
                            <div class="mb-3 p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="mb-0">
                                        <span class="badge bg-primary me-2"><?= $index + 1 ?></span>
                                        <?= esc($match['career_title']) ?>
                                    </h5>
                                    <h4 class="mb-0 text-success fw-bold">
                                        <?= round($match['match_percentage'], 1) ?>%
                                    </h4>
                                </div>
                                <div class="progress mb-2" style="height: 8px;">
                                    <div class="progress-bar bg-success" 
                                         style="width: <?= $match['match_percentage'] ?>%"></div>
                                </div>
                                <p class="text-muted mb-0"><strong>Why Suitable:</strong> <?= esc($match['why_suitable']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- RIASEC Chart -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">RIASEC Profile (Holland Code)</h5>
                </div>
                <div class="card-body">
                    <canvas id="riasecChart"></canvas>
                </div>
            </div>
        </div>

        <!-- VARK Chart -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Learning Style (VARK)</h5>
                </div>
                <div class="card-body">
                    <canvas id="varkChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Gardner Chart -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-purple text-white">
                    <h5 class="mb-0">Multiple Intelligences</h5>
                </div>
                <div class="card-body">
                    <canvas id="gardnerChart"></canvas>
                </div>
            </div>
        </div>

        <!-- EQ Chart -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Emotional Intelligence</h5>
                </div>
                <div class="card-body">
                    <canvas id="eqChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Analysis -->
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
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Learning Style Analysis -->
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

// RIASEC Chart
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
    options: {
        scales: {
            r: {
                beginAtZero: true,
                max: 100
            }
        }
    }
});

// VARK Chart
const varkData = <?= json_encode(array_values($report['vark_profile'])) ?>;
const varkLabels = <?= json_encode(array_keys($report['vark_profile'])) ?>;

new Chart(document.getElementById('varkChart'), {
    type: 'pie',
    data: {
        labels: varkLabels,
        datasets: [{
            data: varkData,
            backgroundColor: [
                chartColors.primary,
                chartColors.success,
                chartColors.warning,
                chartColors.danger
            ]
        }]
    }
});

// Gardner Chart
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
        scales: {
            y: {
                beginAtZero: true,
                max: 100
            }
        },
        indexAxis: 'y'
    }
});

// EQ Chart
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
                'rgba(255, 99, 132, 0.7)',
                'rgba(54, 162, 235, 0.7)',
                'rgba(255, 206, 86, 0.7)',
                'rgba(75, 192, 192, 0.7)',
                'rgba(153, 102, 255, 0.7)'
            ]
        }]
    },
    options: {
        scales: {
            r: {
                beginAtZero: true,
                max: 100
            }
        }
    }
});
</script>

<style>
.bg-purple {
    background-color: #9B59B6 !important;
}
canvas {
    max-height: 300px;
}
</style>

<?= $this->include('layouts/footer') ?>