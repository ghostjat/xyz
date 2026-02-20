<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Pharos Education</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0f172a; /* Deep Navy */
            --accent-color: #3b82f6;  /* Bright Blue */
            --bg-color: #f1f5f9;      /* Slate Grey Background */
            --text-main: #334155;
            --card-border: #e2e8f0;
        }

        body {
            background-color: var(--bg-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-main);
        }

        /* Navbar */
        .navbar {
            background-color: var(--primary-color) !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .navbar-brand img {
            height: 40px;
             /* Make logo white */
        }
        
        
        /* Card Styling */
        .dashboard-card {
            background: #fff;
            border: 1px solid var(--card-border);
            border-radius: 12px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }
        
        .dashboard-card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        /* Top Stats Cards */
        .stat-card {
            border-left: 4px solid var(--accent-color);
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background-color: rgba(59, 130, 246, 0.1);
            color: var(--accent-color);
        }

        /* Compact Quick Actions */
        .action-card {
            display: flex;
            align-items: center;
            padding: 15px;
            cursor: pointer;
            border: 1px solid var(--card-border);
            border-radius: 8px;
            background: white;
            transition: 0.2s;
        }
        .action-card:hover {
            border-color: var(--accent-color);
            background: #f8fafc;
            transform: translateY(-2px);
        }
        .action-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #eff6ff;
            color: var(--accent-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }

        /* Highlights (Right Sidebar) */
        .highlight-item {
            border-left: 3px solid var(--primary-color);
            background: linear-gradient(to right, #ffffff, #f8fafc);
        }
        
        .section-header {
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="#">
            <img src="<?=base_url("assets/img/pharos.webp");?>" alt="Pharos Education Logo">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center gap-3">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="#"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="fas fa-clipboard-list me-2"></i>Assessments</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="fas fa-briefcase me-2"></i>Careers</a>
                </li>
                <li class="nav-item me-3">
                    <a class="nav-link" href="#"><i class="fas fa-bell"></i></a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center text-white" href="#" data-bs-toggle="dropdown">
                        <img src="https://ui-avatars.com/api/?name=Student+Name&background=3b82f6&color=fff" class="rounded-circle border border-2 border-white me-2" width="32" height="32">
                        <span>Student Name</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
                        <li><a class="dropdown-item" href="#">Profile</a></li>
                        <li><a class="dropdown-item text-danger" href="<?= base_url('logout') ?>">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>