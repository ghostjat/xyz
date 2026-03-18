<section style="position:relative; overflow:hidden; background:#0b1c2d;">

    <!-- Background Image -->
    <div style="
        position:absolute;
        inset:0;
        background:url('<?= base_url('assets/img/hero4.webp'); ?>') center/cover no-repeat;
        filter:blur(6px) brightness(0.6);
        transform:scale(1.05);
        z-index:1;">
    </div>

    <!-- Dark Overlay -->
    <div style="
        position:absolute;
        inset:0;
        background:linear-gradient(90deg, rgba(11,28,45,0.9) 40%, rgba(11,28,45,0.5) 100%);
        z-index:2;">
    </div>

    <div class="container" style="position:relative; z-index:3;">
        <div class="row align-items-center" style="min-height:100vh;">

            <!-- LEFT CONTENT -->
            <div class="col-lg-7 text-white">

                <h1 style="
                    font-weight:800;
                    font-size:2.9rem;
                    line-height:1.2;
                    color:#F4C430;
                    text-shadow:0 4px 25px rgba(212,175,55,0.5);">
                    Transform Your School with the 
                    PHAROS Learning System
                </h1>

                <p style="
                    margin-top:20px;
                    font-size:1.2rem;
                    color:#f1f1f1;">
                    An integrated digital learning solution combining curriculum,
                    pedagogy and technology.
                </p>
                
                <h4 style ="
                    font-weight:500;
                    font-size:2rem;
                    line-height:1.2;
                    color:#F4C430;
                    text-shadow:0 4px 25px rgba(212,175,55,0.5);">
                    PHAROS School System builds confidence in your students through
                </h4>

                <ul style="
                    margin-top:25px;
                    list-style:none;
                    padding-left:0;
                    font-size:1.05rem;">
                    <li style="margin-bottom:10px;">
                        <span style="color:#d4af37;">✔</span> Unmatched Curriculum & At-Home Support
                    </li>
                    <li style="margin-bottom:10px;">
                        <span style="color:#d4af37;">✔</span> Advanced Teacher Development Program
                    </li>
                    <li>
                        <span style="color:#d4af37;">✔</span> Infrastructure & Technology Upgrades
                    </li>
                </ul>

            </div>

            <!-- RIGHT FORM -->
            <div class="col-lg-5">

                <div style="
                    background:rgba(255,255,255,0.95);
                    padding:30px;
                    border-radius:20px;
                    box-shadow:0 25px 70px rgba(0,0,0,0.4);
                    max-width:420px;
                    margin:auto;">

                    <h5 style="
                        text-align:center;
                        font-weight:700;
                        margin-bottom:20px;">
                        GIVE YOUR SCHOOL THE <br>
                        <span style="color:#d4af37;">PHAROS ADVANTAGE</span>
                    </h5>

                    <form method="post" action="<?= base_url('school-enquiry'); ?>">
                        
                        <div style="margin-bottom:15px;">
                            <select name="role" required
                                style="width:100%; padding:10px; border-radius:8px; border:1px solid #ccc;">
                                <option value="">Role at school *</option>
                                <option>Owner</option>
                                <option>Director</option>
                                <option>Trustee</option>
                                <option>Principal</option>
                                <option>Vice Principal</option>
                                <option>Teacher</option>
                                <option>Other</option>
                            </select>
                        </div>

                        <div style="margin-bottom:15px;">
                            <input type="text" name="name" required
                                placeholder="Your Name *"
                                style="width:100%; padding:10px; border-radius:8px; border:1px solid #ccc;">
                        </div>

                        <div style="margin-bottom:15px;">
                            <input type="email" name="email" required
                                placeholder="Email *"
                                style="width:100%; padding:10px; border-radius:8px; border:1px solid #ccc;">
                        </div>

                        <div style="margin-bottom:15px;">
                            <input type="tel" name="phone" required
                                placeholder="Phone Number *"
                                style="width:100%; padding:10px; border-radius:8px; border:1px solid #ccc;">
                        </div>

                        <div style="margin-bottom:15px;">
                            <input type="text" name="school" required
                                placeholder="School Name *"
                                style="width:100%; padding:10px; border-radius:8px; border:1px solid #ccc;">
                        </div>

                        <div style="margin-bottom:15px;">
                            <select name="state" required
                                style="width:100%; padding:10px; border-radius:8px; border:1px solid #ccc;">
                                <option value="">Select State *</option>
                                <option>Maharashtra</option>
                                <option>Delhi</option>
                                <option>Karnataka</option>
                                <option>Other</option>
                            </select>
                        </div>

                        <div style="margin-bottom:15px;">
                            <label style="font-size:0.85rem;">
                                <input type="checkbox" required>
                                I agree to receive communications
                            </label>
                        </div>

                        <button type="submit"
                            style="
                                width:100%;
                                padding:12px;
                                background:#d4af37;
                                color:#fff;
                                border:none;
                                border-radius:30px;
                                font-weight:600;">
                            Submit
                        </button>

                    </form>

                </div>

            </div>

        </div>
    </div>

</section>
