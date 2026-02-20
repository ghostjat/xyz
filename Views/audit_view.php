<style>
    /* Page Specific Dark Theme */
    .audit-page-wrapper {
        background-color: #0B1120; /* Deep Dark Blue */
        color: white;
        min-height: 90vh; /* Full viewport height minus header */
        display: flex;
        align-items: center;
        overflow: hidden;
        position: relative;
        padding: 50px 0;
    }

    /* --- Left Content Styling --- */
    .audit-badge {
        background: rgba(6, 182, 212, 0.15);
        color: #22d3ee; /* Cyan */
        border: 1px solid #0e7490;
        font-size: 0.8rem;
        font-weight: 700;
        padding: 5px 12px;
        border-radius: 4px;
        text-transform: uppercase;
        letter-spacing: 1px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
    }

    .audit-title {
        font-size: 3.5rem;
        font-weight: 700;
        line-height: 1.1;
        margin-bottom: 20px;
        background: linear-gradient(to right, #fff, #94a3b8);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .audit-desc {
        color: #94a3b8; /* Slate gray */
        font-size: 1.1rem;
        margin-bottom: 40px;
        max-width: 500px;
        line-height: 1.6;
    }

    /* Icon List */
    .feature-item {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
        color: #cbd5e1;
        font-size: 1.05rem;
    }
    .feature-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: rgba(34, 211, 238, 0.1);
        color: #22d3ee;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    /* CTA Button */
    .btn-audit-start {
        background: #06b6d4; /* Cyan-500 */
        color: #000;
        font-weight: 700;
        padding: 15px 35px;
        border-radius: 8px;
        font-size: 1.1rem;
        border: none;
        margin-top: 20px;
        transition: 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    .btn-audit-start:hover {
        background: #22d3ee; /* Lighter Cyan */
        box-shadow: 0 0 20px rgba(34, 211, 238, 0.4);
    }

    /* --- Right Side: Radar Animation --- */
    .radar-container {
        position: relative;
        width: 500px;
        height: 500px;
        margin: 0 auto;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    /* The Concentric Circles */
    .radar-circle {
        position: absolute;
        border: 1px solid rgba(34, 211, 238, 0.2);
        border-radius: 50%;
    }
    .c1 { width: 100%; height: 100%; }
    .c2 { width: 75%; height: 75%; }
    .c3 { width: 50%; height: 50%; }
    .c4 { width: 25%; height: 25%; background: #22d3ee; box-shadow: 0 0 15px #22d3ee; } /* Center Dot */

    /* The Scanning Line */
    .radar-scan {
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: conic-gradient(from 0deg, transparent 0deg, transparent 270deg, rgba(34, 211, 238, 0.4) 360deg);
        animation: scan 4s linear infinite;
        z-index: 1;
    }

    /* Crosshairs */
    .crosshair-v { position: absolute; width: 1px; height: 100%; background: rgba(34, 211, 238, 0.1); }
    .crosshair-h { position: absolute; width: 100%; height: 1px; background: rgba(34, 211, 238, 0.1); }

    /* Data Points (Red/Green Dots) */
    .data-point {
        position: absolute;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: bold;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        gap: 6px;
        z-index: 2;
    }
    .dp-red {
        top: 25%; right: 20%;
        color: #f87171; border: 1px solid rgba(248, 113, 113, 0.3); background: rgba(248, 113, 113, 0.1);
    }
    .dp-dot-red { width: 8px; height: 8px; background: #f87171; border-radius: 50%; box-shadow: 0 0 10px #f87171; }

    .dp-green {
        bottom: 30%; left: 20%;
        color: #4ade80; border: 1px solid rgba(74, 222, 128, 0.3); background: rgba(74, 222, 128, 0.1);
    }
    .dp-dot-green { width: 8px; height: 8px; background: #4ade80; border-radius: 50%; box-shadow: 0 0 10px #4ade80; }

    .scan-text {
        position: absolute;
        bottom: -40px;
        font-family: monospace;
        color: #0e7490;
        letter-spacing: 2px;
        animation: blink 2s infinite;
    }

    @keyframes scan { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
</style>

<div class="audit-page-wrapper">
    <div class="container">
        <div class="row align-items-center">
            
            <div class="col-lg-6">
                <div class="audit-badge">
                    <i class="bi bi-cpu"></i> AI-Powered Diagnostic
                </div>
                
                <h1 class="audit-title">Audit Your Career<br>in 2 Mins.</h1>
                
                <p class="audit-desc">
                    Our algorithms compare your profile against 50M+ data points to find your hidden salary potential and leadership readiness gaps.
                </p>

                <div class="feature-list">
                    <div class="feature-item">
                        <div class="feature-icon"><i class="bi bi-bar-chart-fill"></i></div>
                        <div>Free Salary Benchmarking</div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="bi bi-bullseye"></i></div>
                        <div>Hidden Skill Gap Detection</div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="bi bi-lightning-charge-fill"></i></div>
                        <div>Growth Velocity Score</div>
                    </div>
                </div>

                <a href="<?= base_url('login?ref=audit') ?>" class="btn-audit-start spa-link">
                    Start Free Audit <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="col-lg-6 d-none d-lg-block">
                <div class="radar-container">
                    <div class="crosshair-v"></div>
                    <div class="crosshair-h"></div>
                    
                    <div class="radar-circle c1"></div>
                    <div class="radar-circle c2"></div>
                    <div class="radar-circle c3"></div>
                    <div class="radar-circle c4"></div> <div class="radar-scan"></div>

                    <div class="data-point dp-red">
                        <div class="dp-dot-red"></div> Salary_Gap
                    </div>
                    
                    <div class="data-point dp-green">
                        <div class="dp-dot-green"></div> High_Demand
                    </div>

                    <div class="scan-text">SYSTEM_SCANNING...</div>
                </div>
            </div>

        </div>
    </div>
</div>