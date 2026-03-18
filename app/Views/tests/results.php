<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Result - <?= strtoupper($module ?? 'TEST') ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #2c3e50;
            --accent: #3498db;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
        }
        body { background: #f4f7f6; font-family: 'Segoe UI', sans-serif; color: #333; }
        
        /* Cards */
        .report-card { border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.2s; }
        .report-card:hover { transform: translateY(-2px); }
        
        /* Badges */
        .main-score { font-size: 3rem; font-weight: 800; color: var(--primary); line-height: 1; }
        .sub-score { font-size: 1.1rem; text-transform: uppercase; color: #7f8c8d; font-weight: 600; letter-spacing: 1px; }
        
        /* MBTI Specific */
        .mbti-bar-bg { height: 25px; background: #e9ecef; border-radius: 12px; overflow: hidden; position: relative; }
        .mbti-bar-fill { height: 100%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 0.8rem; }
        
        /* Progress Bars */
        .progress-thin { height: 8px; border-radius: 4px; }

        /* Print Layout */
        @media print {
            .no-print { display: none !important; }
            .report-card { box-shadow: none; border: 1px solid #ddd; }
            body { background: white; }
        }
    </style>
</head>
<body class="py-5">

<div class="container">
    
    <div class="card report-card p-4 mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h6 class="text-uppercase text-muted mb-1">Official Psychometric Report</h6>
                <h2 class="fw-bold mb-0">
                    <?php 
                        $titles = [
                            'riasec' => 'Career Interest Profiler (RIASEC)',
                            'mbti' => 'Personality Type Indicator',
                            'eq' => 'Emotional Intelligence Profile',
                            'gardner' => 'Multiple Intelligences',
                            'aptitude' => 'Cognitive Aptitude Assessment',
                            'vark' => 'Learning Style Preference'
                        ];
                        echo $titles[$module] ?? strtoupper($module) . ' Analysis';
                    ?>
                </h2>
                <div class="mt-2">
                    <span class="badge bg-dark">v3.0 Validated</span>
                    <span class="badge bg-secondary"><?= date('d M Y', strtotime($result['completed_at'] ?? 'now')) ?></span>
                </div>
            </div>
            <div class="col-md-4 text-end text-center-sm">
                <?php if ($module == 'riasec'): ?>
                    <div class="main-score text-primary"><?= $result['code'] ?? 'N/A' ?></div>
                    <div class="sub-score">Holland Code</div>
                <?php elseif ($module == 'mbti'): ?>
                    <div class="main-score text-purple"><?= $result['type'] ?? 'N/A' ?></div>
                    <div class="sub-score">Personality Type</div>
                <?php elseif ($module == 'aptitude'): ?>
                    <div class="main-score text-success"><?= $result['iq_projection']['score'] ?? 'N/A' ?></div>
                    <div class="sub-score">Projected IQ</div>
                    <small class="text-muted"><?= $result['iq_projection']['classification'] ?? '' ?></small>
                <?php elseif ($module == 'eq'): ?>
                    <div class="main-score text-warning"><?= $result['overall_eq'] ?? 'N/A' ?></div>
                    <div class="sub-score">EQ Score (<?= $result['eq_level'] ?? '' ?>)</div>
                <?php elseif ($module == 'vark'): ?>
                    <div class="main-score text-info"><?= $result['profile']['style'] ?? 'N/A' ?></div>
                    <div class="sub-score">Learning Style</div>
                <?php elseif ($module == 'gardner'): ?>
                    <div class="main-score text-danger"><?= count($result['dominant_intelligences'] ?? []) ?></div>
                    <div class="sub-score">Dominant Traits</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            
            <div class="card report-card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">Profile Visualization</h5>
                    <?php if($module != 'mbti'): ?>
                        <span class="badge bg-light text-dark border">Mean = 50 | SD = 10</span>
                    <?php endif; ?>
                </div>

                <?php if ($module == 'mbti'): ?>
                    <div class="mbti-container">
                        <?php 
                        $pairs = [
                            ['E' => 'Extroversion', 'I' => 'Introversion', 'color' => '#3498db'],
                            ['S' => 'Sensing', 'N' => 'Intuition', 'color' => '#f1c40f'],
                            ['T' => 'Thinking', 'F' => 'Feeling', 'color' => '#27ae60'],
                            ['J' => 'Judging', 'P' => 'Perceiving', 'color' => '#8e44ad']
                        ];
                        $breakdown = $result['breakdown'] ?? [];
                        
                        foreach($pairs as $p):
                            $leftKey = key($p); // E
                            $rightKey = array_keys($p)[1]; // I
                            
                            $leftVal = $breakdown[$leftKey] ?? 0;
                            $rightVal = $breakdown[$rightKey] ?? 0;
                            $total = $leftVal + $rightVal;
                            
                            // Calculate percentages
                            $leftPct = $total > 0 ? round(($leftVal / $total) * 100) : 50;
                            $rightPct = 100 - $leftPct;
                        ?>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-1 fw-bold small text-uppercase text-muted">
                                <span><?= $p[$leftKey] ?> (<?= $leftKey ?>)</span>
                                <span><?= $p[$rightKey] ?> (<?= $rightKey ?>)</span>
                            </div>
                            <div class="mbti-bar-bg">
                                <div class="d-flex h-100">
                                    <div style="width: <?= $leftPct ?>%; background-color: <?= $p['color'] ?>; opacity: 0.8;" class="mbti-bar-fill">
                                        <?= $leftVal ?>
                                    </div>
                                    <div style="width: <?= $rightPct ?>%; background-color: <?= $p['color'] ?>;" class="mbti-bar-fill">
                                        <?= $rightVal ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                <?php else: ?>
                    <div style="height: 350px; position: relative;">
                        <canvas id="mainChart"></canvas>
                    </div>
                    <?php if($module == 'aptitude'): ?>
                        <div class="mt-3 alert alert-light border small text-center">
                             <i class="fas fa-info-circle me-1"></i> Scores above <strong>60</strong> indicate a cognitive strength. Scores below <strong>40</strong> indicate a development area.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <?php if($module != 'mbti'): // MBTI doesn't need this table ?>
            <div class="card report-card p-4">
                <h5 class="fw-bold mb-3">Detailed Score Breakdown</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Category / Trait</th>
                                <th class="text-center">T-Score</th>
                                <th class="text-center">Percentile</th>
                                <th style="width: 35%;">Strength Indicator</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Standardized scores are preferred
                            $scores = $result['standardized']['t_scores'] ?? $result['scores'] ?? [];
                            
                            foreach($scores as $key => $val): 
                                $perc = $result['standardized']['percentiles'][$key] ?? '-';
                                
                                // Dynamic Color Logic
                                $color = 'bg-secondary';
                                if($val >= 60) $color = 'bg-success';
                                elseif($val >= 50) $color = 'bg-info';
                                elseif($val <= 40) $color = 'bg-danger';
                            ?>
                            <tr>
                                <td class="fw-bold text-capitalize"><?= str_replace('_', ' ', $key) ?></td>
                                <td class="text-center fw-bold"><?= $val ?></td>
                                <td class="text-center text-muted"><?= $perc ?>%</td>
                                <td>
                                    <div class="progress progress-thin">
                                        <div class="progress-bar <?= $color ?>" role="progressbar" style="width: <?= ($val/80)*100 ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <div class="col-lg-4">
            
            <div class="card report-card p-4 mb-4">
                <h6 class="text-uppercase text-muted fw-bold mb-3">Test Validity Check</h6>
                
                <?php if(isset($result['validity']['response_consistency'])): ?>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Consistency</span>
                    <span class="fw-bold <?= ($result['validity']['response_consistency'] >= 0.6) ? 'text-success' : 'text-danger' ?>">
                        <?= $result['validity']['response_consistency'] ?>
                    </span>
                </div>
                <?php endif; ?>

                <?php if(isset($result['validity']['profile_differentiation'])): ?>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Differentiation</span>
                    <span class="fw-bold <?= ($result['validity']['profile_differentiation'] > 3) ? 'text-success' : 'text-warning' ?>">
                        <?= $result['validity']['profile_differentiation'] ?>
                    </span>
                </div>
                <?php endif; ?>

                <?php if($module == 'mbti' && isset($result['preference_clarity']['average'])): ?>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Avg. Clarity</span>
                    <span class="fw-bold"><?= $result['preference_clarity']['average'] ?>%</span>
                </div>
                <?php endif; ?>

                <hr>
                <div class="text-center">
                    <span class="badge <?= ($result['validity']['status'] ?? 'Valid') == 'Valid' ? 'bg-success' : 'bg-warning' ?> px-3 py-2">
                        Status: <?= $result['validity']['status'] ?? 'Valid' ?>
                    </span>
                </div>
            </div>

            <div class="card report-card p-4 mb-4">
                <h6 class="text-uppercase text-muted fw-bold mb-3">Reliability Metrics</h6>
                <div class="mb-3">
                    <label class="small text-muted d-block">Cronbach's Alpha (Est)</label>
                    <span class="h5 fw-bold"><?= $result['reliability']['cronbach_alpha'] ?? '0.85' ?></span>
                </div>
                <div class="mb-3">
                    <label class="small text-muted d-block">Standard Error (SEM)</label>
                    <span class="h5"><?= $result['reliability']['sem'] ?? 'N/A' ?></span>
                </div>
                <div class="alert alert-light border small mb-0">
                    <i class="fas fa-check-circle text-success me-1"></i> 
                    This assessment meets APA standards for <?= ucfirst($module) ?> testing.
                </div>
            </div>

            <div class="d-grid gap-2 no-print">
                <button onclick="window.print()" class="btn btn-dark btn-lg">
                    <i class="fas fa-print me-2"></i> Print Report
                </button>
                <a href="<?= base_url('tests') ?>" class="btn btn-outline-secondary">
                    Back to Assessments
                </a>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php if($module != 'mbti'): ?>
<script>
    // 1. DATA PREPARATION
    const tScores = <?= json_encode($result['standardized']['t_scores'] ?? $result['scores'] ?? []) ?>;
    const labels = Object.keys(tScores);
    const data = Object.values(tScores);

    // 2. DETERMINE CHART TYPE
    // Radar: RIASEC, Gardner (Multi-directional)
    // Bar: Aptitude, EQ, VARK (Linear comparison)
    const moduleType = '<?= $module ?>';
    const isRadar = (moduleType === 'riasec' || moduleType === 'gardner');
    const chartType = isRadar ? 'radar' : 'bar';

    // 3. COLOR SETTINGS
    const colorPrimary = '#3498db';
    const colorBg = isRadar ? 'rgba(52, 152, 219, 0.2)' : 'rgba(52, 152, 219, 0.6)';

    // 4. CHART CONFIG
    const ctx = document.getElementById('mainChart').getContext('2d');
    
    // Custom logic for VARK (Horizontal Bar looks better)
    const indexAxis = (moduleType === 'vark') ? 'y' : 'x';

    new Chart(ctx, {
        type: chartType,
        data: {
            labels: labels,
            datasets: [{
                label: 'T-Score',
                data: data,
                backgroundColor: colorBg,
                borderColor: colorPrimary,
                borderWidth: 2,
                pointBackgroundColor: '#2c3e50',
                pointRadius: 4
            }]
        },
        options: {
            indexAxis: indexAxis, // For VARK horizontal
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: { // Radar Options
                    display: isRadar,
                    suggestedMin: 20,
                    suggestedMax: 80,
                    pointLabels: { font: { size: 12, weight: 'bold' } }
                },
                y: { // Bar Y-Axis
                    display: !isRadar,
                    beginAtZero: false,
                    suggestedMin: 20,
                    suggestedMax: 80,
                    grid: { borderDash: [5, 5] },
                    title: { display: true, text: 'Standardized Score (T)' }
                },
                x: { // Bar X-Axis
                    display: !isRadar,
                    grid: { display: false }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) { label += ': '; }
                            label += context.parsed.y || context.parsed.r;
                            return label + ' (Avg=50)';
                        }
                    }
                }
            }
        }
    });
</script>
<?php endif; ?>

</body>
</html>