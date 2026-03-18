<!DOCTYPE html>
<html lang="en">
<head>
    <title>AI Career Simulator | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f1f5f9; color: #334155; font-size: 0.85rem; }
        .sidebar { height: 100vh; background: #0f172a; color: white; padding-top: 20px; position: fixed; width: 250px; }
        .sidebar a { color: #cbd5e1; text-decoration: none; padding: 15px 20px; display: block; font-weight: 600; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: #1e293b; color: white; border-left: 4px solid #3d8c83; }
        .main-content { margin-left: 250px; padding: 20px; }
        .card { border-radius: 6px; border: 1px solid #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.05); margin-bottom: 0; }
        .card-header { background-color: #ffffff; padding: 0.4rem 0.75rem; font-size: 0.8rem; font-weight: 700; border-bottom: 1px solid #e2e8f0; }
        .chart-container { position: relative; height: 180px; width: 100%; padding: 5px; }
        .form-label { font-weight: 600; font-size: 0.75rem; margin-bottom: 0.1rem; color: #475569; }
        .badge-custom { font-size: 0.75rem; padding: 0.35em 0.65em; }
        /* Search dropdown absolute positioning fix */
        .search-wrapper { position: relative; width: 280px; }
        #searchResults { position: absolute; top: 100%; left: 0; right: 0; z-index: 1050; max-height: 300px; overflow-y: auto; }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="text-center mb-4 px-3">
        <h4 class="fw-bold text-white mb-0">Pharos Counselor</h4>
        <small class="text-muted">System Management</small>
    </div>
    <a href="#" class="nav-link" data-target="analytics"><i class="fas fa-chart-pie me-2"></i> Candidate List</a>
    <a href="#" class="nav-link" data-target="appointments"><i class="fas fa-calendar-check me-2"></i> Appointments</a>
    <a href="#" class="nav-link" data-target="reports"><i class="fas fa-file-pdf me-2"></i> Review Reports</a>
    <a href="#" class="nav-link" data-target="questions"><i class="fas fa-list me-2"></i> Career Library</a>
    <a href="#" class="nav-link" data-target="questions"><i class="fas fa-list me-2"></i>Exams</a>
    <a href="#" class="nav-link" data-target="questions"><i class="fas fa-list me-2"></i> Admissions</a>
    <a href="<?= base_url('logout') ?>" class="mt-5 text-danger"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
</div>
<div class="main-content">
    
    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom sticky-top shadow-sm" >
        <div class="d-flex align-items-center gap-3">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-brain text-primary me-2"></i>Pharos Career Counselor</h5>
            
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
            <div class="col-lg-4 col-md-6">
                <div class="card h-100">
                    <div class="card-header"><i class="fas fa-compass text-primary me-2"></i> RIASEC Interest</div>
                    <div class="card-body chart-container"><canvas id="riasecChart"></canvas></div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card h-100">
                    <div class="card-header"><i class="fas fa-brain text-success me-2"></i> Aptitude & Logic</div>
                    <div class="card-body chart-container"><canvas id="aptitudeChart"></canvas></div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card h-100">
                    <div class="card-header"><i class="fas fa-network-wired text-info me-2"></i> Multiple Intelligences</div>
                    <div class="card-body chart-container"><canvas id="miChart"></canvas></div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-heart text-danger me-2"></i> Emotional Intelligence</span>
                        <span id="eqScoreBadge" class="badge bg-danger"></span>
                    </div>
                    <div class="card-body chart-container"><canvas id="eqChart"></canvas></div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card h-100">
                    <div class="card-header"><i class="fas fa-fire text-warning me-2"></i> Core Motivators</div>
                    <div class="card-body chart-container"><canvas id="motivatorsChart"></canvas></div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card h-100">
                    <div class="card-header"><i class="fas fa-user-tie text-secondary me-2"></i> Extracted Soft Skills</div>
                    <div class="card-body chart-container"><canvas id="traitsChart"></canvas></div>
                </div>
            </div>
        </div>

        <div class="card border-primary shadow-lg" style="border-width: 2px;">
            <div class="card-header bg-primary-subtle fs-5">
                <i class="fas fa-sliders-h me-2"></i> ML Input Vectors Config
            </div>
            <div class="card-body p-4">
                <form id="simulationForm">
                    <input type="hidden" id="raw_riasec_r"> <input type="hidden" id="raw_riasec_i">
                    <input type="hidden" id="raw_riasec_a"> <input type="hidden" id="raw_riasec_s">
                    <input type="hidden" id="raw_riasec_e"> <input type="hidden" id="raw_riasec_c">
                    <input type="hidden" id="raw_analytical"> <input type="hidden" id="raw_creative">
                    <input type="hidden" id="raw_social"> <input type="hidden" id="raw_leadership">
                    <input type="hidden" id="raw_technical"> <input type="hidden" id="raw_empathy">

                    <div class="row g-4">
                        <div class="col-md-4 border-end">
                            <h6 class="fw-bold text-primary mb-3">1. Academic Baseline (0-100)</h6>
                            <p class="text-muted small mb-3">Pre-filled from Aptitude Test. Override with actual school marks if available.</p>
                            
                            <div class="mb-3">
                                <label class="form-label">Mathematics / Numerical</label>
                                <input type="number" id="math_score" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Science / Spatial</label>
                                <input type="number" id="science_score" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">English / Verbal</label>
                                <input type="number" id="english_score" class="form-control" required>
                            </div>
                        </div>

                        <div class="col-md-4 border-end">
                            <h6 class="fw-bold text-primary mb-3">2. Lifestyle & Market Proxies</h6>
                            <p class="text-muted small mb-3">Translates student desires into macroeconomic variables.</p>
                            
                            <div class="mb-4">
                                <label class="form-label">Financial Goal (Salary Proxy)</label>
                                <select id="lifestyle_proxy" class="form-select" required>
                                    <option value="50.0">Stability (~$50k/yr)</option>
                                    <option value="75.0" selected>Comfort (~$75k/yr)</option>
                                    <option value="110.0">Wealth & Luxury (~$110k+/yr)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Industry Pace (Growth Proxy)</label>
                                <select id="risk_proxy" class="form-select" required>
                                    <option value="2.0">Traditional / Highly Stable (~2% growth)</option>
                                    <option value="5.0" selected>Balanced / Normal (~5% growth)</option>
                                    <option value="9.0">Cutting-Edge / Rapid (~9% growth)</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <h6 class="fw-bold text-primary mb-3">3. Career Comparison</h6>
                            <p class="text-muted small mb-3">Select two specific career clusters to pit against each other.</p>
                            
                            <div class="mb-4">
                                <label class="form-label">Option A</label>
                                <select id="career_a" class="form-select border-success" required>
                                    <option value="Architecture & Engineering">Architecture & Engineering</option>
                                    <option value="Business & Financial">Business & Financial</option>
                                    <option value="Computer & Mathematical">Computer & Mathematical</option>
                                    <option value="Management & Leadership">Management & Leadership</option>
                                    <option value="Healthcare Practitioners">Healthcare Practitioners</option>
                                    <option value="Arts, Design & Media">Arts, Design & Media</option>
                                    <option value="Science & Social Science">Science & Social Science</option>
                                    <option value="Legal & Public Policy">Legal & Public Policy</option>
                                    <option value="Education & Training">Education & Training</option>
                                    <option value="Sales & Related">Sales & Related</option>
                                    <option value="Office & Admin Support">Office & Admin Support</option>
                                    <option value="Community & Social Service">Community & Social Service</option>
                                    <option value="Protective Service">Protective Service</option>
                                    <option value="Military Specific">Military Specific</option>
                                    <option value="Installation & Repair">Installation & Repair</option>
                                    <option value="Production & Manufacturing">Production & Manufacturing</option>
                                    <option value="Transportation">Transportation</option>
                                    <option value="Food Preparation">Food Preparation</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Option B</label>
                                <select id="career_b" class="form-select border-info" required>
                                    <option value="Business & Financial">Business & Financial</option>
                                    <option value="Architecture & Engineering">Architecture & Engineering</option>
                                    <option value="Computer & Mathematical">Computer & Mathematical</option>
                                    <option value="Management & Leadership">Management & Leadership</option>
                                    <option value="Healthcare Practitioners">Healthcare Practitioners</option>
                                    <option value="Arts, Design & Media">Arts, Design & Media</option>
                                    <option value="Science & Social Science">Science & Social Science</option>
                                    <option value="Legal & Public Policy">Legal & Public Policy</option>
                                    <option value="Education & Training">Education & Training</option>
                                    <option value="Sales & Related">Sales & Related</option>
                                    <option value="Office & Admin Support">Office & Admin Support</option>
                                    <option value="Community & Social Service">Community & Social Service</option>
                                    <option value="Protective Service">Protective Service</option>
                                    <option value="Military Specific">Military Specific</option>
                                    <option value="Installation & Repair">Installation & Repair</option>
                                    <option value="Production & Manufacturing">Production & Manufacturing</option>
                                    <option value="Transportation">Transportation</option>
                                    <option value="Food Preparation">Food Preparation</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">
                        <i class="fas fa-bolt me-2"></i> Execute AI Prediction Model
                    </button>
                </form>
            </div>
        </div>

        <div id="aiOutputArea" class="mt-4 mb-5"></div>

    </div>
</div>

<script>
// Global Chart Instances
let chartInstances = {};

$(document).ready(function () {

    // --- 1. SEARCH CANDIDATE ---
    $('#searchBtn').click(function () {
        let q = $('#searchInput').val();
        if (q.length < 2) return;

        $('#searchBtn').html('<i class="fas fa-spinner fa-spin"></i>');

        $.post('<?= base_url("admin/api/searchCandidate") ?>', {query: q}, function (res) {
            $('#searchBtn').html('Find Student');
            let list = $('#searchResults').empty().show();
            if (res.data.length === 0) {
                list.append('<li class="list-group-item text-danger">No candidates found</li>');
                return;
            }

            res.data.forEach(user => {
                list.append(`<a href="#" class="list-group-item list-group-item-action select-user" data-id="${user.id}" data-name="${user.full_name}">
                <i class="fas fa-user-circle text-secondary me-2"></i> <strong>${user.full_name}</strong> <span class="text-muted ms-2">${user.email}</span>
                </a>`);
            });
        });
    });

    // Close dropdown if clicked outside
    $(document).click(function(e) {
        if (!$(e.target).closest('.input-group, #searchResults').length) {
            $('#searchResults').hide();
        }
    });

    // --- 2. SELECT CANDIDATE & FETCH DATA ---
    $(document).on('click', '.select-user', function (e) {
        e.preventDefault();
        let userId = $(this).data('id');
        let userName = $(this).data('full_name');

        $('#searchResults').hide();
        $('#searchInput').val('');
        $('#selectedCandidateName').text(userName);
        $('#mbtiBadge, #iqBadge').hide();
        $('#simulatorWorkspace').fadeIn();
        $('#aiOutputArea').empty();

        // Fetch Psychometric Data
        $.post('<?= base_url("admin/api/fetchCandidateData") ?>', {user_id: userId}, function (res) {
            if (res.status === 'error') {
                alert(res.msg);
                $('#simulatorWorkspace').hide();
                return;
            }

            let f = res.features;
            let charts = res.charts;

            // Fill hidden psychometric inputs for submission later
            $('#raw_riasec_r').val(f.RIASEC_R); $('#raw_riasec_i').val(f.RIASEC_I);
            $('#raw_riasec_a').val(f.RIASEC_A); $('#raw_riasec_s').val(f.RIASEC_S);
            $('#raw_riasec_e').val(f.RIASEC_E); $('#raw_riasec_c').val(f.RIASEC_C);
            $('#raw_analytical').val(f.analytical); $('#raw_creative').val(f.creative);
            $('#raw_social').val(f.social); $('#raw_leadership').val(f.leadership);
            $('#raw_technical').val(f.technical); $('#raw_empathy').val(f.empathy);

            // Pre-fill academic inputs
            $('#math_score').val(f.math_score.toFixed(0));
            $('#science_score').val(f.science_score.toFixed(0));
            $('#english_score').val(f.english_score.toFixed(0));

            renderAllCharts(f, charts);
        });
    });

    // --- 3. SUBMIT FORM TO RUN SIMULATION ---
    $('#simulationForm').submit(function (e) {
        e.preventDefault();
        
        // Scroll down to output area
        $('html, body').animate({ scrollTop: $('#aiOutputArea').offset().top - 50 }, 500);
        
        $('#aiOutputArea').html(`
            <div class="text-center py-5">
                <i class="fas fa-circle-notch fa-spin fa-3x text-primary mb-3"></i>
                <h5 class="text-muted">Running AI Inference Matrix...</h5>
            </div>
        `);

        let payload = {
            // Raw Psychometrics
            RIASEC_R: $('#raw_riasec_r').val(), RIASEC_I: $('#raw_riasec_i').val(),
            RIASEC_A: $('#raw_riasec_a').val(), RIASEC_S: $('#raw_riasec_s').val(),
            RIASEC_E: $('#raw_riasec_e').val(), RIASEC_C: $('#raw_riasec_c').val(),
            analytical: $('#raw_analytical').val(), creative: $('#raw_creative').val(),
            social: $('#raw_social').val(), leadership: $('#raw_leadership').val(),
            technical: $('#raw_technical').val(), empathy: $('#raw_empathy').val(),

            // Form Inputs
            math_score: $('#math_score').val(),
            science_score: $('#science_score').val(),
            english_score: $('#english_score').val(),
            target_salary_k: $('#lifestyle_proxy').val(),
            desired_growth_pct: $('#risk_proxy').val(),
            career_a: $('#career_a').val(), 
            career_b: $('#career_b').val()
        };

        $.post('<?= base_url("admin/api/runSimulation") ?>', payload, function (res) {
            if (res.status === 'validation_error') {
                let errorMsg = "<h5>Invalid Data Inputs:</h5><ul>";
                $.each(res.errors, function (key, val) { errorMsg += `<li>${val}</li>`; });
                errorMsg += "</ul>";
                $('#aiOutputArea').html(`<div class="alert alert-danger shadow-sm border-danger"><i class="fas fa-exclamation-triangle me-2"></i>${errorMsg}</div>`);
            } else if (res.status === 'error') {
                $('#aiOutputArea').html(`<div class="alert alert-danger shadow-sm border-danger"><i class="fas fa-exclamation-triangle me-2"></i> ${res.msg}</div>`);
            } else {
                $('#aiOutputArea').html(res.html);
            }
        });
    });
});

// --- CHART.JS MEGA-RENDERER ---
function renderAllCharts(features, charts) {
    // Clear old charts
    Object.keys(chartInstances).forEach(key => chartInstances[key].destroy());

    // 1. RIASEC (Radar)
    if(charts.riasec) {
        chartInstances['riasec'] = new Chart(document.getElementById('riasecChart'), {
            type: 'radar',
            data: {
                labels: ['Realistic', 'Investigative', 'Artistic', 'Social', 'Enterprising', 'Conventional'],
                datasets: [{ 
                    label: 'Score (0-100)', 
                    data: [charts.riasec.Realistic, charts.riasec.Investigative, charts.riasec.Artistic, charts.riasec.Social, charts.riasec.Enterprising, charts.riasec.Conventional], 
                    backgroundColor: 'rgba(59, 130, 246, 0.2)', borderColor: '#3b82f6', pointBackgroundColor: '#2563eb' 
                }]
            },
            options: { scales: { r: { min: 0, max: 100, ticks: { display: false } } }, plugins: { legend: { display: false } }, maintainAspectRatio: false }
        });
    }

    // 2. Aptitude (Bar)
    if(charts.aptitude) {
        if(charts.iq) $('#iqBadge').text("IQ Proj: " + charts.iq).show();
        chartInstances['aptitude'] = new Chart(document.getElementById('aptitudeChart'), {
            type: 'bar',
            data: {
                labels: ['Neumrical', 'Verbal', 'Logic', 'Spatial', 'Mech','Acc'],
                datasets: [{ 
                    label: 'Score', 
                    data: [charts.aptitude['Numerical Ability'], charts.aptitude['Verbal Reasoning'], charts.aptitude['Logical Reasoning'], charts.aptitude['Spatial Ability'], charts.aptitude['Mechanical Ability'],charts.aptitude['Accuracy']], 
                    backgroundColor: ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b', '#64748b','#10b981'] 
                }]
            },
            options: { indexAxis: 'y', scales: { x: { max: 100 } }, plugins: { legend: { display: false } }, maintainAspectRatio: false }
        });
    }

    // 3. Multiple Intelligences (Radar)
    if(charts.mi) {
        chartInstances['mi'] = new Chart(document.getElementById('miChart'), {
            type: 'radar',
            data: {
                labels: ['Linguistic','Numerical', 'Logical', 'Spatial', 'Auditory', 'Kinesthetic', 'Intrapersonal', 'Naturalistic'],
                datasets: [{ 
                    label: 'Score', 
                    data: [charts.mi.Linguistic,charts.mi.Numerical, charts.mi.Logical, charts.mi.Spatial, charts.mi.Auditory, charts.mi.Kinesthetic, charts.mi.Intrapersonal, charts.mi.Naturalistic], 
                    backgroundColor: 'rgba(6, 182, 212, 0.2)', borderColor: '#06b6d4', pointBackgroundColor: '#0891b2' 
                }]
            },
            options: { scales: { r: { min: 0, max: 100, ticks: { display: false } } }, plugins: { legend: { display: false } }, maintainAspectRatio: false }
        });
    }

    // 4. Emotional Intelligence (Polar)
    if(charts.eq) {
        if(charts.overall_eq) $('#eqScoreBadge').text(charts.overall_eq + "/100");
        chartInstances['eq'] = new Chart(document.getElementById('eqChart'), {
            type: 'polarArea',
            data: {
                labels: ['Awareness', 'Regulation', 'Motivation', 'Empathy', 'Social'],
                datasets: [{ 
                    data: [charts.eq['Self-Awareness'], charts.eq['Self-Regulation'], charts.eq['Motivation'], charts.eq['Empathy'], charts.eq['Social Skills']], 
                    backgroundColor: ['rgba(239, 68, 68, 0.6)', 'rgba(249, 115, 22, 0.6)', 'rgba(234, 179, 8, 0.6)', 'rgba(16, 185, 129, 0.6)', 'rgba(59, 130, 246, 0.6)'] 
                }]
            },
            options: { scales: { r: { min: 0, max: 100, ticks: { display: false } } }, plugins: { legend: { position: 'right', labels: { boxWidth: 10 } } }, maintainAspectRatio: false }
        });
    }

    // 5. Motivators (Doughnut)
    if(charts.motivators) {
        chartInstances['motivators'] = new Chart(document.getElementById('motivatorsChart'), {
            type: 'doughnut',
            data: {
                labels: ['Learning', 'Independence', 'Structure', 'Adventure', 'Pace', 'Creativity', 'Service'],
                datasets: [{ 
                    data: [charts.motivators['Continuous Learning'], charts.motivators.Independence, charts.motivators['Structured work environment'], charts.motivators.Adventure, charts.motivators['High Paced Environment'], charts.motivators.Creativity, charts.motivators['Social Service']], 
                    backgroundColor: ['#ef4444', '#f97316', '#eab308', '#22c55e', '#06b6d4', '#3b82f6', '#a855f7'] 
                }]
            },
            options: { plugins: { legend: { position: 'right', labels: { boxWidth: 10 } } }, cutout: '60%', maintainAspectRatio: false }
        });
    }

    // 6. Extracted Soft Skills (Bar) - Drawn from the 15 ML features!
    chartInstances['traits'] = new Chart(document.getElementById('traitsChart'), {
        type: 'bar',
        data: {
            labels: ['Analytical', 'Creative', 'Social', 'Leadership', 'Technical', 'Empathy'],
            datasets: [{
                label: 'Score (1-10)',
                data: [features.analytical, features.creative, features.social, features.leadership, features.technical, features.empathy],
                backgroundColor: '#64748b', borderRadius: 4
            }]
        },
        options: { indexAxis: 'y', scales: { x: { max: 10 } }, plugins: { legend: { display: false } }, maintainAspectRatio: false }
    });

    // Handle MBTI Tag
    if(charts.mbti) {
        $('#mbtiBadge').text(charts.mbti.type + " Personality").show();
    }
}
</script>
</body>
</html>