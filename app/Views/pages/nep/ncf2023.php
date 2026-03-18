<section style="
    position:relative;
    background: radial-gradient(circle at 20% 30%, #1e3c72 0%, #0B1C2D 70%);
    padding:80px 0;
    color:#fff;
    overflow:hidden;
    font-family:'Inter',sans-serif;
">

  <div style="position:absolute; width:600px; height:600px; background:rgba(244,196,48,0.1); filter:blur(150px); top:-10%; left:-10%; border-radius:50%;"></div>
  
  <div class="container position-relative">
    <div class="row align-items-center">

      <div class="col-lg-6">
        <span class="badge bg-warning text-dark mb-3 px-3 py-2 rounded-pill">NCF 2023 Official Framework</span>

        <h1 class="fw-bold mb-3"
            style="
            font-family:'Merriweather',serif;
            font-size:3rem;
            line-height: 1.2;
            color: #ffffff;">
          National Curriculum Framework <span style="color:#F4C430;">(NCF) 2023</span>
        </h1>

        <h3 class="h5 fw-normal mb-4" style="color: rgba(255,255,255,0.8); letter-spacing: 1px; text-transform: uppercase;">
          The Implementation Framework of NEP 2020
        </h3>

        <p class="lead" style="color: rgba(255,255,255,0.9); line-height:1.8;">
          The NCF 2023 is a big step towards improving education in India. It provides a clear pathway for preparing students for a better future by highlighting practical learning, skill development, and Indian values.
        </p>

        <a href="#" class="btn btn-gold btn-lg mt-4 px-5 py-3 rounded-pill shadow-lg" 
           style="background:#F4C430; color:#0B1C2D; font-weight:700; border:none;">
           Get NCF Consultation
        </a>

      </div>

      <div class="col-lg-6 text-center mt-5 mt-lg-0">
        <div style="position: relative; display: inline-block;">
             <div style="position:absolute; inset:0; background:rgba(255,255,255,0.2); filter:blur(40px); border-radius:50%;"></div>
             <img src="<?= base_url('assets/img/ncf.webp') ?>"
             class="img-fluid position-relative"
             alt="NEP 2020"
             style="max-height:400px; z-index:2; drop-shadow: 0 10px 30px rgba(0,0,0,0.5);">
        </div>
      </div>

    </div>
  </div>
</section>

<section style="background:#F4C430; padding:50px 0;">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-8">
        <h2 style="font-family: 'Merriweather', serif; font-weight:700; font-size:1.8rem; color:#0B1C2D; margin:0;">
          Want to make your School truly NCF-compliant?
        </h2>
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

<section style="background-color: #eaeaf7; padding:100px 0; font-family:'Inter', sans-serif;">
<div class="container">

    <div class="text-center mb-5">
        <h2 style="
            font-family:'Merriweather', serif;
            color:#0B1C2D;
            font-weight:700;
            font-size:2.8rem;">
            Core Highlights of NCF 2023
        </h2>
        <div style="height: 4px; width: 80px; background: #0B1C2D; margin: 20px auto; border-radius: 2px;"></div>

        <p class="text-secondary mx-auto" style="max-width:800px; font-size:1.1rem; line-height: 1.8;">
            The National Curriculum Framework 2023 introduces a fresh perspective on education, focusing on inclusivity, flexibility, and holistic development.
        </p>
    </div>

    <div class="row g-4">

        <?php 
        $highlights = [
            [
                "title" => "Holistic Transformation",
                "desc" => "Focuses on making learning meaningful by connecting school culture, teaching methods, and the environment to meet the needs of children aged 3 to 18."
            ],
            [
                "title" => "5+3+3+4 Structure",
                "desc" => "Replaces the 10+2 system. Covers Foundational (3-8), Preparatory (8-11), Middle (11-14), and Secondary (14-18) stages."
            ],
            [
                "title" => "Foundational Literacy",
                "desc" => "Emphasizes play-based and activity-oriented learning to ensure every child has strong reading, writing, and math skills early on."
            ],
            [
                "title" => "Teacher Empowerment",
                "desc" => "Strengthens teacher education through continuous training and resources, recognizing them as the torchbearers of this change."
            ],
            [
                "title" => "Multilingual Education",
                "desc" => "Introduces the three-language formula. Students learn their mother tongue, an Indian language, and English to value linguistic heritage."
            ],
            [
                "title" => "Flexible Assessment",
                "desc" => "Students can select subjects across disciplines instead of being restricted to streams like Science or Arts, aligning with career goals."
            ],
            [
                "title" => "Engaging Mathematics",
                "desc" => "Integrates practical and interactive teaching methods, linking math to real-world scenarios to remove fear and encourage understanding."
            ],
            [
                "title" => "Group-Based Learning",
                "desc" => "Categorizes subjects into 4 groups (Languages, Arts/PE, Social Science, Math/Science) to promote interdisciplinary study."
            ],
            [
                "title" => "Modern Assessment",
                "desc" => "Moves away from rote memorization. Focuses on competency-based learning and critical thinking, offering board exams twice a year."
            ]
        ];
        ?>

        <?php foreach($highlights as $item): ?>
        <div class="col-md-4">
            <div class="h-100 p-4 bg-white rounded-3 shadow-sm" 
                 style="border: 1px solid #E2E8F0; border-top: 4px solid #0B1C2D; transition: transform 0.2s;">
                
                <h4 style="color:#0B1C2D; font-weight:700; font-size:1.25rem; font-family:'Merriweather',serif; margin-bottom:15px;">
                    <?= $item['title'] ?>
                </h4>
                <p style="color:#475569; line-height:1.6; font-size:0.95rem; margin-bottom:0;">
                    <?= $item['desc'] ?>
                </p>
            </div>
        </div>
        <?php endforeach; ?>

    </div>
</div>
</section>

<section style="background: linear-gradient(180deg, #0B1C2D 0%, #050E17 100%); padding:80px 0; color:white;">
  <div class="container">
    <div class="row align-items-center">

      <div class="col-lg-8">
        <h2 style="font-family:'Merriweather',serif; font-weight:700; font-size:2.2rem; margin-bottom:15px;">
          <span style="color:#F4C430;">Implement NCF 2023 with Pharos Solutions</span>
        </h2>
        <p class="lead" style="color:rgba(255,255,255,0.8);">
            Align your school with the framework to provide holistic, future-ready education. Empower your teachers and engage students today.
        </p>
      </div>

      <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
        <a href="<?= base_url('contact') ?>"
           class="btn px-5 py-3 rounded-pill shadow-lg"
           style="background:#F4C430; color:#0B1C2D; font-weight:700; font-size:1.1rem; border:none;">
           Start Transformation
        </a>
      </div>

    </div>
  </div>
</section>