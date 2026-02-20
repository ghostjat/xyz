<style>
    /* --- HERO & SEARCH STYLES (Existing) --- */
    .hero-section {
        background-color: #f8f9fa;
        padding: 60px 0 20px 0;
        text-align: center;
    }
    .hero-title { font-weight: 800; color: #212529; font-size: 3rem; margin-bottom: 10px; }
    .hero-highlight { color: #004aad; }
    
    .search-container {
        background: white; padding: 8px; border-radius: 50px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08); max-width: 700px;
        margin: 30px auto; display: flex; align-items: center; border: 1px solid #e0e0e0;
    }
    .search-icon { padding-left: 20px; color: #6c757d; font-size: 1.2rem; }
    .search-input { border: none; flex-grow: 1; padding: 10px 15px; font-size: 1.1rem; outline: none; }
    .search-btn {
        border-radius: 40px; padding: 10px 40px; font-weight: 600;
        background-color: #2c3e50; border: none; color: white;
    }
    .search-btn:hover { background-color: #004aad; }

    .city-pill {
        background: white; border: 1px solid #dee2e6; border-radius: 20px;
        padding: 5px 15px; font-size: 0.85rem; color: #6c757d;
        display: inline-block; margin: 0 5px; text-decoration: none; transition: 0.2s;
    }
    .city-pill:hover { background: #f1f1f1; color: #000; }

    .role-switcher {
        background: white; display: inline-flex; border-radius: 50px;
        padding: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin: 40px 0;
    }
    .role-tab {
        padding: 10px 30px; border-radius: 40px; font-weight: 600; cursor: pointer;
        color: #495057; text-decoration: none; display: flex; align-items: center; gap: 8px;
    }
    .role-tab.active { background-color: #0d6efd; color: white; }

    /* --- SERVICE CARDS --- */
    .service-card {
        background: white; border: 1px solid #eaeaea; border-radius: 12px;
        padding: 25px; text-align: center; transition: transform 0.3s, box-shadow 0.3s;
        height: 100%; position: relative;
    }
    .service-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.08); border-color: #004aad; }
    .card-badge {
        position: absolute; top: 10px; right: 10px; font-size: 0.7rem;
        background: #eef4ff; color: #004aad; padding: 2px 8px; border-radius: 10px; font-weight: 600;
    }
    .icon-circle {
        width: 60px; height: 60px; border-radius: 50%; background: #f8f9fa;
        display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto;
        font-size: 1.5rem; color: #004aad;
    }

    /* --- COUNSELOR TICKER --- */
    .counselor-strip { overflow-x: auto; white-space: nowrap; padding: 20px 0; scrollbar-width: none; }
    .counselor-strip::-webkit-scrollbar { display: none; }
    .counselor-mini-card {
        display: inline-flex; align-items: center; background: white; padding: 10px;
        border-radius: 50px; border: 1px solid #eee; margin-right: 15px; min-width: 200px;
    }
    .counselor-avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; margin-right: 10px; }

    /* --- NEW: CONTACT SECTION STYLES --- */
    .contact-section { background-color: #f0f2f5; padding: 60px 0; }
    .contact-form-card {
        background: white; border-radius: 8px; overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .contact-header { background-color: #1e293b; color: white; padding: 20px; text-align: center; font-weight: 700; font-size: 1.2rem; }
    .form-control-custom { background-color: #f8f9fa; border: 1px solid #dee2e6; padding: 12px; }
    .input-group-text-custom { background-color: #e9ecef; border: 1px solid #dee2e6; color: #6c757d; }
    .btn-contact-submit { background-color: #1d4ed8; color: white; font-weight: 600; padding: 12px; width: 100%; border: none; border-radius: 5px; }
    .btn-contact-submit:hover { background-color: #1e40af; }
    .phone-icon-box {
        background-color: #3b82f6; color: white; width: 40px; height: 40px;
        border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;
    }

    /* --- NEW: TOP 10 COUNSELORS STYLES --- */
    .top-counselors-section { background-color: white; padding: 60px 0; text-align: center; }
    .counselor-circle-img {
        width: 120px; height: 120px; border-radius: 50%; object-fit: cover;
        margin-bottom: 15px; border: 4px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .counselings-done { font-size: 0.9rem; color: #1d4ed8; font-weight: 500; }
</style>

<div class="hero-section">
    <div class="container">
        <h1 class="hero-title">Best Career <span class="hero-highlight">Coach Near Me</span></h1>
        <p class="text-muted fs-5">India's Largest & Most Trusted Career Guidance Platform</p>

        <div class="search-container">
            <i class="bi bi-geo-alt search-icon"></i>
            <input type="text" id="cityInput" class="search-input" placeholder="Search Location (e.g. Delhi, Mumbai)">
            <button id="btnHeroSearch" class="search-btn">Search</button>
        </div>

        <div class="mb-4">
            <span class="text-muted small fw-bold me-2">POPULAR CITIES:</span>
            <a href="#" class="city-pill" onclick="fillCity('Bangalore')"><i class="bi bi-geo-alt-fill me-1"></i>Bangalore</a>
            <a href="#" class="city-pill" onclick="fillCity('Mumbai')"><i class="bi bi-geo-alt-fill me-1"></i>Mumbai</a>
            <a href="#" class="city-pill" onclick="fillCity('Delhi')"><i class="bi bi-geo-alt-fill me-1"></i>Delhi NCR</a>
            <a href="#" class="city-pill" onclick="fillCity('Pune')"><i class="bi bi-geo-alt-fill me-1"></i>Pune</a>
            <a href="#" class="city-pill">+480 Cities</a>
        </div>

        <div class="counselor-strip" id="counselor-ticker">
            <div class="counselor-mini-card">
                <img src="https://ui-avatars.com/api/?name=Dr+Anjali" class="counselor-avatar">
                <div><div class="fw-bold" style="font-size:0.9rem;">Dr. Anjali</div><div class="small text-muted"><i class="bi bi-star-fill text-warning"></i> 4.9 | 2500+ Sessions</div></div>
            </div>
             <div class="counselor-mini-card">
                <img src="https://ui-avatars.com/api/?name=Arshia+M" class="counselor-avatar">
                <div><div class="fw-bold" style="font-size:0.9rem;">Arshia Mehreen</div><div class="small text-muted"><i class="bi bi-star-fill text-warning"></i> 4.9 | 6620+ Sessions</div></div>
            </div>
             <div class="counselor-mini-card">
                <img src="https://ui-avatars.com/api/?name=Vijay+S" class="counselor-avatar">
                <div><div class="fw-bold" style="font-size:0.9rem;">Dr. Vijay</div><div class="small text-muted"><i class="bi bi-star-fill text-warning"></i> 4.9 | 1477+ Sessions</div></div>
            </div>
        </div>

        <div class="role-switcher">
            <a href="#" class="role-tab active"><i class="bi bi-mortarboard-fill"></i> STUDENTS</a>
            <a href="#" class="role-tab"><i class="bi bi-building"></i> SCHOOLS</a>
            <a href="#" class="role-tab"><i class="bi bi-people-fill"></i> PARTNER</a>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="row g-4">
        <div class="col-6 col-md-4 col-lg-2">
            <div class="service-card"><span class="card-badge">480+ Cities</span><div class="icon-circle bg-light text-primary"><i class="bi bi-camera-video"></i></div><h6 class="fw-bold">Career Counselling</h6></div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="service-card"><span class="card-badge">15 Years Plan</span><div class="icon-circle bg-light text-purple" style="color: #6f42c1;"><i class="bi bi-briefcase"></i></div><h6 class="fw-bold">Professional Guidance</h6></div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="service-card"><span class="card-badge">AI Powered</span><div class="icon-circle bg-light text-success"><i class="bi bi-clipboard-data"></i></div><h6 class="fw-bold">Career Assessment</h6></div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="service-card"><span class="card-badge">180+ Courses</span><div class="icon-circle bg-light text-warning"><i class="bi bi-laptop"></i></div><h6 class="fw-bold">Online Degree</h6></div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="service-card"><span class="card-badge">22+ Countries</span><div class="icon-circle bg-light text-info"><i class="bi bi-airplane"></i></div><h6 class="fw-bold">Study Abroad</h6></div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="service-card"><span class="card-badge">6 Programs</span><div class="icon-circle bg-light text-danger"><i class="bi bi-handshake"></i></div><h6 class="fw-bold">Become Partner</h6></div>
        </div>
    </div>
</div>

<div class="contact-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-5 mb-4 mb-md-0">
                <h2 class="fw-bold mb-3">Have any questions?</h2>
                <h4 class="text-muted fw-normal mb-4">We are here to help you!</h4>
                <p class="text-muted mb-4">Talk to our experts and get complete guidance. We have 35 career counsellors live now to answer your queries. Get connected and take informed career decisions.</p>
                <p class="text-muted small">Edumilestones is highest rated and 3 times award-winning best career counselling platform in India. Get advice from leaders in the industry.</p>
                
                <div class="d-flex align-items-center mt-4">
                    <div class="phone-icon-box me-3"><i class="bi bi-telephone-fill"></i></div>
                    <h5 class="fw-bold mb-0">+91 9808723260</h5>
                </div>
            </div>

            <div class="col-md-6 offset-md-1">
                <div class="contact-form-card">
                    <div class="contact-header">Contact Us</div>
                    <div class="p-4">
                        <form action="#" class="spa-form">
                            <div class="input-group mb-3">
                                <span class="input-group-text input-group-text-custom">I'm interested in</span>
                                <select class="form-select form-control-custom">
                                    <option>Select</option>
                                    <option>Career Counselling</option>
                                    <option>Study Abroad</option>
                                </select>
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text input-group-text-custom">Name</span>
                                <input type="text" class="form-control form-control-custom">
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text input-group-text-custom">Email</span>
                                <input type="email" class="form-control form-control-custom">
                            </div>
                            <div class="input-group mb-4">
                                <span class="input-group-text input-group-text-custom">Phone</span>
                                <input type="text" class="form-control form-control-custom" placeholder="91">
                                <input type="text" class="form-control form-control-custom w-50">
                            </div>
                            <button type="button" class="btn-contact-submit">Talk To Our Expert</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="top-counselors-section">
    <div class="container">
        <h2 class="fw-bold mb-3">Top 10 Career Counsellors in India</h2>
        <p class="text-muted mx-auto mb-5" style="max-width: 800px;">
            Introducing India's Finest Career Counselors: Navigating the ever-evolving landscape of career choices and opportunities requires expert guidance. Welcome to a world of top-notch career counseling services.
        </p>

        <div class="row justify-content-center">
            <div class="col-6 col-md-3 mb-4">
                <img src="https://ui-avatars.com/api/?name=Ms+Dixha&background=e91e63&color=fff&size=128" class="counselor-circle-img">
                <h5 class="fw-bold mb-1">Ms Dixha Nath</h5>
                <p class="counselings-done">2695 Counsellings Done</p>
            </div>
             <div class="col-6 col-md-3 mb-4">
                <img src="https://ui-avatars.com/api/?name=Bhumika+P&background=333&color=fff&size=128" class="counselor-circle-img">
                <h5 class="fw-bold mb-1">Bhumika Phutela</h5>
                <p class="counselings-done">2679 Counsellings Done</p>
            </div>
             <div class="col-6 col-md-3 mb-4">
                <img src="https://ui-avatars.com/api/?name=Srinivas+Y&background=ff5722&color=fff&size=128" class="counselor-circle-img">
                <h5 class="fw-bold mb-1">Srinivas Yepuri</h5>
                <p class="counselings-done">2502 Counsellings Done</p>
            </div>
             <div class="col-6 col-md-3 mb-4">
                <img src="https://ui-avatars.com/api/?name=Yogesh+B&background=212121&color=fff&size=128" class="counselor-circle-img">
                <h5 class="fw-bold mb-1">Yogesh Baheti</h5>
                <p class="counselings-done">1764 Counsellings Done</p>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4 mb-5" id="searchResultsArea" style="display:none;">
    <h3>Search Results</h3>
    <div id="results" class="row mt-3"></div>
</div>

<script>
    function fillCity(cityName) { $('#cityInput').val(cityName); }
    $('#btnHeroSearch').click(function() {
        let city = $('#cityInput').val();
        if(city.trim() === "") { alert("Please enter a city name"); return; }
        $('#btnHeroSearch').text('Searching...');
        $.get('<?= base_url("home/search") ?>', { city: city }, function(res) {
            let html = '';
            if(res.counselors.length > 0) {
                res.counselors.forEach(c => {
                    html += `<div class="col-md-4 mb-3"><div class="card p-3 border-0 shadow-sm"><div class="d-flex align-items-center"><img src="https://ui-avatars.com/api/?name=${c.name}" class="rounded-circle me-3" width="50"><div><h5 class="mb-0">${c.name}</h5><p class="text-muted small mb-0">${c.specialization} | ${c.city}</p><div class="text-warning small"><i class="bi bi-star-fill"></i> ${c.experience_years} Years Exp.</div></div></div><button class="btn btn-sm btn-outline-primary mt-3 w-100">View Profile</button></div></div>`;
                });
            } else { html = '<div class="col-12 text-center text-muted">No counselors found in ' + city + '</div>'; }
            $('#results').html(html); $('#searchResultsArea').show();
            $('html, body').animate({ scrollTop: $("#searchResultsArea").offset().top - 100 }, 500);
            $('#btnHeroSearch').text('Search');
        });
    });
</script>