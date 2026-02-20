<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduSpire - Career Guidance Platform</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f8f9fa; 
            padding-top: 70px; /* Space for fixed navbar */
        }
        
        /* Navbar Styling to match Edumilestones */
        .navbar {
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 15px 0;
        }
        .navbar-brand {
            font-weight: 800;
            color: #004aad !important; /* Brand Blue */
            font-size: 1.5rem;
        }
        .nav-link {
            font-weight: 500;
            color: #495057 !important;
            margin-right: 15px;
        }
        .nav-link:hover { color: #004aad !important; }
        
        .btn-login {
            border: 1px solid #dee2e6;
            border-radius: 20px;
            padding: 8px 25px;
            color: #333;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-login:hover {
            background-color: #f8f9fa;
            border-color: #333;
        }

        .btn-cta {
            background-color: #0d6efd;
            color: white !important;
            border-radius: 20px;
            padding: 8px 25px;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3);
        }
        .btn-cta:hover { background-color: #0b5ed7; color: white; }

        /* Page Loader Line */
        #page-loader { 
            display: none; 
            height: 3px; 
            background: #004aad; 
            width: 100%; 
            position: fixed; 
            top: 0; 
            left: 0; 
            z-index: 9999; 
        }
    </style>
</head>
<body>

<div id="page-loader"></div>

<nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top">
  <div class="container">
    <a class="navbar-brand spa-link" href="<?= base_url('/') ?>">
        <i class="bi bi-mortarboard-fill me-2"></i>EduSpire
    </a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item">
            <a class="nav-link spa-link" href="<?= base_url('home') ?>">Explore Counselors</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#">Become a Partner</a>
        </li>
        
        <?php if(session()->get('isLoggedIn')): ?>
            <li class="nav-item">
                <a class="nav-link spa-link btn-cta ms-2" href="<?= base_url('dashboard') ?>">
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link spa-link text-danger ms-2" href="<?= base_url('logout') ?>">Logout</a>
            </li>
        <?php else: ?>
            <li class="nav-item">
                <a class="nav-link btn-cta ms-2 spa-link" href="<?= base_url('career-audit') ?>">Free Career Audit</a>
            </li>
            <li class="nav-item">
                <a class="nav-link spa-link btn-login ms-3" href="<?= base_url('login') ?>">Login</a>
            </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div id="main-content">
    <?= isset($content) ? $content : '' ?>
</div>

<footer class="bg-white text-center p-4 mt-5 border-top">
    <div class="container">
        <p class="text-muted mb-0">&copy; 2024 EduSpire. All Rights Reserved.</p>
        <small class="text-muted">Designed for Career Excellence</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    
    // 1. Intercept Link Clicks
    $(document).on('click', '.spa-link', function(e) {
        e.preventDefault();
        let url = $(this).attr('href');
        // Ignore hash links or empty links
        if(url === '#' || url === '') return;
        loadPage(url);
    });

    // 2. Intercept Form Submissions (Login/Register)
    $(document).on('submit', '.spa-form', function(e) {
        e.preventDefault();
        let form = $(this);
        let submitBtn = form.find('button[type="submit"]');
        let originalText = submitBtn.text();
        
        // Show loading state on button
        submitBtn.prop('disabled', true).text('Processing...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(res) {
                if(res.status === 'success') {
                    // If server says redirect, load that page via SPA
                    loadPage(res.redirect);
                } else {
                    alert(res.message || 'An error occurred');
                }
            },
            error: function(xhr) {
                console.log(xhr);
                alert("Request failed. Please check console.");
            },
            complete: function() {
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });

    // 3. Core Page Load Function
    function loadPage(url, pushState = true) {
        $('#page-loader').show();
        
        $.ajax({
            url: url,
            type: 'GET',
            success: function(html) {
                // Inject HTML
                $('#main-content').html(html);
                
                // Update Browser History
                if(pushState) {
                    history.pushState({url: url}, '', url);
                }
                
                // Scroll to top
                window.scrollTo(0, 0);
            },
            error: function() {
                alert("Failed to load page. Please try again.");
            },
            complete: function() {
                $('#page-loader').hide();
            }
        });
    }

    // 4. Handle Browser Back/Forward Buttons
    window.onpopstate = function(e) {
        if(e.state && e.state.url) {
            loadPage(e.state.url, false);
        } else {
            // If no state (e.g., initial load), reload current to be safe
            location.reload(); 
        }
    };
});
</script>

</body>
</html>