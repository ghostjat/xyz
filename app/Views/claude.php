<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Career Analysis System - Professional Psychometric Assessment</title>
    
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
    <link href="<?= base_url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-brain"></i>
                Career Analysis System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item" id="nav-dashboard" style="display: none;">
                        <a class="nav-link" href="#dashboard">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item" id="nav-reports" style="display: none;">
                        <a class="nav-link" href="#reports">
                            <i class="fas fa-file-alt"></i> Reports
                        </a>
                    </li>
                    <li class="nav-item" id="nav-logout" style="display: none;">
                        <a class="nav-link" href="#" onclick="logout()">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                    <li class="nav-item" id="nav-login">
                        <a class="nav-link" href="#login">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </li>
                    <li class="nav-item" id="nav-register">
                        <a class="nav-link" href="#register">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <div id="app-container">
        <!-- Content will be loaded dynamically here -->
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay" style="display: none;">
        <div class="spinner"></div>
    </div>

    <!-- jQuery 3.7 -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Bootstrap 5.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Main Application Script -->
    <script src="<?= base_url('assets/js/app.js') ?>" type="javascript"></script>

</body>
</html>
