<?= $this->include('layouts/header') ?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
        <p class="lead mb-0">Welcome back, <?= esc($user['full_name']) ?>!</p>
    </div>
</div>

<div class="container">
    <div class="row">
        <!-- Stats Cards -->
        <div class="col-md-3 mb-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-clipboard-check fa-3x text-primary mb-3"></i>
                    <h3 class="fw-bold"><?= count($recent_sessions ?? []) ?></h3>
                    <p class="text-muted mb-0">Assessments Taken</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-file-alt fa-3x text-success mb-3"></i>
                    <h3 class="fw-bold"><?= count($reports ?? []) ?></h3>
                    <p class="text-muted mb-0">Reports Generated</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-briefcase fa-3x text-info mb-3"></i>
                    <h3 class="fw-bold">
                        <?php 
                            $matchCount = 0;
                            if (!empty($reports)) {
                                $latestReport = $reports[0];
                                $matches = json_decode($latestReport['top_career_matches'] ?? '[]', true);
                                $matchCount = count($matches);
                            }
                            echo $matchCount;
                        ?>
                    </h3>
                    <p class="text-muted mb-0">Career Matches</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-chart-line fa-3x text-warning mb-3"></i>
                    <h3 class="fw-bold">
                        <?php 
                            $completionRate = 0;
                            if (!empty($recent_sessions)) {
                                $completed = array_filter($recent_sessions, fn($s) => $s['status'] == 'completed');
                                $completionRate = count($completed) > 0 ? round((count($completed) / count($recent_sessions)) * 100) : 0;
                            }
                            echo $completionRate;
                        ?>%
                    </h3>
                    <p class="text-muted mb-0">Completion Rate</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Quick Actions -->
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="<?= base_url('assessment/start') ?>" class="text-decoration-none">
                                <div class="card bg-light h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-play-circle fa-3x text-primary mb-3"></i>
                                        <h5>Start New Assessment</h5>
                                        <p class="text-muted small mb-0">Begin your career discovery journey</p>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-6">
                            <a href="<?= base_url('careers') ?>" class="text-decoration-none">
                                <div class="card bg-light h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-search fa-3x text-success mb-3"></i>
                                        <h5>Explore Careers</h5>
                                        <p class="text-muted small mb-0">Browse career opportunities</p>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-6">
                            <a href="<?= base_url('reports') ?>" class="text-decoration-none">
                                <div class="card bg-light h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-file-download fa-3x text-info mb-3"></i>
                                        <h5>View Reports</h5>
                                        <p class="text-muted small mb-0">Access your assessment reports</p>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-6">
                            <a href="<?= base_url('profile') ?>" class="text-decoration-none">
                                <div class="card bg-light h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-user-edit fa-3x text-warning mb-3"></i>
                                        <h5>Update Profile</h5>
                                        <p class="text-muted small mb-0">Manage your account settings</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Summary -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-user"></i> Profile Summary</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <?php if (!empty($user['profile_image'])): ?>
                            <img src="<?= base_url('uploads/profiles/' . $user['profile_image']) ?>" 
                                 class="rounded-circle" 
                                 width="100" 
                                 height="100" 
                                 alt="Profile">
                        <?php else: ?>
                            <i class="fas fa-user-circle fa-5x text-muted"></i>
                        <?php endif; ?>
                    </div>

                    <h5 class="text-center mb-3"><?= esc($user['full_name']) ?></h5>

                    <table class="table table-sm table-borderless">
                        <tr>
                            <td><i class="fas fa-envelope text-muted"></i></td>
                            <td><?= esc($user['email']) ?></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-graduation-cap text-muted"></i></td>
                            <td><?= ucwords(str_replace('_', ' ', $user['educational_level'])) ?></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-school text-muted"></i></td>
                            <td><?= esc($user['school_name'] ?? 'Not specified') ?></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-map-marker-alt text-muted"></i></td>
                            <td><?= esc($user['city'] ?? 'Not specified') ?>, <?= esc($user['country'] ?? '') ?></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-calendar text-muted"></i></td>
                            <td>Member since <?= date('M Y', strtotime($user['created_at'])) ?></td>
                        </tr>
                    </table>

                    <div class="d-grid">
                        <a href="<?= base_url('profile/edit') ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Assessments -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Recent Assessments</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_sessions)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Session Code</th>
                                        <th>Age Group</th>
                                        <th>Status</th>
                                        <th>Started</th>
                                        <th>Completed</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($recent_sessions, 0, 5) as $session): ?>
                                        <tr>
                                            <td><code><?= esc($session['session_code']) ?></code></td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?= ucwords(str_replace('_', ' ', $session['age_group'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                    $statusColors = [
                                                        'not_started' => 'secondary',
                                                        'in_progress' => 'warning',
                                                        'completed' => 'success',
                                                        'abandoned' => 'danger'
                                                    ];
                                                    $color = $statusColors[$session['status']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?= $color ?>">
                                                    <?= ucwords(str_replace('_', ' ', $session['status'])) ?>
                                                </span>
                                            </td>
                                            <td><?= $session['started_at'] ? date('M d, Y', strtotime($session['started_at'])) : 'Not started' ?></td>
                                            <td><?= $session['completed_at'] ? date('M d, Y', strtotime($session['completed_at'])) : '-' ?></td>
                                            <td>
                                                <?php if ($session['status'] == 'completed'): ?>
                                                    <a href="<?= base_url('report/' . $session['session_code']) ?>" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fas fa-file-alt"></i> View Report
                                                    </a>
                                                <?php elseif ($session['status'] == 'in_progress'): ?>
                                                    <a href="<?= base_url('assessment/resume/' . $session['id']) ?>" 
                                                       class="btn btn-sm btn-warning">
                                                        <i class="fas fa-play"></i> Resume
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No assessments yet</h5>
                            <p class="text-muted">Start your first assessment to get personalized career recommendations</p>
                            <a href="<?= base_url('assessment/start') ?>" class="btn btn-primary">
                                <i class="fas fa-play-circle"></i> Start Assessment
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Latest Report Preview -->
    <?php if (!empty($reports)): ?>
        <?php $latestReport = $reports[0]; ?>
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-star"></i> Latest Report Highlights</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center mb-3">
                                <h6 class="text-muted">Personality Type</h6>
                                <h2 class="text-primary fw-bold"><?= esc($latestReport['mbti_type']) ?></h2>
                            </div>
                            <div class="col-md-4 text-center mb-3">
                                <h6 class="text-muted">Emotional Intelligence</h6>
                                <h2 class="text-success fw-bold"><?= round($latestReport['eq_score'], 1) ?>%</h2>
                            </div>
                            <div class="col-md-4 text-center mb-3">
                                <h6 class="text-muted">IQ Estimate</h6>
                                <h2 class="text-info fw-bold"><?= $latestReport['iq_estimate'] ?></h2>
                            </div>
                        </div>

                        <div class="text-center mt-3">
                            <a href="<?= base_url('report/' . $latestReport['report_code']) ?>" 
                               class="btn btn-primary">
                                <i class="fas fa-eye"></i> View Full Report
                            </a>
                            <a href="<?= base_url('report/download/' . $latestReport['report_code']) ?>" 
                               class="btn btn-outline-primary">
                                <i class="fas fa-download"></i> Download PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?= $this->include('layouts/footer') ?>