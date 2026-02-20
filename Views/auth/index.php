<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharos Education | Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary-navy: #0f172a; /* Dark Navy from Logo */
            --primary-blue: #1e293b;
            --accent-teal: #3d8c83;  /* Teal for Buttons */
            --accent-teal-hover: #2d6a63;
            --text-light: #f8fafc;
            --text-dark: #334155;
            --bg-light: #f1f5f9;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Open Sans', sans-serif;
            background: var(--bg-light);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* --- MAIN CONTAINER --- */
        .container {
            width: 100%;
            height: 100vh;
            display: flex;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            background: #fff;
            overflow: hidden;
        }

        /* --- LEFT SIDE: HERO --- */
        .hero-section {
            flex: 1.2;
            background: var(--primary-navy);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: var(--text-light);
            overflow: hidden;
            padding: 40px;
        }

        /* Brand / Nav Icon (Top Left) */
        .brand-container {
            position: absolute;
            top: 40px;
            left: 40px;
            display: flex;
            align-items: center;
            z-index: 10;
        }

        .nav-icon {
            height: 50px; /* Adjust size for the top bar */
            width: auto;
            object-fit: contain;
        }

        /* Main Hero Image (Replaces CSS Animation) */
        .hero-image-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 20px;
        }

        .hero-image {
            max-width: 80%; /* Limits width to keep it looking nice */
            max-height: 50vh; /* Prevents it from getting too tall */
            width: auto;
            height: auto;
            object-fit: contain;
            filter: drop-shadow(0 10px 20px rgba(0,0,0,0.3)); /* Adds depth */
            animation: float 6s ease-in-out infinite;
        }

        .hero-content {
            z-index: 5;
            text-align: center;
            max-width: 500px;
            margin-bottom: 60px; /* Space from bottom */
        }

        .hero-content h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 2.2rem;
            line-height: 1.2;
            margin-bottom: 15px;
            font-weight: 800;
        }

        .hero-content p {
            font-size: 1.1rem;
            opacity: 0.8;
            line-height: 1.6;
        }

        /* --- RIGHT SIDE: FORMS --- */
        .form-section {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            position: relative;
        }

        .form-wrapper {
            width: 100%;
            max-width: 450px;
            padding: 40px;
        }

        /* Tab Switcher */
        .form-tabs {
            display: flex;
            background: #f1f5f9;
            border-radius: 12px;
            padding: 5px;
            margin-bottom: 30px;
        }

        .tab-btn {
            flex: 1;
            padding: 12px;
            border: none;
            background: transparent;
            cursor: pointer;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            color: var(--text-dark);
            border-radius: 8px;
            transition: 0.3s;
        }

        .tab-btn.active {
            background: #fff;
            color: var(--primary-navy);
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        /* Input Fields */
        .input-group {
            margin-bottom: 20px;
            position: relative;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .input-group input {
            width: 100%;
            padding: 12px 15px 12px 45px; /* Space for icon */
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: 0.3s;
            background: #f8fafc;
        }

        .input-group input:focus {
            outline: none;
            border-color: var(--accent-teal);
            background: #fff;
        }

        /* Buttons */
        .submit-btn {
            width: 100%;
            padding: 14px;
            background: var(--primary-navy);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        .submit-btn:hover {
            background: #2b3a55;
            transform: translateY(-2px);
        }

        .forgot-link {
            display: block;
            text-align: right;
            margin-top: -10px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            color: var(--accent-teal);
            text-decoration: none;
        }

        /* Form Logic classes */
        .form-content { display: none; animation: fadeIn 0.4s ease; }
        .form-content.active { display: block; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* --- RESPONSIVE --- */
        @media (max-width: 900px) {
            .container { flex-direction: column; height: auto; min-height: 100vh; }
            .hero-section { min-height: 350px; flex: none; padding: 20px; }
            .form-section { padding: 40px 20px; flex: 1; }
            
            .brand-container { top: 20px; left: 20px; }
            .hero-content { display: none; } /* Hide text on mobile to save space */
            .hero-image { max-width: 250px; }
        }
    </style>
</head>
<body>

    <div class="container">
        
        <div class="hero-section">
            
            <div class="brand-container">
                <img src="<?=base_url("assets/img/pharos.webp");?>" alt="Pharos Logo" class="nav-icon">
            </div>

            <div class="hero-image-container">
                <img src="<?=base_url("assets/img/pharos.webp");?>" alt="Pharos Education Lighthouse" class="hero-image">
            </div>
            
            <div class="hero-content">
                <h1>Discover Your True Potential</h1>
                <p>Advanced psychometric assessment platform for modern education.</p>
            </div>
        </div>

        <div class="form-section">
            <div class="form-wrapper">
                
                <div class="form-tabs">
                    <button class="tab-btn active" onclick="switchTab('login')">Login</button>
                    <button class="tab-btn" onclick="switchTab('register')">Register</button>
                </div>

                <div id="login-form" class="form-content active">
                    <form action="<?= base_url('auth/authenticate') ?>" method="POST">
                        <div class="input-group">
                            <label>Email Address</label>
                            <div class="input-wrapper">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="email" placeholder="student@example.com" required>
                            </div>
                        </div>
                        
                        <div class="input-group">
                            <label>Password</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="password" placeholder="Enter password" required>
                            </div>
                        </div>

                        <a href="#" class="forgot-link">Forgot Password?</a>

                        <button type="submit" class="submit-btn">Login to Dashboard</button>
                    </form>
                </div>

                <div id="register-form" class="form-content">
                    <form action="<?= base_url('auth/store') ?>" method="POST">
                        <div class="input-group">
                            <label>User Name</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user"></i>
                                <input type="text" name="username" placeholder="JohnDoe" required>
                            </div>
                        </div>
                        
                        <div class="input-group">
                            <label>Full Name</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user"></i>
                                <input type="text" name="full_name" placeholder="John Doe" required>
                            </div>
                        </div>

                        <div class="input-group">
                            <label>Email Address</label>
                            <div class="input-wrapper">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="email" placeholder="student@example.com" required>
                            </div>
                        </div>

                        <div class="input-group">
                            <label>Mobile Number</label>
                            <div class="input-wrapper">
                                <i class="fas fa-phone"></i>
                                <input type="tel" name="phone" placeholder="+91 98765 43210" required>
                            </div>
                        </div>
                        
                        <div class="input-group">
                            <label>Create Password</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="password" placeholder="Min 8 characters" required>
                            </div>
                        </div>

                        <input type="hidden" name="role" value="student">

                        <button type="submit" class="submit-btn" style="background-color: var(--accent-teal);">Create Account</button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            // Update Buttons
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            event.currentTarget.classList.add('active');

            // Update Forms
            document.querySelectorAll('.form-content').forEach(form => form.classList.remove('active'));
            document.getElementById(tab + '-form').classList.add('active');
        }
    </script>

</body>
</html>