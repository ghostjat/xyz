<!DOCTYPE html>
<html lang="en">
<head>
    <title>Professional Career Dossier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; color: #333; font-family: 'Segoe UI', sans-serif; }
        .page-container { max-width: 1000px; margin: 40px auto; background: white; box-shadow: 0 0 20px rgba(0,0,0,0.05); }
        .header-strip { background: #2c3e50; color: white; padding: 40px; }
        .section-title { border-bottom: 2px solid #2c3e50; padding-bottom: 10px; margin-bottom: 20px; font-weight: 700; color: #2c3e50; }
        .score-box { background: #f8f9fa; border-left: 5px solid #2c3e50; padding: 15px; margin-bottom: 15px; }
        .career-card { border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; margin-bottom: 15px; transition: 0.3s; }
        .career-card:hover { border-color: #3498db; background: #f0f8ff; }
        .match-badge { font-size: 0.9rem; font-weight: bold; padding: 5px 10px; border-radius: 4px; }
        .match-high { background: #d4edda; color: #155724; }
        .match-med { background: #fff3cd; color: #856404; }
        @media print {
            .no-print { display: none; }
            body { background: white; }
            .page-container { box-shadow: none; margin: 0; max-width: 100%; }
        }
    </style>
</head>
<body>

<div class="page-container">
    
    <div class="header-strip d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 fw-bold mb-1">CAREER MAPPING DOSSIER</h1>
            <p class="mb-0 opacity-75">Integrated Psychometric Analysis v2.0</p>
        </div>
        <div class="text-end">
            <div class="h5">CANDIDATE ID: <?= session()->get('user_id') ?></div>
            <div class="small opacity-75"><?= date('F d, Y') ?></div>
        </div>
    </div>

    <div class="p-5">

        <?php if(!empty($data['missing'])): ?>
            <div class="alert alert-danger mb-5">
                <h5 class="fw-bold"><i class="bi bi-exclamation-octagon"></i> Analysis Incomplete</h5>
                <p>The Deep Career Engine requires 100% data density. Please complete: 
                    <strong><?= implode(', ', $data['missing']) ?></strong>
                </p>
                <div class="no-print"><a href="<?= base_url('dashboard') ?>" class="btn btn-sm btn-danger">Complete Now</a></div>
            </div>
        <?php else: ?>

            <div class="row mb-5">
                <h4 class="section-title">I. PSYCHOMETRIC PROFILE</h4>
                
                <div class="col-md-6 mb-4">
                    <div class="score-box border-primary">
                        <div class="d-flex justify-content-between">
                            <h6 class="text-primary fw-bold">INTERESTS (RIASEC)</h6>
                            <span class="badge bg-primary"><?= $data['riasec']['trait'] ?></span>
                        </div>
                        <div class="small mt-2">
                            Dominant: <strong><?= $data['riasec']['result']['dominant_field'] ?></strong><br>
                            Profile Code: <?= $data['riasec']['trait'] ?> (Differentiation: High)
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="score-box border-success">
                        <div class="d-flex justify-content-between">
                            <h6 class="text-success fw-bold">PERSONALITY (MBTI)</h6>
                            <span class="badge bg-success"><?= $data['mbti']['trait'] ?></span>
                        </div>
                        <div class="small mt-2">
                            Type: <strong><?= $data['mbti']['trait'] ?></strong><br>
                            Role: <?= strpos($data['mbti']['trait'], 'N') ? 'Visionary/Strategist' : 'Realist/Tactician' ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Emotional Intelligence:</strong><br>
                            <span class="text-muted"><?= $data['eq']['trait'] ?> (Score: <?= $data['eq']['result']['score'] ?>)</span>
                        </div>
                        <div class="col-md-4">
                            <strong>Top Intelligence:</strong><br>
                            <span class="text-muted"><?= $data['gardner']['trait'] ?></span>
                        </div>
                        <div class="col-md-4">
                            <strong>Work Readiness:</strong><br>
                            <span class="text-muted"><?= $data['aptitude']['trait'] ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-5">
                <h4 class="section-title">II. AI CAREER RECOMMENDATIONS</h4>
                <p class="text-muted mb-4">
                    Recommendations are generated using a 4-layer filtering matrix combining your Interests (<?= $data['riasec']['trait'] ?>), Personality (<?= $data['mbti']['trait'] ?>), and Aptitudes.
                </p>

                <div class="row">
                    <?php foreach($careers as $job): ?>
                    <div class="col-md-6">
                        <div class="career-card">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="fw-bold text-dark mb-0"><?= $job['role'] ?></h5>
                                <span class="match-badge <?= $job['match'] > 90 ? 'match-high' : 'match-med' ?>">
                                    <?= $job['match'] ?>% Match
                                </span>
                            </div>
                            <p class="text-secondary small mb-0">
                                <i class="bi bi-diagram-3"></i> <strong>Why:</strong> <?= $job['why'] ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        <?php endif; ?>

        <div class="text-center mt-5 pt-4 border-top no-print">
            <button onclick="window.print()" class="btn btn-dark px-5">Print Official Dossier</button>
            <br><br>
            <a href="<?= base_url('dashboard') ?>" class="text-muted">Return to Dashboard</a>
        </div>

    </div>
</div>

</body>
</html>