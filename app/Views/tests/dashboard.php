<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Psychometric Assessment Hub</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .test-card {
            transition: transform 0.2s;
            cursor: default;
        }
        .test-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        #result-container {
            min-height: 200px;
            display: none; /* Hidden by default */
        }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">Eduspire Assessment</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="<?= base_url('dashboard') ?>">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="<?= base_url('logout') ?>">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h2 class="fw-bold text-primary">Available Assessments</h2>
                <p class="text-muted">Select a test below to begin or view your analysis.</p>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-5" id="test-grid">
            <?php 
            $tests = [
                'RIASEC'   => ['icon' => 'fa-briefcase', 'desc' => 'Holland Codes (Career Interests)'],
                'MBTI'     => ['icon' => 'fa-users', 'desc' => 'Myers-Briggs Type Indicator'],
                'GARDNER'  => ['icon' => 'fa-brain', 'desc' => 'Multiple Intelligences'],
                'EQ'       => ['icon' => 'fa-heart', 'desc' => 'Emotional Intelligence'],
                'APTITUDE' => ['icon' => 'fa-calculator', 'desc' => 'General Aptitude'],
                'VARK'     => ['icon' => 'fa-eye', 'desc' => 'Learning Styles']
            ]; 
            ?>

            <?php foreach ($tests as $name => $info): ?>
            <div class="col">
                <div class="card h-100 test-card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-3 text-primary">
                            <i class="fas <?= $info['icon'] ?> fa-3x"></i>
                        </div>
                        <h4 class="card-title fw-bold"><?= $name ?></h4>
                        <p class="card-text text-muted small"><?= $info['desc'] ?></p>
                    </div>
                    <div class="card-footer bg-white border-0 pb-3 d-flex justify-content-between">
                        <button class="btn btn-primary btn-sm w-45 btn-action" 
                                data-action="take" 
                                data-test="<?= $name ?>">
                            <i class="fas fa-play me-1"></i> Take Test
                        </button>
                        <button class="btn btn-outline-secondary btn-sm w-45 btn-action" 
                                data-action="report" 
                                data-test="<?= $name ?>">
                            <i class="fas fa-chart-bar me-1"></i> Report
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="row mb-5">
            <div class="col-12">
                <div id="loading-spinner" class="text-center py-5 d-none">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Fetching data...</p>
                </div>

                <div id="render-area" class="card shadow-sm d-none">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0" id="render-title">Result</h5>
                        <button type="button" class="btn-close" id="close-render-area"></button>
                    </div>
                    <div class="card-body" id="render-content">
                        </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    $(document).ready(function() {
        
        // Handle Button Clicks
        $('.btn-action').click(function(e) {
            e.preventDefault();
            
            let testName = $(this).data('test');
            let action = $(this).data('action'); // 'take' or 'report'
            
            // UI Updates
            $('#render-area').addClass('d-none');
            $('#loading-spinner').removeClass('d-none');
            
            // Scroll to render area
            $('html, body').animate({
                scrollTop: $("#render-area").offset().top - 100
            }, 500);

            // Construct URL (Adjust based on your CI4 Routes)
            // Example: /tests/take/RIASEC or /tests/report/MBTI
            let url = "<?= base_url('tests') ?>/" + testName.toLowerCase();

            console.log("Fetching: " + url);

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'html', // Expecting HTML partial view
                success: function(response) {
                    $('#loading-spinner').addClass('d-none');
                    $('#render-area').removeClass('d-none');
                    
                    // Update Header
                    let title = (action === 'take') ? 'Taking Test: ' : 'Analysis Report: ';
                    $('#render-title').text(title + testName);
                    
                    // Inject Content
                    $('#render-content').html(response);
                },
                error: function(xhr, status, error) {
                    $('#loading-spinner').addClass('d-none');
                    $('#render-area').removeClass('d-none');
                    $('#render-content').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> 
                            Error loading data. Status: ${xhr.status}
                        </div>
                    `);
                }
            });
        });

        // Close Render Area
        $('#close-render-area').click(function() {
            $('#render-area').addClass('d-none');
            $('html, body').animate({ scrollTop: 0 }, 500);
        });

    });
    </script>
</body>
</html>