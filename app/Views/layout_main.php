<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Pharos Education |<?= esc($page_title ?? 'Empowering Future<') ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <?= csrf_meta();?>
        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous">
        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Merriweather:wght@700&display=swap" rel="stylesheet">
        <link href="<?= base_url('assets/css/lighthouse-theme.css') ?>" rel="stylesheet">
        
    </head>

    <body>
        <div id="page-loader"><div class="bar"></div></div>
        <!-- NAVBAR -->
        <nav class="navbar navbar-expand-lg sticky-top shadow-sm">
            <div class="container">

                <a class="navbar-brand" href="<?= base_url('/') ?>">
                    <img src="<?= base_url('assets/img/pharos.webp')?>" width="25%">
                </a>

                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon" style="filter: invert(1) brightness(2);"></span>
                </button>

                <div class="collapse navbar-collapse" id="navMenu">
                    <ul class="navbar-nav ms-auto align-items-center gap-2">

                        <li class="nav-item">
                            <a class="nav-link spa-link" href="<?= base_url('home') ?>">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link spa-link" href="<?= base_url('about') ?>">About</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="nepDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">NEP</a>
                            <ul class="dropdown-menu border-0 shadow-lg" aria-labelledby="nepsDropdown">
                                <li><a class="dropdown-item spa-link" href="<?= base_url('nep/nep2020') ?>">
                                        <span class="menu-title">NEP-2020</span>
                                        <span class="menu-desc">The vision of the policy is to build an educational system rooted in the Indian ethos that contributes directly 
                                            to transforming India by providing high-quality education to all</span>
                                    </a></li>
                                <li><a class="dropdown-item spa-link" href="<?= base_url('nep/ncf2023') ?>">
                                    <span class="menu-title">NCF-2023</span>
                                        <span class="menu-desc">The National Curriculum Framework (NCF) 2023 is a big step towards improving education in India. It follows the goals 
                                            set by the National Education Policy (NEP) 2020 and provides a clear pathway for preparing students for a better future.</span>
                                    </a></li>
                                <li><a class="dropdown-item spa-link" href="<?= base_url('nep/hlci') ?>">
                                        <span class="menu-title">Holistic Learning & Curricular Integration</span>
                                        <span class="menu-desc">How will NEP 2020 implement holistic learning and curricular integration of essential subjects?</span>
                                        </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item spa-link" href="<?= base_url('nep/nipun') ?>">
                                        <spam class="menu-title">NIPUN Bharat Mission</spam>
                                        <span class="menu-desc">NIPUN stands for National Initiative for Proficiency in Reading with Understanding and Numeracy.
                                            It was launched by the Ministry of Education in July 2021 addressing critical gaps in early education.</span>
                                    </a></li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="servicesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Services
                            </a>
                            <ul class="dropdown-menu border-0 shadow-lg" aria-labelledby="servicesDropdown">
                                <li><a class="dropdown-item spa-link" href="<?= base_url('service/cc89') ?>">
                                        <span class="menu-title">Career Guidance</span>
                                        <span class="menu-desc">Stream & Subject Selection Advanced 6-dimensional  assessment & personalised guidance to help you select 
                                            the perfect stream and subjects that align you to the right careers.</span>
                                    </a></li>
                                <li><a class="dropdown-item spa-link" href="<?= base_url('service/cc1112') ?>">
                                    <span class="menu-title">Career Counseling</span>
                                        <span class="menu-desc">career counseling sessions conducted by certified experts who analyze your unique strengths, interests, and aspirations.</span>
                                    </a></li>
                                 <li><a class="dropdown-item spa-link" href="<?= base_url('service/pat') ?>">
                                    <span class="menu-title">Psychometric Test</span>
                                        <span class="menu-desc">22+ Types of Career Assessments Available For Grade 2nd to Working Professionals
                                        Scientifically Validated Reports Trusted by Experts & Institutions</span>
                                    </a></li>
                                <li><a class="dropdown-item spa-link" href="<?= base_url('service/ugpg') ?>">
                                        <span class="menu-title">India Admissions</span>
                                        <span class="menu-desc">Online Admissions Assistance We help students choose the best UGC-approved online universities and programs that match their career plans.</span>
                                        </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item spa-link" href="<?= base_url('service/overseas') ?>">
                                        <spam class="menu-title">Overseas Admissions</spam>
                                        <span class="menu-desc">Studying in top international universities? At Career Pharos, we provide comprehensive study abroad support—right from understanding your goals to helping you land your student visa. </span></a>
                                </li>
                                <li><a class="dropdown-item spa-link" href="<?= base_url('service/hr') ?>">
                                        <spam class="menu-title">Talent Acquisition</spam>
                                        <span class="menu-desc">End-to-end talent search guidance to help you build the strong team for your targets.</span></a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="<?= base_url('school')?>"id="schoolDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">School</a>
                            <ul class="dropdown-menu border-0 shadow-lg" aria-labelledby="schoolsDropdown">
                                <li><a class="dropdown-item spa-link" href="<?= base_url('school/ccSchool') ?>">
                                        <span class="menu-title">Career Program</span>
                                        <span class="menu-desc">In School Career Guidance State-of-the-art assessment & end-to-end career guidance to help students discover their perfect career.</span>
                                    </a></li>
                                <li><a class="dropdown-item spa-link" href="<?= base_url('school/mun') ?>">
                                    <span class="menu-title">MUN</span>
                                        <span class="menu-desc">MUN Training Program Expert-led training and comprehensive guidance sessions to help students excel at MUN conferences./span>
                                    </a></li>
                                <li><a class="dropdown-item spa-link" href="<?= base_url('school/tt') ?>">
                                        <span class="menu-title">Teacher Training</span>
                                        <span class="menu-desc">Career Selection & Development 5-dimensional assessment & superior guidance to help you discover 
                                            your perfect career and choose the best next step.</span>
                                        </a></li>
                                <li><a class="dropdown-item spa-link" href="<?= base_url('school/admkt') ?>">
                                        <span class="menu-title">Admission & Marketing</span>
                                        <span class="menu-desc">Career Selection & Development 5-dimensional assessment & superior guidance to help you discover 
                                            your perfect career and choose the best next step.</span>
                                        </a></li>
                                        
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item spa-link" href="<?= base_url('school/psl') ?>">
                                        <spam class="menu-title">Pharos Learning</spam>
                                        <span class="menu-desc">End-to-end overseas admissions guidance to help you build the perfect applications for your target universities.</span>
                                    </a>
                                </li>
                            </ul>
                            
                        </li>
                        <li class="nav-item">
                            <a class="nav-link spa-link" href="<?= base_url('students') ?>">Students</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link spa-link" href="<?= base_url('teachers') ?>">Teachers</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link spa-link" href="<?= base_url('solutions/s1') ?>">Solutions</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link spa-link" href="<?= base_url('contact') ?>">Contact</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link spa-link" href="<?= base_url('login')?>">Login</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <main id="app-root">
            <?= $page_content ?>
        </main>

        <!-- FOOTER -->
        <footer class="py-4">
            <div class="container">
                <div class="row g-4">
                    <div class="col-md-4">
                        <h5>Pharos Education </h5>
                        <p>Empowering Futures</p>
                        <p class="small mb-0">&copy; <?= date('Y') ?> Pharos Education Consultancy. All rights reserved.</p>
                    </div>
                    <div class="col-md-4">
                        <h5>Contact</h5>
                        <p>Email: info@pharoseducation.in</p>
                        <p>Email: support@pharoseducation.in</p>
                        <p>Phone: +91-XXXXXXXXXX</p>
                    </div>
                    <div class="col-md-4">
                        <h5>Legal</h5>
                        <a class="nav-link spa-link" href="<?= base_url('policy/policy')?>"><p>Privacy Policy</p></a>
                        <a class="nav-link spa-link" href="<?= base_url('policy/termsCondition')?>"><p>Terms of Service</p></a>
                        <a class="nav-link spa-link" href="<?= base_url('policy/coc')?>"><p>Code of Conduct</p></a>
                    </div>
                </div>
            </div>
        </footer>
        <!-- Bootstrap JS (only for navbar toggle) -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    </body>
</html>
