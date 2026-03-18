<!DOCTYPE html>
<html lang="en">
<head>
   <title>AI Career Simulator | Counselor</title>
    <?= csrf_meta() ?> 
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js" integrity="sha384-jb8JQMbMoBUzgWatfe6COACi2ljcDdZQ2OxczGA3bGNeWe+6DChMTBJemed7ZnvJ" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js" integrity="sha384-k5vbMeKHbxEZ0AEBTSdR7UjAgWCcUfrS8c0c5b2AfIh7olfhNkyCZYwOfzOQhauK" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js" integrity="sha384-PgPBH0hy6DTJwu7pTf6bkRqPlf/+pjUBExpr/eIfzszlGYFlF9Wi9VTAJODPhgCO" crossorigin="anonymous"></script>
    
    <style>
        body { background-color: #f1f5f9; color: #334155; font-size: 0.85rem; }
        .sidebar { height: 100vh; background: #0f172a; color: white; padding-top: 20px; position: fixed; width: 250px; z-index: 1000; }
        .sidebar a { color: #cbd5e1; text-decoration: none; padding: 15px 20px; display: block; font-weight: 600; transition: 0.3s; cursor: pointer; }
        .sidebar a:hover, .sidebar a.active { background: #1e293b; color: white; border-left: 4px solid #3d8c83; }
        .main-content { margin-left: 250px; padding: 20px; }
        .card { border-radius: 6px; border: 1px solid #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.05); margin-bottom: 0; }
        .card-header { background-color: #ffffff; padding: 0.4rem 0.75rem; font-size: 0.8rem; font-weight: 700; border-bottom: 1px solid #e2e8f0; }
        .chart-container { position: relative; height: 180px; width: 100%; padding: 5px; }
        .form-label { font-weight: 600; font-size: 0.75rem; margin-bottom: 0.1rem; color: #475569; }
        .badge-custom { font-size: 0.75rem; padding: 0.35em 0.65em; }
        .search-wrapper { position: relative; width: 280px; }
        #searchResults { position: absolute; top: 100%; left: 0; right: 0; z-index: 1050; max-height: 300px; overflow-y: auto; }
        .stat-card { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-3px); }
        .icon-circle { width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
        .table-custom th { background-color: #f1f5f9; color: #475569; font-weight: 600; border-bottom: 2px solid #cbd5e1; }
        .table-custom td { vertical-align: middle; color: #334155; }
        .badge-soft-success { background-color: #d1fae5; color: #065f46; }
        .badge-soft-warning { background-color: #fef3c7; color: #92400e; }
        
        /* SPA Logic */
        .tab-pane { display: none; }
        .tab-pane.active { display: block; animation: fadeIn 0.4s; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="text-center mb-4 px-3">
        <h4 class="fw-bold text-white mb-0">Pharos Counselor</h4>
        <small class="text-muted">System Management</small>
    </div>
    <a class="nav-link active" data-target="dashboard"><i class="fas fa-chart-line me-2"></i> Home</a>
    <a class="nav-link" data-target="analytics"><i class="fas fa-users me-2"></i> Candidate List</a>
    <a class="nav-link" data-target="appointments"><i class="fas fa-calendar-check me-2"></i> Appointments</a>
    <a class="nav-link" data-target="reports"><i class="fas fa-file-pdf me-2"></i> Review Reports</a>
    <a class="nav-link" data-target="careerLib"><i class="fas fa-list me-2"></i> Career Library</a>
    <a class="nav-link" data-target="aiSim"><i class="fas fa-brain me-2"></i> AI Career Simulator</a>
    <a class="nav-link" data-target="cohortAnalytics"><i class="fas fa-chart-pie me-2"></i> Cohort Analytics</a>
    <a href="<?= base_url('logout') ?>" class="mt-5 text-danger"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
</div>

<div class="main-content">
    
    <div id="dashboard" class="tab-pane active">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-3"><i class="fas fa-tachometer-alt text-secondary me-2"></i>Counselor Command Center</h5>
                <p class="text-muted mb-0">Welcome back, <?= esc(session()->get('fname') ?? session()->get('name')) ?></p>
            </div>
            <button class="btn btn-primary btn-sm shadow-sm fw-bold nav-link" data-target="aiSim">
                <i class="fas fa-bolt me-2"></i> Launch AI Simulator
            </button>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-circle bg-primary text-white me-3 shadow-sm"><i class="fas fa-users"></i></div>
                        <div>
                            <h6 class="text-muted mb-1 fw-bold">Total Students</h6>
                            <h3 class="mb-0 fw-bold"><?= $total_students ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-circle bg-success text-white me-3 shadow-sm"><i class="fas fa-check-circle"></i></div>
                        <div>
                            <h6 class="text-muted mb-1 fw-bold">Completed Profiles</h6>
                            <h3 class="mb-0 fw-bold"><?= $completed_profiles ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-circle bg-warning text-dark me-3 shadow-sm"><i class="fas fa-calendar-alt"></i></div>
                        <div>
                            <h6 class="text-muted mb-1 fw-bold">Pending Appointments</h6>
                            <h3 class="mb-0 fw-bold"><?= $pending_apts ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-user-graduate text-primary me-2"></i> Student Roster & Tracking</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-custom mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Student Name</th>
                                        <th>Contact</th>
                                        <th>Tests Done</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($student_roster)): ?>
                                        <tr><td colspan="5" class="text-center py-4 text-muted">No students assigned.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($student_roster as $s): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-dark"><?= esc($s['name']) ?></td>
                                            <td><div class="small"><i class="fas fa-envelope text-muted me-1"></i><?= esc($s['email']) ?></div></td>
                                            <td>
                                                <div class="progress" style="height: 6px; width: 80px; margin-top: 6px;">
                                                    <div class="progress-bar bg-<?= $s['status_col'] ?>" style="width: <?= min(100, ($s['tests_done']/6)*100) ?>%;"></div>
                                                </div>
                                                <small class="text-muted"><?= $s['tests_done'] ?> / 6 Modules</small>
                                            </td>
                                            <td><span class="badge badge-soft-<?= $s['status_col'] ?> rounded-pill px-3 py-2"><?= $s['status'] ?></span></td>
                                            <td class="text-end pe-4">
                                                <?php if($s['tests_done'] > 0): ?>
                                                    <button class="btn btn-sm btn-outline-primary load-ai-btn" data-id="<?= $s['id'] ?>" data-name="<?= esc($s['name']) ?>" title="Run AI Inference">
                                                        <i class="fas fa-brain"></i> AI
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-light text-muted" disabled>N/A</button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-calendar-check text-warning me-2"></i> Upcoming Appointments</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php if(empty($appointments)): ?>
                                <li class="list-group-item py-4 text-center text-muted">No upcoming appointments.</li>
                            <?php else: ?>
                                <?php foreach($appointments as $apt): ?>
                                    <li class="list-group-item p-3 border-bottom">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="fw-bold mb-1 text-dark">
                                                    <?php 
                                                        $aptUser = array_filter($users, fn($u) => $u['id'] == $apt['user_id']);
                                                        echo esc(reset($aptUser)['name'] ?? reset($aptUser)['fname'] ?? 'Student');
                                                    ?>
                                                </h6>
                                                <div class="text-muted small mb-1"><i class="fas fa-clock me-1"></i> <?= date('M d - h:i A', strtotime($apt['preferred_datetime'])) ?></div>
                                                <div class="text-secondary small"><i class="fas fa-tag me-1"></i> <?= esc($apt['topic'] ?? 'Counseling') ?></div>
                                            </div>
                                            <div>
                                                <?php if($apt['status'] === 'pending'): ?>
                                                    <button class="btn btn-sm btn-success update-apt" data-id="<?= $apt['id'] ?>" data-status="approved"><i class="fas fa-check"></i></button>
                                                    <button class="btn btn-sm btn-danger update-apt" data-id="<?= $apt['id'] ?>" data-status="cancelled"><i class="fas fa-times"></i></button>
                                                <?php elseif($apt['status'] === 'approved'): ?>
                                                    <span class="badge bg-success">Approved</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><?= ucfirst($apt['status']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="aiSim" class="tab-pane">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom sticky-top shadow-sm" style="background: #f1f5f9; z-index:10;">
            <div class="d-flex align-items-center gap-3">
                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-brain text-primary me-2"></i>Pharos Neural Engine</h5>
                
                <div id="activeCandidateBadge" style="display: none;" class="bg-white border rounded px-2 py-1 shadow-sm d-flex align-items-center gap-2">
                    <span class="text-muted" style="font-size: 0.75rem;">Candidate:</span>
                    <strong id="selectedCandidateName" class="text-primary">--</strong>
                    <span id="iqBadge" class="badge bg-dark badge-custom" style="display:none;"></span>
                    <span id="mbtiBadge" class="badge bg-info text-dark badge-custom" style="display:none;"></span>
                </div>
            </div>

            <div class="search-wrapper">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="searchInput" class="form-control" placeholder="Search candidate name/email...">
                    <button class="btn btn-primary btn-sm px-3" id="searchBtn">Find</button>
                </div>
                <ul id="searchResults" class="list-group shadow-sm" style="display:none;"></ul>
            </div>
        </div>
       
        <div id="simulatorWorkspace" style="display: none;">
            <h5 class="fw-bold mb-3"><i class="fas fa-chart-pie text-secondary me-2"></i> Psychometric & Cognitive Profile</h5>
            <div class="row g-4 mb-4">
                <div class="col-lg-4 col-md-6" id="card_riasec"><div class="card h-100"><div class="card-header"><i class="fas fa-compass text-primary me-2"></i> RIASEC Interest</div><div class="card-body chart-container"><canvas id="riasecChart"></canvas></div></div></div>
                <div class="col-lg-4 col-md-6" id="card_aptitude"><div class="card h-100"><div class="card-header"><i class="fas fa-brain text-success me-2"></i> Aptitude & Logic</div><div class="card-body chart-container"><canvas id="aptitudeChart"></canvas></div></div></div>
                <div class="col-lg-4 col-md-6" id="card_mi"><div class="card h-100"><div class="card-header"><i class="fas fa-network-wired text-info me-2"></i> Multiple Intelligences</div><div class="card-body chart-container"><canvas id="miChart"></canvas></div></div></div>
                <div class="col-lg-4 col-md-6" id="card_eq"><div class="card h-100"><div class="card-header d-flex justify-content-between align-items-center"><span><i class="fas fa-heart text-danger me-2"></i> Emotional Intelligence</span><span id="eqScoreBadge" class="badge bg-danger"></span></div><div class="card-body chart-container"><canvas id="eqChart"></canvas></div></div></div>
                <div class="col-lg-4 col-md-6" id="card_motivators"><div class="card h-100"><div class="card-header"><i class="fas fa-fire text-warning me-2"></i> Core Motivators</div><div class="card-body chart-container"><canvas id="motivatorsChart"></canvas></div></div></div>
                <div class="col-lg-4 col-md-6" id="card_traits"><div class="card h-100"><div class="card-header"><i class="fas fa-user-tie text-secondary me-2"></i> Extracted Soft Skills</div><div class="card-body chart-container"><canvas id="traitsChart"></canvas></div></div></div>
            </div>

            <div class="card border-primary shadow-lg" style="border-width: 2px;">
                <div class="card-header bg-primary-subtle fs-5"><i class="fas fa-sliders-h me-2"></i> ML Input Vectors Config</div>
                <div class="card-body p-4">
                    <form id="simulationForm">
                        <input type="hidden" id="raw_riasec_r"> <input type="hidden" id="raw_riasec_i"> <input type="hidden" id="raw_riasec_a"> <input type="hidden" id="raw_riasec_s"> <input type="hidden" id="raw_riasec_e"> <input type="hidden" id="raw_riasec_c">
                        <input type="hidden" id="raw_analytical"> <input type="hidden" id="raw_creative"> <input type="hidden" id="raw_social"> <input type="hidden" id="raw_leadership"> <input type="hidden" id="raw_technical"> <input type="hidden" id="raw_empathy">

                        <div class="row g-4">
                            <div class="col-md-4 border-end">
                                <h6 class="fw-bold text-primary mb-3">1. Academic Baseline (0-100)</h6>
                                <div class="mb-3"><label class="form-label">Mathematics / Numerical</label><input type="number" id="math_score" class="form-control" required></div>
                                <div class="mb-3"><label class="form-label">Science / Spatial</label><input type="number" id="science_score" class="form-control" required></div>
                                <div class="mb-3"><label class="form-label">English / Verbal</label><input type="number" id="english_score" class="form-control" required></div>
                            </div>
                            <div class="col-md-4 border-end">
                                <h6 class="fw-bold text-primary mb-3">2. Lifestyle & Market Proxies</h6>
                                <div class="mb-4"><label class="form-label">Financial Goal (Salary Proxy)</label><select id="lifestyle_proxy" class="form-select" required><option value="50.0">Stability (~$50k/yr)</option><option value="75.0" selected>Comfort (~$75k/yr)</option><option value="110.0">Wealth & Luxury (~$110k+/yr)</option></select></div>
                                <div class="mb-3"><label class="form-label">Industry Pace (Growth Proxy)</label><select id="risk_proxy" class="form-select" required><option value="2.0">Traditional / Highly Stable (~2% growth)</option><option value="5.0" selected>Balanced / Normal (~5% growth)</option><option value="9.0">Cutting-Edge / Rapid (~9% growth)</option></select></div>
                            </div>
                            <div class="col-md-4">
                                <h6 class="fw-bold text-primary mb-3">3. Career Comparison</h6>
                                <div class="mb-4"><label class="form-label">Option A</label><select id="career_a" class="form-select border-success" required><option value="Architecture & Engineering">Architecture & Engineering</option><option value="Business & Financial">Business & Financial</option><option value="Computer & Mathematical">Computer & Mathematical</option><option value="Management & Leadership">Management & Leadership</option><option value="Healthcare Practitioners">Healthcare Practitioners</option><option value="Arts, Design & Media">Arts, Design & Media</option><option value="Science & Social Science">Science & Social Science</option><option value="Legal & Public Policy">Legal & Public Policy</option><option value="Education & Training">Education & Training</option><option value="Sales & Related">Sales & Related</option><option value="Office & Admin Support">Office & Admin Support</option><option value="Community & Social Service">Community & Social Service</option><option value="Protective Service">Protective Service</option><option value="Military Specific">Military Specific</option><option value="Installation & Repair">Installation & Repair</option><option value="Production & Manufacturing">Production & Manufacturing</option><option value="Transportation">Transportation</option><option value="Food Preparation">Food Preparation</option></select></div>
                                <div class="mb-3"><label class="form-label">Option B</label><select id="career_b" class="form-select border-info" required><option value="Business & Financial">Business & Financial</option><option value="Architecture & Engineering">Architecture & Engineering</option><option value="Computer & Mathematical">Computer & Mathematical</option><option value="Management & Leadership">Management & Leadership</option><option value="Healthcare Practitioners">Healthcare Practitioners</option><option value="Arts, Design & Media">Arts, Design & Media</option><option value="Science & Social Science">Science & Social Science</option><option value="Legal & Public Policy">Legal & Public Policy</option><option value="Education & Training">Education & Training</option><option value="Sales & Related">Sales & Related</option><option value="Office & Admin Support">Office & Admin Support</option><option value="Community & Social Service">Community & Social Service</option><option value="Protective Service">Protective Service</option><option value="Military Specific">Military Specific</option><option value="Installation & Repair">Installation & Repair</option><option value="Production & Manufacturing">Production & Manufacturing</option><option value="Transportation">Transportation</option><option value="Food Preparation">Food Preparation</option></select></div>
                            </div>
                        </div>
                        <hr class="my-4">
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold"><i class="fas fa-bolt me-2"></i> Execute AI Prediction Model</button>
                    </form>
                </div>
            </div>
            <div id="aiOutputArea" class="mt-4 mb-5"></div>
        </div>
    </div>
    
   <div id="analytics" class="tab-pane">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="fas fa-users text-primary me-2"></i> Candidate Management</h4>
                <p class="text-muted mb-0">View, search, and analyze your assigned candidates.</p>
            </div>
        </div>
        <div id="candidatesListContainer">
            <div class="text-center py-5">
                <i class="fas fa-circle-notch fa-spin fa-3x text-primary mb-3"></i>
                <h5 class="text-muted">Loading Data...</h5>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rawScoresModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white border-0 py-3">
                    <h5 class="modal-title fw-bold" id="scoreModalTitle"><i class="fas fa-chart-bar me-2"></i> Raw Scores</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0 bg-light" id="scoresTableBody">
                    </div>
                <div class="modal-footer border-top-0 bg-white">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    
    <div id="appointments" class="tab-pane">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="fas fa-calendar-alt text-warning me-2"></i> Master Schedule</h4>
                <p class="text-muted mb-0">Manage all student session requests and historical appointments.</p>
            </div>
        </div>
        <div id="appointmentsContainer">
            <div class="text-center py-5">
                <i class="fas fa-circle-notch fa-spin fa-3x text-primary mb-3"></i>
                <h5 class="text-muted">Loading Schedule...</h5>
            </div>
        </div>
    </div>
    
    <div id="reports" class="tab-pane">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="fas fa-file-pdf text-danger me-2"></i> Review Completed Reports</h4>
                <p class="text-muted mb-0">Access and evaluate the full psychometric dossiers for students who have finished all assessments.</p>
            </div>
        </div>
        <div id="reportsContainer">
            <div class="text-center py-5">
                <i class="fas fa-circle-notch fa-spin fa-3x text-primary mb-3"></i>
                <h5 class="text-muted">Loading Completed Reports...</h5>
            </div>
        </div>
    </div>
    
    <div id="careerLib" class="tab-pane">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="fas fa-book-open text-primary me-2"></i> Career Library Explorer</h4>
                <p class="text-muted mb-0">Browse career clusters to view categorized professions and their requirements.</p>
            </div>
        </div>

        <div id="clusterView">
            <div class="row g-4" id="clusterGrid">
                </div>
            <div id="clusterLoading" class="text-center py-5">
                <i class="fas fa-circle-notch fa-spin fa-3x text-primary mb-3"></i>
                <h5 class="text-muted">Loading Clusters...</h5>
            </div>
        </div>

        <div id="careersListView" style="display:none;">
            <button class="btn btn-outline-secondary btn-sm mb-4 fw-bold shadow-sm" onclick="showClusters()">
                <i class="fas fa-arrow-left me-2"></i> Back to Categories
            </button>
            
            <div class="card border-0 shadow-sm mb-4 border-top border-4 border-primary">
                <div class="card-body">
                    <h5 class="fw-bold mb-0 text-primary" id="currentClusterTitle">Category Name</h5>
                    <small class="text-muted">Alphabetical list of careers in this cluster.</small>
                </div>
            </div>

            <div class="row g-3" id="careersListGrid">
                </div>
        </div>
    </div>
    
    <div id="cohortAnalytics" class="tab-pane">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="fas fa-chart-network text-info me-2"></i> Enterprise Cohort Intelligence</h4>
                <p class="text-muted mb-0">Macroscopic analysis of cognitive, emotional, and career profiles across your institution.</p>
            </div>
            <div>
                <select id="analyticsBatchSelect" class="form-select shadow-sm fw-bold text-primary border-primary">
                    <option value="class_8">Class 8</option>
                    <option value="class_9">Class 9</option>
                    <option value="class_10">Class 10</option>
                    <option value="class_11">Class 11</option>
                    <option value="class_12" selected>Class 12</option>
                </select>
            </div>
        </div>

        <div id="analyticsErrorArea" class="alert alert-warning" style="display:none;"></div>

        <div id="analyticsChartsArea" style="display:none;">
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <div class="card h-100 border-0 shadow-sm border-top border-4 border-success">
                        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-graduation-cap text-success me-2"></i> Recommended Academic Streams (Data-Driven Subject Selection)</h6>
                            <span class="badge bg-light text-muted border">Based on RIASEC & Aptitude intersections</span>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-5 chart-container" style="height: 300px;">
                                    <canvas id="aggStreamChart"></canvas>
                                </div>
                                <div class="col-md-7">
                                    <h6 class="fw-bold text-dark mb-3">How are these subjects recommended?</h6>
                                    <ul class="list-group list-group-flush small text-muted">
                                        <li class="list-group-item bg-transparent px-0"><strong class="text-primary">Science (PCM):</strong> High Investigative traits intersected with dominant Numerical Aptitude.</li>
                                        <li class="list-group-item bg-transparent px-0"><strong class="text-success">Science (PCB):</strong> High Investigative traits intersected with dominant Spatial/Science Aptitude.</li>
                                        <li class="list-group-item bg-transparent px-0"><strong class="text-warning text-dark">Commerce:</strong> Dominant Enterprising or Conventional traits (Business & Financial pathways).</li>
                                        <li class="list-group-item bg-transparent px-0"><strong class="text-danger">Humanities & Arts:</strong> High Social or Artistic traits (Design, Psychology, Law, Media).</li>
                                        <li class="list-group-item bg-transparent px-0"><strong class="text-info text-dark">Vocational & IT:</strong> High Realistic traits (Hands-on, Applied Technologies).</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm bg-primary text-white h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-white-50 mb-1 fw-bold text-uppercase">Total Batch Size</h6>
                                <h2 class="mb-0 fw-bold" id="kpiTotalStudents">0</h2>
                            </div>
                            <i class="fas fa-users fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm bg-success text-white h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-white-50 mb-1 fw-bold text-uppercase">Most Common Personality</h6>
                                <h2 class="mb-0 fw-bold" id="kpiTopMbti">--</h2>
                            </div>
                            <i class="fas fa-brain fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-header bg-white py-3 border-bottom"><h6 class="fw-bold mb-0 text-dark"><i class="fas fa-compass text-primary me-2"></i> RIASEC Career Interests (Averages)</h6></div>
                        <div class="card-body chart-container" style="height: 350px;"><canvas id="aggRiasecChart"></canvas></div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-header bg-white py-3 border-bottom"><h6 class="fw-bold mb-0 text-dark"><i class="fas fa-users text-warning me-2"></i> 16 Personalities (MBTI) Distribution</h6></div>
                        <div class="card-body chart-container" style="height: 350px;"><canvas id="aggMbtiChart"></canvas></div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-header bg-white py-3 border-bottom"><h6 class="fw-bold mb-0 text-dark"><i class="fas fa-bolt text-success me-2"></i> Cognitive Aptitude Profile</h6></div>
                        <div class="card-body chart-container" style="height: 350px;"><canvas id="aggAptitudeChart"></canvas></div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-header bg-white py-3 border-bottom"><h6 class="fw-bold mb-0 text-dark"><i class="fas fa-heart text-danger me-2"></i> Emotional Intelligence (EQ) Map</h6></div>
                        <div class="card-body chart-container" style="height: 350px;"><canvas id="aggEqChart"></canvas></div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-header bg-white py-3 border-bottom"><h6 class="fw-bold mb-0 text-dark"><i class="fas fa-network-wired text-info me-2"></i> Multiple Intelligences (Dominance)</h6></div>
                        <div class="card-body chart-container" style="height: 350px;"><canvas id="aggMiChart"></canvas></div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-header bg-white py-3 border-bottom"><h6 class="fw-bold mb-0 text-dark"><i class="fas fa-eye text-primary me-2"></i> VARK Learning Styles</h6></div>
                        <div class="card-body chart-container" style="height: 350px;"><canvas id="aggVarkChart"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-header bg-white py-3 border-bottom"><h6 class="fw-bold mb-0 text-dark"><i class="fas fa-fire text-danger me-2"></i> Core Workplace Motivators</h6></div>
                        <div class="card-body chart-container" style="height: 350px;"><canvas id="aggMotivatorsChart"></canvas></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="careerDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 py-3">
                    <h5 class="modal-title fw-bold" id="modalCareerTitle"><i class="fas fa-briefcase me-2"></i> Career Profile</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0 bg-white">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0 align-middle">
                            <tbody id="careerTableBody">
                                </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close Profile</button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="remarksModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white border-0 py-3">
                    <h5 class="modal-title fw-bold"><i class="fas fa-pen-nib me-2"></i> Report Details: <span id="remarksStudentName"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body bg-light">
                    <form id="remarksForm">
                        <input type="hidden" id="remarksStudentId">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Report Status</label>
                            <select id="reportStatus" class="form-select shadow-sm">
                                <option value="generated">Generated (Unread)</option>
                                <option value="reviewed">Reviewed by Counselor</option>
                                <option value="sent">Sent to Student</option>
                                <option value="discussed">Discussed in Session</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Counselor's Final Verdict & Remarks</label>
                            <textarea id="counselorRemarks" class="form-control shadow-sm" rows="5" placeholder="Add personalized advice to be appended to the final PDF report..."></textarea>
                            <small class="text-muted mt-1 d-block">These remarks will be printed on the final page of the student's dossier.</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top-0 bg-white">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary fw-bold" id="saveRemarksBtn">Save Details</button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="crmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 py-3">
                    <h5 class="modal-title fw-bold"><i class="fas fa-address-book me-2"></i> CRM Workspace: <span id="crmStudentName"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-light p-4">
                    <input type="hidden" id="crmStudentId">
                    <div class="row g-4">
                        
                        <div class="col-lg-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-header bg-white fw-bold text-dark border-bottom"><i class="fas fa-lock text-warning me-2"></i> Private Session Notes</div>
                                <div class="card-body p-2" style="height: 300px; overflow-y: auto;" id="crmNotesList"></div>
                                <div class="card-footer bg-white border-top-0 p-2">
                                    <textarea id="newNoteText" class="form-control form-control-sm mb-2" rows="2" placeholder="Type a private note..."></textarea>
                                    <button class="btn btn-sm btn-primary w-100" id="saveNoteBtn">Add Note</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-header bg-white fw-bold text-dark border-bottom"><i class="fas fa-check-square text-success me-2"></i> Action Items (To-Do)</div>
                                <div class="card-body p-2" style="height: 300px; overflow-y: auto;" id="crmTasksList"></div>
                                <div class="card-footer bg-white border-top-0 p-2">
                                    <input type="text" id="newTaskDesc" class="form-control form-control-sm mb-2" placeholder="Task description...">
                                    <button class="btn btn-sm btn-success w-100" id="saveTaskBtn">Assign Task</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-header bg-white fw-bold text-dark border-bottom"><i class="fas fa-link text-info me-2"></i> Shared Resources</div>
                                <div class="card-body p-2" style="height: 300px; overflow-y: auto;" id="crmResourcesList"></div>
                                <div class="card-footer bg-white border-top-0 p-2">
                                    <input type="text" id="newResTitle" class="form-control form-control-sm mb-2" placeholder="Resource Title">
                                    <input type="url" id="newResUrl" class="form-control form-control-sm mb-2" placeholder="https://...">
                                    <button class="btn btn-sm btn-info text-white w-100" id="saveResourceBtn">Share Link</button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    // Wait for the DOM to process the injected HTML
    setTimeout(function() {
        // Double check that DataTable actually exists before calling it
        if (typeof $.fn.DataTable !== 'undefined') {
            if (!$.fn.DataTable.isDataTable('#candidatesDataTable')) {
                $('#candidatesDataTable').DataTable({
                    "order": [[ 4, "desc" ]], // Sort by Joined Date
                    "pageLength": 25,
                    "language": { "search": "<i class='fas fa-search text-muted'></i> Search:" }
                });
            }
        } else {
            console.error("DataTables library is still not loaded. Check script order in index.php.");
        }
    }, 100); // 100ms delay ensures HTML is fully injected before binding
// GLOBAL AJAX SECURITY: Attach CSRF Token to every POST request automatically
$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="X-CSRF-TOKEN"]').attr('content') }
});

// ADD THESE TWO MISSING LINES RIGHT HERE:
let currentCsrfName = '<?= csrf_token() ?>';
let currentCsrfHash = '<?= csrf_hash() ?>';

// ADD THIS MISSING FUNCTION HERE:
function escapeHTML(str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/[&<>'"]/g, 
        tag => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            "'": '&#39;',
            '"': '&quot;'
        }[tag] || tag)
    );
}

// 1. SPA Navigation Logic
$('.nav-link').click(function(e) {
    if($(this).attr('href')) return; 
    e.preventDefault();
    let target = $(this).data('target');
    if(!target) return;
    
    $('.nav-link').removeClass('active');
    $(`.nav-link[data-target="${target}"]`).addClass('active');
    
    $('.tab-pane').removeClass('active');
    $('#' + target).addClass('active');
    
    // Auto-load categories if clicking library
    if(target === 'careerLib' && $('#clusterGrid').children().length === 0) {
        loadClusters();
    }
  
        if(target === 'analytics' && $('#candidatesListContainer .fa-spin').length > 0) {
            loadCandidatesTable();
        }
   if(target === 'reports' && $('#reportsContainer .fa-spin').length > 0) {
            $.post('<?= base_url("cca/loadCompletedReports") ?>', { [currentCsrfName]: currentCsrfHash }, function(htmlResponse) {
                $('#reportsContainer').html(htmlResponse);
            }).fail(function() {
                $('#reportsContainer').html('<div class="alert alert-danger">Error loading reports.</div>');
            });
        }
        if(target === 'appointments' && $('#appointmentsContainer .fa-spin').length > 0) {
            $.post('<?= base_url("cca/loadAppointments") ?>', { [currentCsrfName]: currentCsrfHash }, function(htmlResponse) {
                $('#appointmentsContainer').html(htmlResponse);
            }).fail(function() {
                $('#appointmentsContainer').html('<div class="alert alert-danger">Error loading appointments.</div>');
            });
        }
        
    if(target === 'cohortAnalytics') {
        loadCohortAnalytics();
    }
});

// SPA Trigger from the Data Table
$('.load-ai-btn').click(function(e) {
    e.preventDefault();
    let userId = $(this).data('id');
    let userName = $(this).data('name');
    
    $(`.nav-link[data-target="aiSim"]`).trigger('click');
    loadCandidateIntoAI(userId, userName);
});

let chartInstances = {};

$(document).ready(function () {
    
    // Update Appointments
    $('.update-apt').click(function() {
        let id = $(this).data('id');
        let status = $(this).data('status');
        let btn = $(this);
        btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);

        $.post('<?= base_url("cca/updateAppointmentStatus") ?>', { id: id, status: status }, function(res) {
            if (res.status === 'success') location.reload();
            else { alert('Error updating appointment.'); btn.prop('disabled', false); }
        });
    });

    // Search Candidates
    $('#searchBtn').click(function () {
        let q = $('#searchInput').val();
        if (q.length < 2) return;
        $('#searchBtn').html('<i class="fas fa-spinner fa-spin"></i>');

        $.post('<?= base_url("cca/searchCandidate") ?>', {query: q}, function (res) {
            $('#searchBtn').html('Find');
            let list = $('#searchResults').empty().show();
            if (res.data.length === 0) { list.append('<li class="list-group-item text-danger">No candidates found</li>'); return; }

            res.data.forEach(user => {
                list.append(`<a href="#" class="list-group-item list-group-item-action select-user" data-id="${user.id}" data-name="${user.name}">
                <i class="fas fa-user-circle text-secondary me-2"></i> <strong>${user.name}</strong> <span class="text-muted ms-2">${user.email}</span></a>`);
            });
        });
    });

    $(document).click(function(e) { if (!$(e.target).closest('.search-wrapper').length) $('#searchResults').hide(); });

    // Select Candidate from Search
    $(document).on('click', '.select-user', function (e) {
        e.preventDefault();
        loadCandidateIntoAI($(this).data('id'), $(this).data('name'));
    });

    // Run Neural Network
    $('#simulationForm').submit(function (e) {
        e.preventDefault();
        $('html, body').animate({ scrollTop: $('#aiOutputArea').offset().top - 50 }, 500);
        $('#aiOutputArea').html(`<div class="text-center py-5"><i class="fas fa-circle-notch fa-spin fa-3x text-primary mb-3"></i><h5 class="text-muted">Running AI Inference Matrix...</h5></div>`);

        let payload = {
            RIASEC_R: $('#raw_riasec_r').val(), RIASEC_I: $('#raw_riasec_i').val(), RIASEC_A: $('#raw_riasec_a').val(), 
            RIASEC_S: $('#raw_riasec_s').val(), RIASEC_E: $('#raw_riasec_e').val(), RIASEC_C: $('#raw_riasec_c').val(),
            analytical: $('#raw_analytical').val(), creative: $('#raw_creative').val(), social: $('#raw_social').val(), 
            leadership: $('#raw_leadership').val(), technical: $('#raw_technical').val(), empathy: $('#raw_empathy').val(),
            math_score: $('#math_score').val(), science_score: $('#science_score').val(), english_score: $('#english_score').val(),
            target_salary_k: $('#lifestyle_proxy').val(), desired_growth_pct: $('#risk_proxy').val(),
            career_a: $('#career_a').val(), career_b: $('#career_b').val()
        };

        $.post('<?= base_url("cca/runSimulation") ?>', payload, function (res) {
            if (res.status === 'validation_error') {
                let err = "<h5>Invalid Inputs:</h5><ul>"; $.each(res.errors, function (k, v) { err += `<li>${v}</li>`; }); err += "</ul>";
                $('#aiOutputArea').html(`<div class="alert alert-danger shadow-sm">${err}</div>`);
            } else if (res.status === 'error') {
                $('#aiOutputArea').html(`<div class="alert alert-danger shadow-sm">${res.msg}</div>`);
            } else {
                $('#aiOutputArea').html(res.html);
            }
        });
    });
});

// Modular extraction so both the table button and search bar can load students
function loadCandidateIntoAI(userId, userName) {
    $('#searchResults').hide();
    $('#searchInput').val('');
    $('#selectedCandidateName').text(userName);
    $('#mbtiBadge, #iqBadge').hide();
    $('#simulatorWorkspace').fadeIn();
    $('#aiOutputArea').empty();

    $.post('<?= base_url("cca/fetchCandidateData") ?>', {user_id: userId}, function (res) {
        if (res.status === 'error') { alert(res.msg); $('#simulatorWorkspace').hide(); return; }

        let f = res.features;
        $('#raw_riasec_r').val(f.RIASEC_R); $('#raw_riasec_i').val(f.RIASEC_I); $('#raw_riasec_a').val(f.RIASEC_A); 
        $('#raw_riasec_s').val(f.RIASEC_S); $('#raw_riasec_e').val(f.RIASEC_E); $('#raw_riasec_c').val(f.RIASEC_C);
        $('#raw_analytical').val(f.analytical); $('#raw_creative').val(f.creative); $('#raw_social').val(f.social); 
        $('#raw_leadership').val(f.leadership); $('#raw_technical').val(f.technical); $('#raw_empathy').val(f.empathy);
        
        $('#math_score').val(f.math_score.toFixed(0)); $('#science_score').val(f.science_score.toFixed(0)); $('#english_score').val(f.english_score.toFixed(0));

        renderAllCharts(f, res.charts);
    });
}

function renderAllCharts(features, charts) {
    Object.keys(chartInstances).forEach(key => { if(chartInstances[key]) chartInstances[key].destroy(); });

    if(charts.riasec) {
        $('#card_riasec').show();
        chartInstances['riasec'] = new Chart(document.getElementById('riasecChart'), { type: 'radar', data: { labels: ['Realistic', 'Investigative', 'Artistic', 'Social', 'Enterprising', 'Conventional'], datasets: [{ label: 'Score', data: [charts.riasec.Realistic, charts.riasec.Investigative, charts.riasec.Artistic, charts.riasec.Social, charts.riasec.Enterprising, charts.riasec.Conventional], backgroundColor: 'rgba(59, 130, 246, 0.2)', borderColor: '#3b82f6' }] }, options: { scales: { r: { min: 0, max: 100, ticks: { display: false } } }, plugins: { legend: { display: false } }, maintainAspectRatio: false } });
    } else $('#card_riasec').hide();

    if(charts.aptitude) {
        $('#card_aptitude').show();
        if(charts.iq) $('#iqBadge').text("IQ Proj: " + charts.iq).show();
        chartInstances['aptitude'] = new Chart(document.getElementById('aptitudeChart'), { type: 'bar', data: { labels: ['Numerical', 'Verbal', 'Logic', 'Spatial', 'Mech', 'Admin'], datasets: [{ label: 'Score', data: [charts.aptitude['Numerical Ability'] || 0, charts.aptitude['Verbal Reasoning'] || 0, charts.aptitude['Logical Reasoning'] || 0, charts.aptitude['Spatial Ability'] || 0, charts.aptitude['Mechanical Ability'] || 0, charts.aptitude['Accuracy'] || 0], backgroundColor: ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b', '#64748b', '#10b981'] }] }, options: { indexAxis: 'y', scales: { x: { max: 100 } }, plugins: { legend: { display: false } }, maintainAspectRatio: false } });
    } else $('#card_aptitude').hide();

    if(charts.mi) {
        $('#card_mi').show();
        chartInstances['mi'] = new Chart(document.getElementById('miChart'), { type: 'radar', data: { labels: ['Linguistic','Numerical', 'Logical', 'Spatial', 'Auditory', 'Kinesthetic', 'Intrapersonal', 'Naturalistic'], datasets: [{ label: 'Score', data: [charts.mi.Linguistic, charts.mi.Numerical, charts.mi.Logical, charts.mi.Spatial, charts.mi.Auditory, charts.mi.Kinesthetic, charts.mi.Intrapersonal, charts.mi.Naturalistic], backgroundColor: 'rgba(6, 182, 212, 0.2)', borderColor: '#06b6d4' }] }, options: { scales: { r: { min: 0, max: 100, ticks: { display: false } } }, plugins: { legend: { display: false } }, maintainAspectRatio: false } });
    } else $('#card_mi').hide();

    if(charts.eq) {
        $('#card_eq').show();
        if(charts.overall_eq) $('#eqScoreBadge').text(charts.overall_eq + "/100");
        chartInstances['eq'] = new Chart(document.getElementById('eqChart'), { type: 'polarArea', data: { labels: ['Awareness', 'Regulation', 'Motivation', 'Empathy', 'Social'], datasets: [{ data: [charts.eq['Self-Awareness'], charts.eq['Self-Regulation'], charts.eq['Motivation'], charts.eq['Empathy'], charts.eq['Social Skills']], backgroundColor: ['rgba(239, 68, 68, 0.6)', 'rgba(249, 115, 22, 0.6)', 'rgba(234, 179, 8, 0.6)', 'rgba(16, 185, 129, 0.6)', 'rgba(59, 130, 246, 0.6)'] }] }, options: { scales: { r: { min: 0, max: 100, ticks: { display: false } } }, plugins: { legend: { position: 'right', labels: { boxWidth: 10 } } }, maintainAspectRatio: false } });
    } else $('#card_eq').hide();

    if(charts.motivators) {
        $('#card_motivators').show();
        chartInstances['motivators'] = new Chart(document.getElementById('motivatorsChart'), { type: 'doughnut', data: { labels: ['Learning', 'Independence', 'Structure', 'Adventure', 'Pace', 'Creativity', 'Service'], datasets: [{ data: [charts.motivators['Continuous Learning'], charts.motivators.Independence, charts.motivators['Structured work environment'], charts.motivators.Adventure, charts.motivators['High Paced Environment'], charts.motivators.Creativity, charts.motivators['Social Service']], backgroundColor: ['#ef4444', '#f97316', '#eab308', '#22c55e', '#06b6d4', '#3b82f6', '#a855f7'] }] }, options: { plugins: { legend: { position: 'right', labels: { boxWidth: 10 } } }, cutout: '60%', maintainAspectRatio: false } });
    } else $('#card_motivators').hide();

    $('#card_traits').show();
    chartInstances['traits'] = new Chart(document.getElementById('traitsChart'), { type: 'bar', data: { labels: ['Analytical', 'Creative', 'Social', 'Leadership', 'Technical', 'Empathy'], datasets: [{ label: 'Score (1-10)', data: [features.analytical, features.creative, features.social, features.leadership, features.technical, features.empathy], backgroundColor: '#64748b', borderRadius: 4 }] }, options: { indexAxis: 'y', scales: { x: { max: 10 } }, plugins: { legend: { display: false } }, maintainAspectRatio: false } });

    if(charts.mbti) $('#mbtiBadge').text(charts.mbti.type + " Personality").show();
}

// --- CAREER LIBRARY HTML SERVER RENDERING LOGIC ---

// 1. Load Categories (Clusters)
function loadClusters() {
    $('#clusterGrid').empty();
    $('#clusterLoading').show();
    $('#careersListView').hide();
    $('#clusterView').show();

    $.post('<?= base_url("cca/getCareerClusters") ?>', function(htmlResponse) {
        $('#clusterLoading').hide();
        $('#clusterGrid').html(htmlResponse);
    }).fail(function() {
        $('#clusterLoading').hide();
        $('#clusterGrid').html('<div class="col-12 text-center text-danger">Error loading categories.</div>');
    });
}

// 2. Show the Cluster Grid (Back Button)
window.showClusters = function() {
    $('#careersListView').hide();
    $('#clusterView').fadeIn();
}

// 3. Click Listener for Clusters (Load Careers List)
$(document).on('click', '.cluster-card', function() {
    let categoryNameRaw = $(this).attr('data-category'); 
    
    $('#clusterView').hide();
    $('#careersListGrid').html('<div class="col-12 text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></div>');
    $('#currentClusterTitle').text(categoryNameRaw);
    $('#careersListView').fadeIn();

    // Passing the exact string from the data attribute to the controller
    let payload = { category: categoryNameRaw };

    $.post('<?= base_url("cca/getCareersByCluster") ?>', payload, function(htmlResponse) {
        $('#careersListGrid').html(htmlResponse);
    }).fail(function() {
        $('#careersListGrid').html('<div class="col-12 text-center text-danger py-4">Server Error loading careers.</div>');
    });
});


// 4. Click Listener for Individual Careers (Modal Details)
    $(document).on('click', '.career-item', function() {
        let id = $(this).data('id');
        let title = $(this).data('title');
        
        $('#modalCareerTitle').text(title);
        $('#careerTableBody').html('<tr><td class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></td></tr>');
        new bootstrap.Modal(document.getElementById('careerDetailModal')).show();

        // Send to server to generate career.php HTML
        $.post('<?= base_url("cca/getCareerDetails") ?>', { id: id }, function(htmlResponse) {
            $('#careerTableBody').html(htmlResponse);
        }).fail(function() {
             $('#careerTableBody').html('<tr><td class="text-center text-danger py-4">Error loading details.</td></tr>');
        });
    });
    
    // --- CANDIDATE LIST LOGIC ---
    function loadCandidatesTable() {
        $.post('<?= base_url("cca/loadCandidateList") ?>', { [currentCsrfName]: currentCsrfHash }, function(htmlResponse) {
            $('#candidatesListContainer').html(htmlResponse);
        }).fail(function() {
            $('#candidatesListContainer').html('<div class="alert alert-danger">Error loading candidates.</div>');
        });
    }

    // Load Student Raw Scores into Modal
    $(document).on('click', '.view-scores-btn', function() {
        let id = $(this).data('id');
        let name = $(this).data('name');
        
        $('#scoreModalTitle').html('<i class="fas fa-chart-bar me-2"></i>' + escapeHTML(name) + ' - Raw Output');
        $('#scoresTableBody').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-muted"></i></div>');
        new bootstrap.Modal(document.getElementById('rawScoresModal')).show();

        $.post('<?= base_url("cca/getStudentRawScores") ?>', { user_id: id, [currentCsrfName]: currentCsrfHash }, function(html) {
            $('#scoresTableBody').html(html);
        }).fail(function() {
            $('#scoresTableBody').html('<div class="alert alert-danger m-3">Error fetching score records.</div>');
        });
    });

// --- ENHANCED REPORT MANAGEMENT LOGIC ---

    // 1. Open Remarks Modal
    $(document).on('click', '.open-remarks-btn', function() {
        let studentId = $(this).data('id');
        let studentName = $(this).data('name');
        
        $('#remarksStudentId').val(studentId);
        $('#remarksStudentName').text(studentName);
        $('#counselorRemarks').val('Loading...');
        
        new bootstrap.Modal(document.getElementById('remarksModal')).show();

        // Fetch existing remarks safely
        $.post('<?= base_url("cca/getReportMeta") ?>', { student_id: studentId, [currentCsrfName]: currentCsrfHash }, function(res) {
            if(res.status === 'success') {
                $('#counselorRemarks').val(res.remarks); // Safe insertion via jQuery .val()
                $('#reportStatus').val(res.report_status);
            }
        });
    });

    // 2. Save Remarks
    $('#saveRemarksBtn').click(function() {
        let btn = $(this);
        btn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);

        let payload = {
            student_id: $('#remarksStudentId').val(),
            status: $('#reportStatus').val(),
            remarks: $('#counselorRemarks').val(),
            [currentCsrfName]: currentCsrfHash
        };

        $.post('<?= base_url("cca/saveReportRemarks") ?>', payload, function(res) {
            btn.html('Save Details').prop('disabled', false);
            if(res.status === 'success') {
                $('#remarksModal').modal('hide');
                loadCompletedReports(); // Refresh the table to update status badges
            } else {
                alert(res.msg); // Will show throttling or auth errors
            }
        }).fail(function(xhr) {
            btn.html('Save Details').prop('disabled', false);
            if(xhr.status === 429) alert("Rate limit exceeded. Please wait.");
            else alert("Error saving data.");
        });
    });

    // 3. One-Click Email
    $(document).on('click', '.email-report-btn', function() {
        let btn = $(this);
        let studentId = btn.data('id');
        
        if(!confirm("Generate PDF and email directly to this student?")) return;

        btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);

        $.post('<?= base_url("cca/emailStudentReport") ?>', { student_id: studentId, [currentCsrfName]: currentCsrfHash }, function(res) {
            btn.html('<i class="fas fa-paper-plane"></i> Email').prop('disabled', false);
            alert(res.msg);
            if(res.status === 'success') loadCompletedReports(); // Refresh badges
        }).fail(function(xhr) {
            btn.html('<i class="fas fa-paper-plane"></i> Email').prop('disabled', false);
            if(xhr.status === 429) alert("DDoS Protection active: Too many emails sent. Please wait a minute.");
            else alert("Error sending email.");
        });
    });
    
    // --- ENTERPRISE COHORT ANALYTICS LOGIC ---
    let cohortChartInstances = {};

    function loadCohortAnalytics() {
        let level = $('#analyticsBatchSelect').val();
        $('#analyticsErrorArea').hide();
        $('#analyticsChartsArea').hide();
        
        let payload = { level: level, [currentCsrfName]: currentCsrfHash };

        $.post('<?= base_url("cca/getSchoolAnalytics") ?>', payload, function(res) {
            if(res.status === 'empty') {
                $('#analyticsErrorArea').text(res.msg).fadeIn();
                return;
            }
            if(res.status === 'error') { alert(res.msg); return; }

            $('#analyticsChartsArea').show();

            // Populate KPIs
            $('#kpiTotalStudents').text(res.total);
            let topMbtiType = Object.keys(res.mbti).length > 0 ? Object.keys(res.mbti)[0] : 'Pending Data';
            $('#kpiTopMbti').text(topMbtiType);

            // Destroy previous instances
            Object.keys(cohortChartInstances).forEach(key => { 
                if(cohortChartInstances[key]) cohortChartInstances[key].destroy(); 
            });
            
            // 0. SUBJECT SELECTION STREAMS (Doughnut Chart)
            let streamColors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#06b6d4'];
            cohortChartInstances['streams'] = new Chart(document.getElementById('aggStreamChart'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(res.streams),
                    datasets: [{
                        data: Object.values(res.streams),
                        backgroundColor: streamColors,
                        borderWidth: 2
                    }]
                },
                options: { 
                    maintainAspectRatio: false, 
                    cutout: '60%', 
                    plugins: { legend: { position: 'right' } } 
                }
            });
            
            // 1. RIASEC (Bar Chart)
            cohortChartInstances['riasec'] = new Chart(document.getElementById('aggRiasecChart'), {
                type: 'bar',
                data: {
                    labels: Object.keys(res.riasec),
                    datasets: [{ label: 'Avg %', data: Object.values(res.riasec), backgroundColor: '#3b82f6', borderRadius: 4 }]
                },
                options: { maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });

            // 2. MBTI (Bar Chart)
            cohortChartInstances['mbti'] = new Chart(document.getElementById('aggMbtiChart'), {
                type: 'bar',
                data: {
                    labels: Object.keys(res.mbti),
                    datasets: [{ label: 'Students', data: Object.values(res.mbti), backgroundColor: '#f59e0b', borderRadius: 4 }]
                },
                options: { indexAxis: 'y', maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });

            // 3. Aptitude (Radar)
            cohortChartInstances['aptitude'] = new Chart(document.getElementById('aggAptitudeChart'), {
                type: 'radar',
                data: {
                    labels: Object.keys(res.aptitude),
                    datasets: [{ label: 'Score %', data: Object.values(res.aptitude), backgroundColor: 'rgba(16, 185, 129, 0.2)', borderColor: '#10b981' }]
                },
                options: { maintainAspectRatio: false, scales: { r: { min: 0, max: 100 } } }
            });

            // 4. EQ (Polar Area)
            cohortChartInstances['eq'] = new Chart(document.getElementById('aggEqChart'), {
                type: 'polarArea',
                data: {
                    labels: Object.keys(res.eq),
                    datasets: [{ data: Object.values(res.eq), backgroundColor: ['#ef4444', '#f97316', '#eab308', '#10b981', '#3b82f6'] }]
                },
                options: { maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
            });

            // 5. Multiple Intelligences (Radar - FIXED KEYS)
            cohortChartInstances['mi'] = new Chart(document.getElementById('aggMiChart'), {
                type: 'radar',
                data: {
                    labels: Object.keys(res.mi),
                    datasets: [{ label: 'Score %', data: Object.values(res.mi), backgroundColor: 'rgba(6, 182, 212, 0.2)', borderColor: '#06b6d4' }]
                },
                options: { maintainAspectRatio: false, scales: { r: { min: 0, max: 100 } } }
            });

            // 6. VARK (Doughnut Chart)
            cohortChartInstances['vark'] = new Chart(document.getElementById('aggVarkChart'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(res.vark),
                    datasets: [{ data: Object.values(res.vark), backgroundColor: ['#3b82f6', '#eab308', '#ef4444', '#10b981'] }]
                },
                options: { maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'right' } } }
            });

            // 7. Motivators (Bar Chart)
            cohortChartInstances['motivators'] = new Chart(document.getElementById('aggMotivatorsChart'), {
                type: 'bar',
                data: {
                    labels: Object.keys(res.motivators),
                    datasets: [{ label: 'Avg Value', data: Object.values(res.motivators), backgroundColor: '#8b5cf6', borderRadius: 4 }]
                },
                options: { maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });

        }).fail(function(xhr) {
            if(xhr.status === 429) alert("DDoS Protection: Too many requests. Please wait.");
            else alert("Error fetching cohort data.");
        });
    }

    // Bind dropdown change
    $('#analyticsBatchSelect').change(function() { loadCohortAnalytics(); });
    
    // --- CRM WORKSPACE LOGIC ---

    // Open Modal & Load Data
    $(document).on('click', '.open-crm-btn', function() {
        let studentId = $(this).data('id');
        let studentName = $(this).data('name');
        
        $('#crmStudentId').val(studentId);
        $('#crmStudentName').text(studentName);
        
        // Clear inputs
        $('#newNoteText, #newTaskDesc, #newResTitle, #newResUrl').val('');
        $('#crmNotesList, #crmTasksList, #crmResourcesList').html('<div class="text-center mt-4"><i class="fas fa-spinner fa-spin text-muted"></i></div>');
        
        new bootstrap.Modal(document.getElementById('crmModal')).show();
        loadCrmData(studentId);
    });

    // Central Data Loader (With strict XSS Escaping via escapeHTML)
    function loadCrmData(studentId) {
        $.post('<?= base_url("cca/getCrmData") ?>', { student_id: studentId, [currentCsrfName]: currentCsrfHash }, function(res) {
            if(res.status !== 'success') return;

            // Render Notes
            let notesHtml = '';
            res.notes.forEach(n => {
                notesHtml += `<div class="bg-light p-2 mb-2 rounded border-start border-warning border-3"><small class="text-muted d-block mb-1" style="font-size: 0.7rem;">${escapeHTML(n.created_at)}</small><div class="small">${escapeHTML(n.note_text)}</div></div>`;
            });
            $('#crmNotesList').html(notesHtml || '<div class="text-muted small text-center mt-3">No private notes yet.</div>');

            // Render Tasks
            let tasksHtml = '';
            res.tasks.forEach(t => {
                let badge = t.status === 'completed' ? '<span class="badge bg-success ms-auto">Done</span>' : '<span class="badge bg-secondary ms-auto">Pending</span>';
                tasksHtml += `<div class="bg-light p-2 mb-2 rounded border-start border-success border-3 d-flex align-items-start"><div class="small">${escapeHTML(t.task_description)}</div>${badge}</div>`;
            });
            $('#crmTasksList').html(tasksHtml || '<div class="text-muted small text-center mt-3">No tasks assigned.</div>');

            // Render Resources
            let resHtml = '';
            res.resources.forEach(r => {
                resHtml += `<div class="bg-light p-2 mb-2 rounded border-start border-info border-3"><div class="small fw-bold text-dark">${escapeHTML(r.resource_title)}</div><a href="${escapeHTML(r.resource_url)}" target="_blank" class="small text-decoration-none">Open Link <i class="fas fa-external-link-alt ms-1" style="font-size:0.6rem;"></i></a></div>`;
            });
            $('#crmResourcesList').html(resHtml || '<div class="text-muted small text-center mt-3">No resources shared.</div>');
        });
    }

    // Generic CRM Save Function
    function saveCrmEntity(type, btnObj, dataPayload, clearSelector) {
        let btnText = btnObj.html();
        btnObj.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
        
        dataPayload.type = type;
        dataPayload.student_id = $('#crmStudentId').val();
        dataPayload[currentCsrfName] = currentCsrfHash;

        $.post('<?= base_url("cca/saveCrmItem") ?>', dataPayload, function(res) {
            btnObj.html(btnText).prop('disabled', false);
            if(res.status === 'success') {
                $(clearSelector).val('');
                loadCrmData(dataPayload.student_id);
            } else {
                alert(res.msg);
            }
        }).fail(function(xhr) {
            btnObj.html(btnText).prop('disabled', false);
            if(xhr.status === 429) alert("Rate limit exceeded. Please slow down.");
            else alert("Server error.");
        });
    }

    $('#saveNoteBtn').click(function() { saveCrmEntity('note', $(this), { text: $('#newNoteText').val() }, '#newNoteText'); });
    $('#saveTaskBtn').click(function() { saveCrmEntity('task', $(this), { description: $('#newTaskDesc').val() }, '#newTaskDesc'); });
    $('#saveResourceBtn').click(function() { saveCrmEntity('resource', $(this), { title: $('#newResTitle').val(), url: $('#newResUrl').val() }, '#newResTitle, #newResUrl'); });
</script>
</body>
</html>