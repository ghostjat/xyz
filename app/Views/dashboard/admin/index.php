<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Control Panel | Pharos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?= csrf_meta();?>
    <style>
        body { background-color: #f1f5f9; color: #334155; font-size: 0.85rem; }
        .sidebar { height: 100vh; background: #0f172a; color: white; padding-top: 20px; position: fixed; width: 250px; }
        .sidebar a { color: #cbd5e1; text-decoration: none; padding: 15px 20px; display: block; font-weight: 600; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: #1e293b; color: white; border-left: 4px solid #3d8c83; }
        .main-content { margin-left: 250px; padding: 30px; }
        .stat-card { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-left: 5px solid #3d8c83; }
        .table-card { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .tab-pane { display: none; }
        .tab-pane.active { display: block; animation: fadeIn 0.4s; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="text-center mb-4 px-3">
        <h4 class="fw-bold text-white mb-0">Pharos Admin</h4>
        <small class="text-muted">System Management</small>
    </div>
    <a href="#" class="nav-link active" data-target="dashboard"><i class="fas fa-chart-line me-2"></i> Performance</a>
    <a href="#" class="nav-link" data-target="users"><i class="fas fa-users me-2"></i> Manage Users</a>
    <?php if($role === 'admin'): ?>
    <a href="#" class="nav-link" data-target="schools"><i class="fas fa-university me-2"></i> Partner Schools</a>
    <a href="<?= base_url('admin/simulator') ?>"><i class="fas fa-brain me-2"></i> AI Career Simulator</a>
    <?php endif; ?>
    <a href="#" class="nav-link" data-target="analytics"><i class="fas fa-chart-pie me-2"></i> Cohort Analytics</a>
    <a href="#" class="nav-link" data-target="appointments"><i class="fas fa-calendar-check me-2"></i> Appointments</a>
    <a href="#" class="nav-link" data-target="questions"><i class="fas fa-list me-2"></i> Question Bank</a>
    <a href="#" class="nav-link" data-target="reports"><i class="fas fa-file-pdf me-2"></i> Review Reports</a>
    <a href="<?= base_url('logout') ?>" class="mt-5 text-danger"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
</div>

<div class="main-content">
    
    <div id="dashboard" class="tab-pane active">
        <h3 class="fw-bold mb-4">Platform Performance</h3>
        <div class="row g-4">
            <div class="col-md-3"><div class="stat-card"><p class="text-muted small fw-bold text-uppercase mb-1">Total Users</p><h2 class="mb-0 fw-bold"><?= $stats['total_users'] ?></h2></div></div>
            <div class="col-md-3"><div class="stat-card"><p class="text-muted small fw-bold text-uppercase mb-1">Tests Completed</p><h2 class="mb-0 fw-bold"><?= $stats['total_tests_taken'] ?></h2></div></div>
            <div class="col-md-3"><div class="stat-card border-warning"><p class="text-muted small fw-bold text-uppercase mb-1">Active Students</p><h2 class="mb-0 fw-bold"><?= $stats['active_students'] ?></h2></div></div>
            <?php if($role === 'admin'): ?>
            <div class="col-md-3"><div class="stat-card border-info"><p class="text-muted small fw-bold text-uppercase mb-1">Partner Schools</p><h2 class="mb-0 fw-bold"><?= $stats['total_schools'] ?></h2></div></div>
            <?php endif; ?>
        </div>
    </div>

    <div id="users" class="tab-pane">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">User Management</h3>
            <div>
                <button class="btn btn-success me-2" onclick="bulkImportModal.show()"><i class="fas fa-file-csv me-1"></i> Bulk CSV Import</button>
                <button class="btn btn-primary" onclick="openUserModal()"><i class="fas fa-plus me-1"></i> Add User</button>
            </div>
        </div>
        <div class="table-card table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light"><tr><th>Name</th><th>Email</th><th>School</th><th>Role</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach($users as $u): ?>
                    <tr>
                        <td class="fw-bold"><?= esc($u['full_name']) ?></td>
                        <td><?= esc($u['email']) ?></td>
                        <td><span class="badge bg-light text-dark border"><?= $u['school_id'] ? 'Partner School' : 'Independent' ?></span></td>
                        <td><span class="badge bg-secondary"><?= strtoupper(esc($u['category'])) ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick='openUserModal(<?= json_encode($u) ?>)'><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteRecord('deleteuser', <?= $u['id'] ?>)"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if($role === 'admin'): ?>
    <div id="schools" class="tab-pane">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">B2B Partner Schools</h3>
            <button class="btn btn-primary" onclick="openSchoolModal()"><i class="fas fa-plus me-1"></i> Add School</button>
        </div>
        <div class="table-card table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light"><tr><th>School Name</th><th>Contact Person</th><th>Contact Email</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach($schools as $s): ?>
                    <tr>
                        <td class="fw-bold"><?= esc($s['name']) ?></td>
                        <td><?= esc($s['contact_person']) ?></td>
                        <td><?= esc($s['contact_email']) ?></td>
                        <td><button class="btn btn-sm btn-outline-primary" onclick='openSchoolModal(<?= json_encode($s) ?>)'><i class="fas fa-edit"></i></button></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <div id="reports" class="tab-pane">
        <h3 class="fw-bold mb-4">Student Test Results & Feedback</h3>
        <div class="table-card table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light"><tr><th>Student</th><th>School</th><th>Test Module</th><th>Result</th><th>Date</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach($reports as $r): ?>
                    <tr>
                        <td class="fw-bold"><?= esc($r['full_name']) ?><br><small class="text-muted"><?= esc($r['email']) ?></small></td>
                        <td><small class="text-muted"><?= esc($r['school_name'] ?? 'N/A') ?></small></td>
                        <td><span class="badge bg-dark text-uppercase"><?= esc($r['module_code']) ?></span></td>
                        <td class="fw-bold text-primary"><?= esc($r['primary_trait']) ?></td>
                        <td><?= date('M d, Y', strtotime($r['completed_at'])) ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="openCommentModal(<?= $r['id'] ?>, '<?= esc(addslashes($r['full_name'])) ?>', '<?= strtoupper($r['module_code']) ?>', `<?= htmlspecialchars($r['admin_feedback'], ENT_QUOTES) ?>`)"><i class="fas fa-comment-dots me-1"></i> Note</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteRecord('deletereport', <?= $r['id'] ?>)"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="questions" class="tab-pane">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">Question Bank</h3>
            <button class="btn btn-primary" onclick="openQuestionModal()"><i class="fas fa-plus me-1"></i> Add Question</button>
        </div>
        <div class="table-card table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light"><tr><th>Module</th><th>Category</th><th>Question Text</th><th>Type</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach($questions as $q): ?>
                    <tr>
                        <td><span class="badge bg-dark text-uppercase"><?= esc($q['module_code']) ?></span></td>
                        <td><?= esc($q['category']) ?></td>
                        <td><small><?= esc($q['question_text']) ?></small></td>
                        <td><?= esc($q['input_type']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick='openQuestionModal(<?= json_encode($q, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteRecord('deletequestion', <?= $q['id'] ?>)"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div id="analytics" class="tab-pane">
        <h3 class="fw-bold mb-4">Cohort Aggregate Report</h3>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="stat-card border-danger text-center">
                    <h5 class="text-muted text-uppercase mb-3">Average Emotional Intelligence</h5>
                    <div class="display-4 fw-bold text-danger"><?php //= $cohortStats['avg_eq'] ?></div>
                    <small class="text-muted">Across <?php //= $cohortStats['eq_count'] ?> tested students</small>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="table-card">
                    <h5 class="fw-bold mb-3">Dominant Personality Types (MBTI)</h5>
                    <div class="row">
                        <?php //foreach(array_slice($cohortStats['mbti_breakdown'], 0, 6) as $trait => $count): ?>
                        <div class="col-4 mb-3 text-center">
                            <h3 class="mb-0 text-primary fw-bold"><?php // $trait ?></h3>
                            <span class="badge bg-secondary"><?php // $count ?> Students</span>
                        </div>
                        <?php //endforeach; ?>
                        <?php //if(empty($cohortStats['mbti_breakdown'])): ?>
                            <p class="text-muted">Not enough MBTI data collected yet.</p>
                        <?php //endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="appointments" class="tab-pane">
        <h3 class="fw-bold mb-4">Counseling Appointments</h3>
        <div class="table-card table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light"><tr><th>Student</th><th>Topic</th><th>Requested Time</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php //foreach($appointments as $apt): ?>
                    <tr>
                        <td class="fw-bold"><?php //= esc($apt['full_name']) ?></td>
                        <td><?php //= esc($apt['topic']) ?></td>
                        <td><?php //= date('M d, Y - h:i A', strtotime($apt['preferred_datetime'])) ?></td>
                        <td>
                            <?php 
                                //$bg = 'warning';
                                //if($apt['status'] == 'approved') $bg = 'success';
                                //if($apt['status'] == 'cancelled') $bg = 'danger';
                            ?>
                            <span class="badge bg-<?php //= $bg ?> text-uppercase"><?php //= $apt['status'] ?></span>
                        </td>
                        <td>
                            <?php //if($apt['status'] == 'pending'): ?>
                            <button class="btn btn-sm btn-success" onclick="updateApt(<?php //= $apt['id'] ?>, 'approved')"><i class="fas fa-check"></i> Accept</button>
                            <button class="btn btn-sm btn-danger" onclick="updateApt(<?php //= $apt['id'] ?>, 'cancelled')"><i class="fas fa-times"></i> Reject</button>
                            <?php //else: ?>
                            <span class="text-muted small">Processed</span>
                            <?php //endif; ?>
                        </td>
                    </tr>
                    <?php //endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<div class="modal fade" id="bulkImportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="bulkImportForm" enctype="multipart/form-data">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-file-csv me-2"></i> Bulk Import Students</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning small">
                        <strong>CSV Format Required:</strong><br>
                        Row 1 (Headers): Full Name, Email, Password, Phone<br>
                        Row 2+: John Doe, john@test.com, password123, 9876543210
                    </div>
                    <?php if($role === 'admin'): ?>
                    <div class="mb-3">
                        <label>Assign to School (Optional)</label>
                        <select name="school_id" class="form-select">
                            <option value="">-- No School (B2C) --</option>
                            <?php foreach($schools as $s): ?><option value="<?= $s['id'] ?>"><?= esc($s['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label>Upload CSV File</label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-success">Upload & Import</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="schoolModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="schoolForm">
                <div class="modal-header bg-dark text-white"><h5 class="modal-title" id="schoolModalTitle">Add School</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="s_id">
                    <div class="mb-3"><label>School Name</label><input type="text" name="name" id="s_name" class="form-control" required></div>
                    <div class="mb-3"><label>Contact Person</label><input type="text" name="contact_person" id="s_contact" class="form-control"></div>
                    <div class="mb-3"><label>Contact Email</label><input type="email" name="contact_email" id="s_email" class="form-control"></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Save School</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="userForm">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="userModalTitle">Add User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="u_id">
                    <div class="mb-3"><label>Full Name</label><input type="text" name="full_name" id="u_name" class="form-control" required></div>
                    <div class="mb-3"><label>Email</label><input type="email" name="email" id="u_email" class="form-control" required></div>
                    
                    <?php if($role === 'admin'): ?>
                    <div class="mb-3"><label>School (Optional)</label>
                        <select name="school_id" id="u_school" class="form-select">
                            <option value="">-- Independent --</option>
                            <?php foreach($schools as $s): ?><option value="<?= $s['id'] ?>"><?= esc($s['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="mb-3"><label>Role</label>
                        <select name="category" id="u_role" class="form-select" required>
                            <option value="student">Student</option><option value="counselor">Counselor</option><option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-3"><label>Password <small class="text-muted">(Leave blank to keep)</small></label><input type="password" name="password" id="u_password" class="form-control"></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Save User</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="questionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="questionForm">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="questionModalTitle">Add Question</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="q_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Module Code</label>
                            <select name="module_code" id="q_module" class="form-select" required onchange="fetchCategories(this.value)">
                                <option value="">Select Module</option>
                                <?php 
                                // Loop through distinct modules passed from your controller
                                if(isset($modules)): 
                                    foreach($modules as $mod): 
                                ?>
                                    <option value="<?= esc($mod['module_code']) ?>"><?= esc($mod['module_code']) ?></option>
                                <?php 
                                    endforeach; 
                                endif; 
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Category</label>
                            <select name="category" id="q_category" class="form-select" required>
                                <option value="">Select Category</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Question Text</label>
                        <textarea name="question_text" id="q_text" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Input Type</label>
                            <select name="input_type" id="q_type" class="form-control" required>
                                <option value="likert_5">Likert 5 (Strongly Disagree to Agree)</option>
                                <option value="likert_3">Likert 3 (Dislike, Neutral, Like)</option>
                                <option value="forced_choice">Forced Choice (A / B)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Display Order</label>
                            <input type="number" name="display_order" id="q_order" class="form-control" value="0">
                        </div>
                    </div>
                    
                    <div class="mb-3 p-3 border rounded bg-light">
                        <label class="font-weight-bold text-primary">Question Options (Auto-JSON Builder)</label>
                        <p class="text-muted small mb-3">Enter the option label and its value/weight. The JSON will be generated automatically.</p>
                        
                        <input type="hidden" name="options_json" id="q_json" value="">

                        <div id="dynamic_options_container"></div>

                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addOptionRow()">
                            + Add Option Row
                        </button>
                        
                        <div class="mt-3 p-2 border rounded" style="background: #e9ecef;">
                            <small class="text-muted fw-bold">Generated JSON Preview:</small><br>
                            <code id="json_preview" class="text-dark">{}</code>
                        </div>
                    </div>
                    </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-dark">Save Question</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="commentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="commentForm">
                <div class="modal-header bg-dark text-white"><h5 class="modal-title">Counselor Note</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="alert alert-info py-2">Student: <strong id="modalStudentName"></strong><br>Module: <strong id="modalModule"></strong></div>
                    <input type="hidden" name="result_id" id="modalResultId">
                    <div class="mb-3"><label class="form-label fw-bold">Specific Feedback</label><textarea class="form-control" name="comment" id="modalCommentText" rows="5"></textarea></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Save to Report</button></div>
            </form>
        </div>
    </div>
</div>

<script>
    // Navigation Logic
    $('.nav-link').click(function(e) {
        if($(this).attr('href') !== '#') return;
        e.preventDefault();
        $('.nav-link').removeClass('active'); $(this).addClass('active');
        $('.tab-pane').removeClass('active'); $('#' + $(this).data('target')).addClass('active');
    });

    // ========================================== JS MODAL HANDLERS ==========================================
    const userModal = new bootstrap.Modal(document.getElementById('userModal'));
    const questionModal = new bootstrap.Modal(document.getElementById('questionModal'));
    const commentModal = new bootstrap.Modal(document.getElementById('commentModal'));
    const bulkImportModal = new bootstrap.Modal(document.getElementById('bulkImportModal'));
    // Only init School modal if element exists (Admin Only)
    const schoolModalEl = document.getElementById('schoolModal');
    const schoolModal = schoolModalEl ? new bootstrap.Modal(schoolModalEl) : null;

    function openUserModal(user = null) {
        $('#userForm')[0].reset();
        if(user) {
            $('#userModalTitle').text('Edit User');
            $('#u_id').val(user.id); $('#u_name').val(user.full_name); $('#u_email').val(user.email); 
            $('#u_role').val(user.category); $('#u_school').val(user.school_id);
            $('#u_password').prop('required', false);
        } else {
            $('#userModalTitle').text('Add User');
            $('#u_id').val(''); $('#u_password').prop('required', true);
        }
        userModal.show();
    }

    function openSchoolModal(school = null) {
        if(!schoolModal) return;
        $('#schoolForm')[0].reset();
        if(school) {
            $('#schoolModalTitle').text('Edit School');
            $('#s_id').val(school.id); $('#s_name').val(school.name); 
            $('#s_contact').val(school.contact_person); $('#s_email').val(school.contact_email);
        } else {
            $('#schoolModalTitle').text('Add School');
            $('#s_id').val('');
        }
        schoolModal.show();
    }

function openQuestionModal(q = null) {
        $('#questionForm')[0].reset();
        
        if(q) {
            $('#questionModalTitle').text('Edit Question');
            $('#q_id').val(q.id); 
            
            // Set the module dropdown
            $('#q_module').val(q.module_code); 
            
            // --> TRIGGER AJAX TO LOAD CATEGORIES AND PRE-SELECT <--
            fetchCategories(q.module_code, q.category);
            
            $('#q_text').val(q.question_text); 
            $('#q_type').val(q.input_type); 
            $('#q_order').val(q.display_order); 
            $('#q_json').val(q.options_json);
            
            // Trigger the visual JSON builder
            loadOptionsUI(q.options_json);
            
        } else {
            $('#questionModalTitle').text('Add Question');
            $('#q_id').val('');
            $('#q_json').val('');
            
            // Reset Category dropdown
            $('#q_category').empty().append('<option value="">Select Category</option>');
            
            // Trigger a blank row for a new question
            loadOptionsUI('');
        }
        
        questionModal.show();
    }

    function openCommentModal(resultId, studentName, module, existingComment) {
        $('#modalResultId').val(resultId); $('#modalStudentName').text(studentName); $('#modalModule').text(module); $('#modalCommentText').val(existingComment);
        commentModal.show();
    }

    // ========================================== AJAX SUBMISSIONS ==========================================
    function handleAjaxSubmit(formId, url) {
        $(formId).submit(function(e) {
            e.preventDefault();
            let btn = $(this).find('button[type=submit]');
            btn.prop('disabled', true).text('Saving...');
            
            // Handle Multipart for CSV
            let isMultipart = $(this).attr('enctype') === 'multipart/form-data';
            let ajaxConfig = {
                url: url, type: 'POST', data: isMultipart ? new FormData(this) : $(this).serialize(),
                success: function(res) {
                    if(res.status === 'success') { location.reload(); } else { alert(res.msg); btn.prop('disabled', false).text('Save'); }
                },
                error: function() { alert('Server error occurred.'); btn.prop('disabled', false).text('Save'); }
            };

            if(isMultipart) {
                ajaxConfig.processData = false;
                ajaxConfig.contentType = false;
            }

            $.ajax(ajaxConfig);
        });
    }

    handleAjaxSubmit('#userForm', '<?= base_url("admin/saveuser") ?>');
    handleAjaxSubmit('#questionForm', '<?= base_url("admin/savequestion") ?>');
    handleAjaxSubmit('#commentForm', '<?= base_url("admin/saveReportComment") ?>');
    handleAjaxSubmit('#schoolForm', '<?= base_url("admin/saveschool") ?>');
    handleAjaxSubmit('#bulkImportForm', '<?= base_url("admin/bulkImportUsers") ?>');

    // ========================================== AJAX DELETION ==========================================
    function deleteRecord(actionUrl, id) {
        if(confirm('Are you sure you want to permanently delete this record? This action cannot be undone.')) {
            $.post('<?= base_url("admin") ?>/' + actionUrl + '/' + id, function(res) {
                if(res.status === 'success') location.reload(); else alert('Error deleting record.');
            }, 'json');
        }
    }
    function updateApt(id, status) {
            if(confirm('Are you sure you want to mark this appointment as ' + status + '?')) {
                $.post('<?= base_url("admin/updateAppointmentStatus") ?>', {id: id, status: status}, function(res) {
                    location.reload();
                });
            }
        }
// =========================================================
    // DYNAMIC JSON BUILDER LOGIC
    // =========================================================

    // 1. Add a new row to the UI
    function addOptionRow(key = '', value = '') {
        const container = document.getElementById('dynamic_options_container');
        const row = document.createElement('div');
        
        row.className = 'd-flex align-items-center mb-2 option-row';
        row.style.gap = '10px';
        
        row.innerHTML = `
            <input type="text" class="form-control opt-key" placeholder="Option Label (e.g., Strongly Agree)" value="${key}" oninput="updateOptionsJSON()">
            <input type="text" class="form-control opt-val" placeholder="Weight/Value (e.g., 5 or R)" value="${value}" oninput="updateOptionsJSON()">
            <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove(); updateOptionsJSON();">X</button>
        `;
        
        container.appendChild(row);
        updateOptionsJSON();
    }

    // 2. Read rows, build JSON, and update the hidden input
    function updateOptionsJSON() {
        const keys = document.querySelectorAll('.opt-key');
        const vals = document.querySelectorAll('.opt-val');
        let jsonObj = {};
        
        for (let i = 0; i < keys.length; i++) {
            let k = keys[i].value.trim();
            let v = vals[i].value.trim();
            
            if (k !== '') {
                // If it's a number, save as integer. Otherwise save as string.
                if (v !== '' && !isNaN(v)) {
                    v = Number(v);
                }
                jsonObj[k] = v;
            }
        }
        
        const jsonString = Object.keys(jsonObj).length > 0 ? JSON.stringify(jsonObj) : '';
        
        // Update the hidden input that gets sent to the backend
        document.getElementById('q_json').value = jsonString;
        
        // Update the visual preview
        document.getElementById('json_preview').innerText = jsonString || '{}';
    }

    // 3. Helper function to load existing JSON into the UI (Use this when EDITING a question)
    function loadOptionsUI(jsonString) {
        const container = document.getElementById('dynamic_options_container');
        container.innerHTML = ''; // Clear existing rows
        
        if (jsonString && jsonString.trim() !== '') {
            try {
                let parsed = JSON.parse(jsonString);
                for (const [key, value] of Object.entries(parsed)) {
                    addOptionRow(key, value);
                }
            } catch (e) {
                console.error("Invalid JSON detected in database.", e);
                addOptionRow(); // Fallback to 1 empty row
            }
        } else {
            addOptionRow(); // 1 empty row for new questions
        }
    }

    // Reset UI when the modal is closed or opened for a "New" question
    document.getElementById('questionModal').addEventListener('show.bs.modal', function (event) {
        // Only add a blank row if the container is totally empty (e.g., clicking "Add New Question")
        if (document.getElementById('dynamic_options_container').innerHTML.trim() === '') {
            loadOptionsUI('');
        }
    });
    
    document.getElementById('questionModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('dynamic_options_container').innerHTML = '';
        document.getElementById('q_json').value = '';
        document.getElementById('json_preview').innerText = '{}';
    });
    
    // Fetches categories via AJAX based on the selected module
    function fetchCategories(moduleCode, selectedCategory = null) {
        let categorySelect = $('#q_category');
        
        // Temporarily show loading text while waiting for the server
        categorySelect.empty().append('<option value="">Loading...</option>');

        if (!moduleCode) {
            categorySelect.empty().append('<option value="">Select Category</option>');
            return;
        }

        $.post('<?= base_url("admin/getCategoriesByModule") ?>', { module_code: moduleCode }, function(res) {
            // Clear the loading text
            categorySelect.empty().append('<option value="">Select Category</option>');
            
            if (res.status === 'success' && res.data && res.data.length > 0) {
                // Loop through the returned array
                res.data.forEach(function(cat) {
                    // Check if we are editing and need to auto-select this option
                    let isSelected = (selectedCategory === cat) ? 'selected' : '';
                    
                    // Inject the option into the select dropdown
                    categorySelect.append(`<option value="${cat}" ${isSelected}>${cat}</option>`);
                });
            }
        }, 'json').fail(function() {
            // Failsafe in case of server error
            categorySelect.empty().append('<option value="">Error loading</option>');
        });
    }
</script>

</body>
</html>