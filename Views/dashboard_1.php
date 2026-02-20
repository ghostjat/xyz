<div class="container mt-5">
    <div class="row">
        <div class="col-md-3">
            <div class="list-group">
                <div class="list-group-item bg-primary text-white">Menu</div>
                <a href="#" class="list-group-item">Profile</a>
                <a href="#" class="list-group-item">Settings</a>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card p-4">
                <h3>Welcome, <?= $name ?></h3>
                <span class="badge bg-info text-dark w-25"><?= $role ?> Account</span>
                <hr>
                <p>Manage your appointments, profile, and messages here.</p>
                
                <?php if($role == 'Student'): ?>
                    <button class="btn btn-outline-primary">Take Career Test</button>
                <?php elseif($role == 'Counselor'): ?>
                    <button class="btn btn-outline-success">View Appointments</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    
    <div class="row align-items-center mb-5">
        <div class="col-lg-6">
            <h2 class="fw-bold text-primary mb-1">My Assessment Dashboard</h2>
            <p class="text-muted">Track your progress and access your career insights.</p>
        </div>
        <div class="col-lg-6 text-lg-end">
            <a href="<?= base_url('report/consolidated') ?>" class="btn btn-warning text-dark fw-bold shadow-sm px-4 py-2 me-2">
                <i class="bi bi-star-fill"></i> View Career Dossier
            </a>
        </div>
    </div>

    <div class="card bg-white border-0 shadow-sm mb-5">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <span class="text-uppercase text-muted small fw-bold letter-spacing-1">Overall Progress</span>
                    <?php 
                        $total = count($modules);
                        $done = count(array_filter($modules, fn($m) => $m['status'] === 'completed'));
                        $pct = ($total > 0) ? ($done / $total) * 100 : 0;
                    ?>
                    <div class="h3 fw-bold mb-0 text-dark"><?= $done ?> / <?= $total ?> Tests</div>
                </div>
                <div class="col-md-9">
                    <div class="progress" style="height: 10px; background-color: #e9ecef; border-radius: 5px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $pct ?>%; transition: width 1s ease;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <?php foreach($modules as $mod): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 hover-card">
                <div class="card-body p-4">
                    
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div class="icon-box bg-<?= $mod['status']=='completed' ? 'success' : 'primary' ?> bg-opacity-10 text-<?= $mod['status']=='completed' ? 'success' : 'primary' ?>">
                            <?php if($mod['code'] == 'riasec'): ?><i class="bi bi-briefcase fs-4"></i>
                            <?php elseif($mod['code'] == 'mbti'): ?><i class="bi bi-person-badge fs-4"></i>
                            <?php elseif($mod['code'] == 'eq'): ?><i class="bi bi-heart fs-4"></i>
                            <?php elseif($mod['code'] == 'gardner'): ?><i class="bi bi-lightbulb fs-4"></i>
                            <?php else: ?><i class="bi bi-graph-up fs-4"></i>
                            <?php endif; ?>
                        </div>
                        
                        <?php if($mod['status'] === 'completed'): ?>
                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Completed</span>
                        <?php else: ?>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill">Pending</span>
                        <?php endif; ?>
                    </div>

                    <h5 class="card-title fw-bold mb-2"><?= $mod['name'] ?></h5>
                    <p class="card-text text-muted small mb-4" style="min-height: 40px;"><?= $mod['desc'] ?></p>

                    <?php if($mod['status'] === 'completed'): ?>
                        <div class="alert alert-light border border-light-subtle d-flex align-items-center mb-0 py-2">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <div>
                                <small class="text-muted d-block" style="font-size: 0.75rem;">Primary Result</small>
                                <strong class="text-dark"><?= $mod['result_summary'] ?></strong>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card-footer bg-white border-0 p-4 pt-0">
                    <?php if($mod['status'] === 'completed'): ?>
                        <div class="d-grid gap-2">
                            <a href="<?= base_url('test/results/'.$mod['code']) ?>" class="btn btn-outline-success">
                                View Full Report
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="d-grid gap-2">
                            <a href="<?= base_url('test/'.$mod['code']) ?>" class="btn btn-primary">
                                Start Assessment
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
    .hover-card { 
        transition: transform 0.2s ease-in-out, box-shadow 0.2s; 
    }
    .hover-card:hover { 
        transform: translateY(-5px); 
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; 
    }
    .icon-box { 
        width: 50px; 
        height: 50px; 
        border-radius: 12px; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
    }
    .letter-spacing-1 { letter-spacing: 1px; }
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">