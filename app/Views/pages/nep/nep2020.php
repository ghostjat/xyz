<section style="
    background: linear-gradient(135deg, #0F3460 0%, #0B1C2D 100%);
    padding: 80px 0;
    color: #fff;
    font-family: 'Inter', sans-serif;
    position: relative;
    overflow: hidden;
">
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
         background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 30px 30px; opacity: 0.05;"></div>

    <div class="container position-relative">
        <div class="row align-items-center">

            <div class="col-lg-6">
                <span class="badge bg-warning text-dark mb-3 px-3 py-2 rounded-pill">National Education Policy NEP 2020</span>

                <h1 class="fw-bold mb-3" style="font-family: 'Merriweather', serif; font-size: 2.8rem; line-height: 1.2;">
                    <span style="color: #FFF;"> National Education Policy </span><span style="color: #F4C430;">(NEP) 2020</span>
                </h1>

                <h3 class="h5 fw-normal mb-4" style="color: rgba(255,255,255,0.8);">
                    The Vision for India's New Education System
                </h3>

                <p class="lead mb-4" style="color: rgba(255,255,255,0.9); font-size: 1.05rem; line-height: 1.8;">
                    Approved by the Union Cabinet on 29 July 2020, NEP 2020 outlines the vision for a new, future-ready education system. 
                    It focuses on holistic development, rooted in Indian ethos, to transform India into a global knowledge superpower. 
                    The NCF 2023 is a big step towards improving education in India. It provides a clear pathway for preparing students for a better future by highlighting practical learning, skill development, and Indian values.
                </p>

                <a href="<?= base_url('contact') ?>" class="btn px-4 py-3 rounded-2 fw-bold shadow-sm"
                   style="background: #F4C430; color: #0F3460; border: none; transition: transform 0.2s;">
                   Get School Consultation
                </a>

            </div>

            <div class="col-lg-6 text-center mt-5 mt-lg-0">
                <div class="p-2" style="display: inline-block;">
                    <img src="<?= base_url('assets/img/NEP2020.webp') ?>"
                         class="img-fluid rounded-2"
                         alt="NEP 2020 Overview"
                         style="max-height: 400px;">
                </div>
            </div>

        </div>
    </div>
</section>

<section style="background: #F4C430; padding: 60px 0; border-bottom: 1px solid #E5E7EB;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h3 style="font-family: 'Merriweather', serif; font-weight:700; font-size:1.8rem; color:#0B1C2D; margin:0;">
                    Is your school NEP 2020 Compliant?
                </h3>
                <p class="mb-0" style="color: #0B1C2D;">
                    We help institutions align their curriculum and pedagogy with the new national standards.
                </p>
            </div>
              <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
        <a href="#" class="btn px-5 py-3 shadow-sm"
           style="background:#0B1C2D; border:none; border-radius:50px; color:#fff; font-weight:600;">
          Connect with us
        </a>
      </div>
        </div>
    </div>
</section>

<section style="background: #F3F4F6; padding: 80px 0; font-family: 'Inter', sans-serif;">
    <div class="container">
        
        <div class="row g-5">
            
            <div class="col-lg-5">
                <h2 class="fw-bold mb-4" style="color: #0F3460; font-family: 'Merriweather', serif;">
                    Key Highlights for Schools
                </h2>
                <p class="text-muted mb-4">
                    The policy emphasizes a shift from rote learning to competency-based education.
                </p>
                
                <ul class="list-unstyled">
                    <?php 
                    $points = [
                        "Focus on Early Childhood Care (ECCE)",
                        "Foundational Literacy and Numeracy (FLN)",
                        "Holistic, Integrated & Enjoyable Learning",
                        "Teacher Empowerment & Development",
                        "Equitable & Inclusive Education",
                        "Technology Integration in Teaching",
                        "Curtailing Dropouts & Universal Access"
                    ];
                    foreach($points as $point): ?>
                    <li class="d-flex align-items-center mb-3 p-3 rounded-2" 
                        style="background: #F9FAFB; border-left: 4px solid #F4C430;">
                        <i class="bi bi-check-circle-fill me-3" style="color: #0F3460;"></i>
                        <span style="color: #374151; font-weight: 500;"><?= $point ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="col-lg-7">
                <div class="row g-4">
                    
                    <div class="col-md-6">
                        <div class="h-100 p-4 rounded-3 border shadow-sm" style="background: #fff; border-color: #E5E7EB;">
                            <i class="bi bi-journal-richtext fs-1 mb-3 d-block" style="color: #0F3460;"></i>
                            <h5 class="fw-bold" style="color: #111;">New Pedagogical Structure</h5>
                            <p class="small text-muted mb-0">Moving away from 10+2 to a flexible 5+3+3+4 system aligned with developmental needs.</p>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="h-100 p-4 rounded-3 border shadow-sm" style="background: #fff; border-color: #E5E7EB;">
                            <i class="bi bi-globe-central-south-asia fs-1 mb-3 d-block" style="color: #0F3460;"></i>
                            <h5 class="fw-bold" style="color: #111;">Universal Access</h5>
                            <p class="small text-muted mb-0">Ensuring education for all by bridging gaps in access, participation, and learning outcomes.</p>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="h-100 p-4 rounded-3 border shadow-sm" style="background: #fff; border-color: #E5E7EB;">
                            <i class="bi bi-translate fs-1 mb-3 d-block" style="color: #0F3460;"></i>
                            <h5 class="fw-bold" style="color: #111;">Multilingualism</h5>
                            <p class="small text-muted mb-0">Promoting mother tongue/local language as a medium of instruction to improve conceptual understanding.</p>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="h-100 p-4 rounded-3 border shadow-sm" style="background: #fff; border-color: #E5E7EB;">
                            <i class="bi bi-puzzle fs-1 mb-3 d-block" style="color: #0F3460;"></i>
                            <h5 class="fw-bold" style="color: #111;">Essential Skills</h5>
                            <p class="small text-muted mb-0">Focus on 21st-century skills: critical thinking, creativity, collaboration, and communication.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="h-100 p-4 rounded-3 border shadow-sm" style="background: #fff; border-color: #E5E7EB;">
                            <i class="bi bi-puzzle fs-1 mb-3 d-block" style="color: #0F3460;"></i>
                            <h5 class="fw-bold" style="color: #111;">Teacher Empowerment</h5>
                            <p class="small text-muted mb-0">India’s National Education Policy (NEP) 2020 replaces the traditional 10+2 model with a new 5+3+3+4 structure, aligned with updated curriculum and flexible learning pathways.</p>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

<section  style="background: #F3F4F6; padding: 80px 0; border-top: 1px solid #E5E7EB;">
    <div class="container">
        
        <div class="text-center mb-5">
            <span class="text-uppercase fw-bold text-muted small letter-spacing-1">Curricular Framework</span>
            <h2 class="fw-bold mt-2" style="color: #0F3460; font-family: 'Merriweather', serif;">
                The New 5+3+3+4 Structure
            </h2>
            <p class="text-muted mx-auto" style="max-width: 700px;">
                The new academic structure replaces the 10+2 model, breaking education into four stages based on the cognitive development of children.
            </p>
            
        </div>

        <div class="table-responsive shadow-sm rounded-3 bg-white">
            <table class="table table-hover align-middle mb-0">
                <thead style="background: #0F3460; color: #fff;">
                    <tr>
                        <th class="py-3 px-4">Stage</th>
                        <th class="py-3 px-4">Duration</th>
                        <th class="py-3 px-4">Ages / Classes</th>
                        <th class="py-3 px-4">Focus Area</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-bold px-4 text-dark" style="border-left: 5px solid #F4C430;">Foundational</td>
                        <td class="px-4">5 Years</td>
                        <td class="px-4">Ages 3-8 <br><small class="text-muted">(Preschool - Class 2)</small></td>
                        <td class="px-4 text-muted">Play-based, multi-level, activity-based learning.</td>
                    </tr>
                    <tr>
                        <td class="fw-bold px-4 text-dark" style="border-left: 5px solid #D97706;">Preparatory</td>
                        <td class="px-4">3 Years</td>
                        <td class="px-4">Ages 8-11 <br><small class="text-muted">(Class 3 - 5)</small></td>
                        <td class="px-4 text-muted">Discovery, interaction, and classroom learning.</td>
                    </tr>
                    <tr>
                        <td class="fw-bold px-4 text-dark" style="border-left: 5px solid #B45309;">Middle</td>
                        <td class="px-4">3 Years</td>
                        <td class="px-4">Ages 11-14 <br><small class="text-muted">(Class 6 - 8)</small></td>
                        <td class="px-4 text-muted">Experiential learning in sciences, math, arts, social sciences.</td>
                    </tr>
                    <tr>
                        <td class="fw-bold px-4 text-dark" style="border-left: 5px solid #78350F;">Secondary</td>
                        <td class="px-4">4 Years</td>
                        <td class="px-4">Ages 14-18 <br><small class="text-muted">(Class 9 - 12)</small></td>
                        <td class="px-4 text-muted">Multidisciplinary study, critical thinking, flexibility & student choice.</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</section>

<section style="background: linear-gradient(180deg, #0B1C2D 0%, #050E17 100%); padding:80px 0; color:white;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 style="font-family:'Merriweather',serif; font-weight:700; font-size:2.2rem; margin-bottom:15px;">
                    <span style="color: #F4C430;"> Transform Your School Today</span>
                </h2>
                <p class="mb-0" style="color: rgba(255,255,255,0.8); font-size: 1.1rem;">
                    Implement Pharos’s integrated solutions to align with NEP 2020 ensuring a future-ready curriculum and empowered teachers.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <a href="<?= base_url('contact') ?>" class="btn px-5 py-3 rounded-pill shadow-lg"
           style="background:#F4C430; color:#0B1C2D; font-weight:700; font-size:1.1rem; border:none;">
                   Get Started
                </a>
            </div>
        </div>
    </div>
</section>