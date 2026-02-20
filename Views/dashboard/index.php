<?php include 'layout/header.php'; ?>

<div class="container-fluid px-4 py-4">
    
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="dashboard-card stat-card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Assessments</p>
                        <h3 class="mb-0 fw-bold">1</h3>
                    </div>
                    <div class="stat-icon"><i class="fas fa-clipboard-check fa-lg"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="dashboard-card stat-card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Reports</p>
                        <h3 class="mb-0 fw-bold">1</h3>
                    </div>
                    <div class="stat-icon"><i class="fas fa-file-alt fa-lg"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="dashboard-card stat-card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Career Matches</p>
                        <h3 class="mb-0 fw-bold">0</h3>
                    </div>
                    <div class="stat-icon"><i class="fas fa-briefcase fa-lg"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="dashboard-card stat-card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Completion</p>
                        <h3 class="mb-0 fw-bold">100%</h3>
                    </div>
                    <div class="stat-icon"><i class="fas fa-chart-line fa-lg"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        
        <div class="col-lg-9">
            
            <div class="mb-4">
                <div class="section-header"><i class="fas fa-history me-2"></i> Recent Activity</div>
                <div class="dashboard-card overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 text-secondary small text-uppercase">Session ID</th>
                                    <th class="py-3 text-secondary small text-uppercase">Module</th>
                                    <th class="py-3 text-secondary small text-uppercase">Status</th>
                                    <th class="py-3 text-secondary small text-uppercase">Date</th>
                                    <th class="pe-4 py-3 text-end text-secondary small text-uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">#SES-8F95303</td>
                                    <td>Psychometric Series A</td>
                                    <td><span class="badge bg-success-subtle text-success border border-success px-2 rounded-pill">Completed</span></td>
                                    <td class="text-muted">Feb 14, 2026</td>
                                    <td class="pe-4 text-end">
                                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3">View Report</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">#SES-9A22104</td>
                                    <td>Aptitude Test</td>
                                    <td><span class="badge bg-warning-subtle text-warning border border-warning px-2 rounded-pill">Pending</span></td>
                                    <td class="text-muted">Feb 15, 2026</td>
                                    <td class="pe-4 text-end">
                                        <button class="btn btn-sm btn-primary rounded-pill px-3">Resume</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div>
                <div class="section-header"><i class="fas fa-bolt me-2"></i> Quick Actions</div>
                <div class="row g-3">
                    <div class="col-md-3 col-sm-6">
                        <div class="action-card">
                            <div class="action-icon"><i class="fas fa-play"></i></div>
                            <div>
                                <h6 class="mb-0 fw-bold">Start Test</h6>
                                <small class="text-muted">New assessment</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="action-card">
                            <div class="action-icon"><i class="fas fa-search"></i></div>
                            <div>
                                <h6 class="mb-0 fw-bold">Careers</h6>
                                <small class="text-muted">Explore jobs</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="action-card">
                            <div class="action-icon"><i class="fas fa-download"></i></div>
                            <div>
                                <h6 class="mb-0 fw-bold">Reports</h6>
                                <small class="text-muted">Download PDF</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="action-card">
                            <div class="action-icon"><i class="fas fa-user-cog"></i></div>
                            <div>
                                <h6 class="mb-0 fw-bold">Profile</h6>
                                <small class="text-muted">Edit details</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-lg-3">
            
            <div class="dashboard-card p-4 text-center mb-4">
                <div class="position-relative d-inline-block">
                    <img src="<?=base_url("assets/img/pharos.webp");?>" class="rounded-circle border border-3 border-light shadow-sm" width="80" height="80" alt="User">
                    <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-light rounded-circle"></span>
                </div>
                <h5 class="fw-bold mt-3 mb-1">Student Name</h5>
                <p class="text-muted small mb-3">Class 11 • LVIS Noida</p>
                <div class="d-grid">
                    <button class="btn btn-outline-dark btn-sm rounded-pill">View Full Profile</button>
                </div>
            </div>

            <div class="section-header"><i class="fas fa-lightbulb me-2"></i> Latest Insights</div>
            
            <div class="dashboard-card highlight-item p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-uppercase text-muted fw-bold">Personality</small>
                    <i class="fas fa-fingerprint text-primary opacity-50"></i>
                </div>
                <h2 class="mb-0 fw-bold text-dark">ISTJ</h2>
                <small class="text-muted">The Logistician</small>
            </div>

            <div class="dashboard-card highlight-item p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-uppercase text-muted fw-bold">Emotional EQ</small>
                    <i class="fas fa-heart text-danger opacity-50"></i>
                </div>
                <h2 class="mb-0 fw-bold text-dark">74.5%</h2>
                <div class="progress mt-2" style="height: 4px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 74.5%"></div>
                </div>
            </div>

            <div class="dashboard-card highlight-item p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-uppercase text-muted fw-bold">IQ Estimate</small>
                    <i class="fas fa-brain text-warning opacity-50"></i>
                </div>
                <h2 class="mb-0 fw-bold text-dark">92</h2>
                <small class="text-muted">Average Range</small>
            </div>

        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>