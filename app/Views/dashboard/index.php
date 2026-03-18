<?php include 'layout/header.php'; ?>

<div class="container-fluid px-4 py-4">

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="dashboard-card stat-card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Tests Completed</p>
                        <h3 class="mb-0 fw-bold"><?= $completed_count; ?> / <?= count($modules); ?></h3>
                    </div>
                    <div class="stat-icon"><i class="fas fa-clipboard-check fa-lg text-primary"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="dashboard-card stat-card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Completion</p>
                        <h3 class="mb-0 fw-bold"><?= $completion_rate; ?>%</h3>
                    </div>
                    <div class="stat-icon"><i class="fas fa-chart-pie fa-lg text-success"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="dashboard-card stat-card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Official Report</p>
                        <h3 class="mb-0 fw-bold">
                            <?php if ($completion_rate == 100): ?>
                                Ready
                            <?php else: ?>
                                Locked
                            <?php endif; ?>
                        </h3>
                    </div>
                    <div class="stat-icon"><i class="fas fa-file-pdf fa-lg text-danger"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-9">

            <div class="mb-4">
                <div class="section-header"><i class="fas fa-list-check me-2"></i> Your Assessments</div>
                <div class="dashboard-card overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 text-secondary small text-uppercase">Module</th>
                                    <th class="py-3 text-secondary small text-uppercase">Status</th>
                                    <th class="py-3 text-secondary small text-uppercase">Date Completed</th>
                                    <th class="pe-4 py-3 text-end text-secondary small text-uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($modules as $code => $mod): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark">
                                            <i class="<?= $mod['icon'] ?> text-muted me-2"></i> <?= $mod['name'] ?>
                                        </td>
                                        <td><span class="badge <?= $mod['badge'] ?> px-2 rounded-pill"><?= ucfirst($mod['status']) ?></span></td>
                                        <td class="text-muted"><?= $mod['date'] ?></td>
                                        <td class="pe-4 text-end">
                                            <a href="<?= $mod['action_url'] ?>" 
                                               class="btn btn-sm <?= $mod['btn_class'] ?> rounded-pill px-3" 
                                               <?= $mod['status'] == 'completed' ? 'tabindex="-1" aria-disabled="true" style="pointer-events: none; opacity: 0.6;"' : '' ?>>
                                                   <?= $mod['action_text'] ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div>
                <div class="section-header"><i class="fas fa-bolt me-2"></i> Quick Actions</div>
                <div class="row g-3">
                    <div class="col-md-4 col-sm-6">
                        <a href="#" class="text-decoration-none text-dark" onclick="new bootstrap.Modal(document.getElementById('bookingModal')).show(); return false;">
                            <div class="action-card">
                                <div class="action-icon bg-info text-white"><i class="fas fa-calendar-alt"></i></div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Book Counseling</h6>
                                    <small class="text-muted">Request 1-on-1 Session</small>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <?php if (!$hasPaid): ?>
                        <div class="col-12 mt-3">
                            <div class="alert alert-warning border border-warning shadow-sm">
                                <i class="fas fa-lock me-2"></i> Your assessments and reports are locked. 
                                <a href="<?= base_url('payment') ?>" class="alert-link text-decoration-underline">Please complete your payment</a> to start the tests.
                            </div>
                        </div>
                    <?php elseif ($completion_rate == 100): ?>
                        <div class="col-md-4 col-sm-6">
                            <a href="<?= base_url('report/viewReport') ?>" class="text-decoration-none text-dark">
                                <div class="action-card">
                                    <div class="action-icon bg-success text-white"><i class="fas fa-chart-line"></i></div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">Deep Analysis</h6>
                                        <small class="text-muted">View Career Clusters</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4 col-sm-6">
                            <a href="<?= base_url('report/downloadPdf') ?>" class="text-decoration-none text-dark">
                                <div class="action-card">
                                    <div class="action-icon bg-danger text-white"><i class="fas fa-file-pdf"></i></div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">Download Report</h6>
                                        <small class="text-muted">Official Dossier</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="col-12 mt-3">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i> Please complete all pending assessments to unlock your final career reports and deep analysis.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <div class="col-lg-3" style="margin-top: -100px !important;">
            <div class="dashboard-card p-4 text-center mb-4" style="border-left: 4px solid #8e44ad;">
                <div class="position-relative d-inline-block">
                    <i class="fa fa-user fa-2x"></i>
                </div>
                <h5 class="fw-bold mt-3 mb-1"><?= esc($user['full_name'] ?? 'Student'); ?></h5>
                <p class="text-muted small mb-3"><?= esc($user['school_name'] ?? 'Unregistered School'); ?> • <?= esc($user['educational_level'] ?? 'N/A'); ?></p>
            </div>

            <div class="section-header"><i class="fas fa-lightbulb me-2"></i> Psychometric Insights</div>

            <div class="dashboard-card highlight-item p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-uppercase text-muted fw-bold">Personality</small>
                    <i class="fas fa-fingerprint text-primary opacity-50"></i>
                </div>
                <h2 class="mb-0 fw-bold text-dark text-truncate" style="font-size: 1.3rem;"><?= $insights['mbti'] ?? 'Pending' ?></h2>
            </div>

            <div class="dashboard-card highlight-item p-3 mb-3" style="border-left: 4px solid #8e44ad;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-uppercase text-muted fw-bold">Emotional EQ</small>
                    <i class="fas fa-heart text-danger opacity-50"></i>
                </div>
                <h2 class="mb-0 fw-bold text-dark text-truncate" style="font-size: 1.3rem;"><?= $insights['eq'] ?? 0 ?>%</h2>
                <div class="progress mt-2" style="height: 4px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= $insights['eq'] ?? 0 ?>%"></div>
                </div>
            </div>

            <div class="dashboard-card highlight-item p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-uppercase text-muted fw-bold">IQ Estimate</small>
                    <i class="fas fa-brain text-warning opacity-50"></i>
                </div>
                <h2 class="mb-0 fw-bold text-dark text-truncate" style="font-size: 1.3rem;"><?= $insights['iq'] ?? 'Pending' ?></h2>
            </div>

            <div class="dashboard-card highlight-item p-3 mb-3" style="border-left: 4px solid #8e44ad;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-uppercase text-muted fw-bold">Learning Style</small>
                    <i class="fas fa-book-open text-info opacity-50" style="color: #8e44ad !important;"></i>
                </div>
                <h2 class="mb-0 fw-bold text-dark text-truncate" style="font-size: 1.3rem;"><?= $insights['vark'] ?? 'Pending' ?></h2>
            </div>

            <div class="dashboard-card highlight-item p-3 mb-3" style="border-left: 4px solid #c0392b;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-uppercase text-muted fw-bold">Top Motivator</small>
                    <i class="fas fa-bullseye opacity-50" style="color: #c0392b;"></i>
                </div>
                <h2 class="mb-0 fw-bold text-dark text-truncate" style="font-size: 1.3rem;" title="<?= $insights['motivator'] ?? 'Pending' ?>">
                    <?= $insights['motivator'] ?? 'Pending' ?>
                </h2>
            </div>

        </div>
    </div>
</div>
<div class="modal fade" id="bookingModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form id="bookingForm" action="<?= base_url('appointment/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header bg-info text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-calendar-alt me-2"></i> Request Counseling Session</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    
                    <div id="bookingAlert" class="alert" style="display: none; font-size: 0.9rem; font-weight: 600;"></div>

                    <div class="mb-4">
                        <label class="form-label text-muted fw-bold small text-uppercase">Preferred Date & Time</label>
                        <input type="datetime-local" name="preferred_datetime" id="modalPreferredDatetime" class="form-control form-control-lg bg-light" required>
                        <div class="form-text mt-2"><i class="fas fa-clock text-info me-1"></i> All sessions are 30 minutes long.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted fw-bold small text-uppercase">Discussion Topic</label>
                        <select name="topic" class="form-select form-select-lg bg-light" required>
                            <option value="" disabled selected>Select a primary focus...</option>
                            <option value="Report Decoding">Psychometric Report Decoding</option>
                            <option value="Stream Selection">Class 11 Stream Selection</option>
                            <option value="College Shortlisting">College & Course Shortlisting</option>
                            <option value="General Career Guidance">General Career Guidance</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-outline-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="submitBookingBtn" class="btn btn-info text-white fw-bold px-4 shadow-sm">
                        Submit Request <i class="fas fa-paper-plane ms-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php if (!$hasPaid): ?>
    <div class="modal fade" id="paymentRequiredModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center border-0 shadow-lg">
                <div class="modal-header bg-warning text-dark justify-content-center border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-exclamation-triangle me-2"></i> Payment Required</h5>
                </div>
                <div class="modal-body p-5">
                    <div class="mb-4">
                        <i class="fas fa-lock fa-4x text-muted"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Unlock Your Assessments</h4>
                    <p class="text-muted mb-4">You need to complete the assessment fee payment to unlock the psychometric tests and generate your personalized career reports.</p>
                    
                    <a href="<?= base_url('payment') ?>" class="btn btn-primary btn-lg rounded-pill px-5 shadow">
                        <i class="fas fa-credit-card me-2"></i> Pay Now
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Automatically show the modal if the user hasn't paid
            var paymentModal = new bootstrap.Modal(document.getElementById('paymentRequiredModal'));
            paymentModal.show();
        });
    </script>
    <?php endif; ?>

<?php include 'layout/footer.php'; ?>