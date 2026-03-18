<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Pharos Education Career Analysis Report</title>
    <style>
        /* =========================================================
           1. CORE RESET & PAGE SETUP (DOMPDF SAFE)
           ========================================================= */
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            color: #333; 
            margin: 0; 
            padding: 40px 0; /* Space above and below the pages in workspace */
            font-size: 10pt; 
            line-height: 1.5; 
            background: #111; /* Black workspace screen */
        }
        
        .page { 
            width: 210mm; 
            min-height: 297mm; /* Exact A4 Dimensions */
            padding: 40px; 
            margin: 0 auto 40px auto; /* Centered with space between pages */
            background: white; 
            position: relative; 
            box-sizing: border-box; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.6); /* Realistic paper shadow */
            overflow: auto; 
        }

        /* =========================================================
           2. FLOATING ACTION BAR (OUTSIDE THE A4 PAGE)
           ========================================================= */
        .workspace-actions {
            position: fixed;
            top: 20px;
            right: 30px;
            z-index: 9999;
            display: flex;
            gap: 15px;
        }
        .btn-print {
            background: #e67e22;
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 14pt;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,0,0,0.5);
            transition: 0.2s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn-print:hover {
            background: #d35400;
            transform: translateY(-2px);
        }

       /* =========================================================
           3. STRICT DYNAMIC PRINT RULES (TRIGGERS ON CTRL+P)
           ========================================================= */
        @media print {
            /* 1. Set global paper size and safe margins so text never hits the edge */
            @page { 
                size: A4 portrait; 
                margin:0 !important;
            }
            
            /* 2. Strip away the dark workspace */
            body { 
                background: white !important; 
                padding: 0 !important; 
                margin: 0 !important; 
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            /* 3. CRITICAL FIX: Make the rigid pages fluid */
            /* We remove the strict heights and paddings so dynamic content can flow endlessly */
            .page {
                display: flex;
                flex-direction: column;
                width: 100vw;
                height: 100vh !important; /* EXACTLY 1 sheet of paper */
                min-height: 297mm !important;
                padding: 15mm 15mm 12mm 15mm !important; 
                box-sizing: border-box;
                page-break-after: always !important;
                break-after: page !important;
            }
            
            footer {
                margin-bottom: 100px !important; /* Glues footer to the absolute bottom of the 100vh page */
                bottom: 12mm; /* Sits perfectly inside the 25mm Dead Zone */
                left: 15mm;
                right: 15mm;
                border-top: 1.5px solid #000; /* Replicates the bold line from your screenshot */
                padding-top: 8px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                font-size: 10.5pt;
                font-weight: 600;
                color: #000;
                background: #fff;
            }
            
            /* The Cover Page should always take exactly one full sheet */
            .cover-page {
                height: 100vh !important;
                page-break-after: always !important;
            }

            /* 4. ANTI-BREAK RULES (The "Glue") */
            /* This tells the browser: "If this specific element is about to be cut in half at the bottom of the paper, move the ENTIRE element to the top of the next page." */
            tr, .editorial-box, .mbti-row, .chart-container, .no-break, h2, h3, h4 { 
                page-break-inside: avoid !important; 
                break-inside: avoid !important; 
            }
            
            /* Ensure table headers repeat on the next page if a table is long */
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
            
            /* Hide the web-only action buttons */
            .workspace-actions, .no-print { display: none !important; }
            
            /* Force exact background colors and SVGs */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
        .page-content {
            flex-grow: 1; /* Forces the content to take up all empty space, pushing the footer down */
        }
         footer {
            flex-shrink: 0; /* Protects the footer from ever being crushed by content */
            border-top: 1.5px solid #000;
            padding-top: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10.5pt;
            font-weight: 600;
            color: #000;
            background: #fff;
            margin-top: 15px; /* Ensures a gap between text and the black line */
        }
        .footer-contact {
            display: flex;
            gap: 25px;
            align-items: center;
        }
        .footer-contact i {
            font-size: 12pt;
            margin-right: 5px;
        }
        /* =========================================================
           2. PREMIUM HYBRID COLOR PALETTE
           ========================================================= */
        .text-primary { color: #2980b9; } /* Corporate Blue */
        .text-accent { color: #e67e22; }  /* Warm Orange */
        .text-dark { color: #2c3e50; }    /* Slate Gray */
        .text-muted { color: #7f8c8d; }   /* Light Gray */
        
        .bg-primary { background: #2980b9; color: white; }
        .bg-dark { background: #2c3e50; color: white; }
        .bg-light { background: #f4f6f9; } /* SaaS Card Background */
        
        /* =========================================================
           3. TYPOGRAPHY & STRUCTURAL ELEMENTS
           ========================================================= */
        h1, h2, h3, h4 { margin-top: 0; font-family: 'Helvetica', sans-serif; color: #2c3e50; }
        .header-strip { background: #f4f6f9; border-left: 5px solid #2980b9; padding: 12px 20px; font-weight: bold; text-transform: uppercase; margin-bottom: 30px; font-size: 11pt; color: #2c3e50; }
        .section-title { font-size: 14pt; color: #2980b9; border-bottom: 2px solid #ecf0f1; padding-bottom: 5px; margin-top: 30px; margin-bottom: 15px; text-transform: uppercase; }
        
        
        /* =========================================================
           4. DATA VISUALIZATION CLASSES (TABLE BASED)
           ========================================================= */
        table.layout-table { width: 100%; border-collapse: collapse; }
        table.layout-table td { vertical-align: top; }
        
        /* Sliders */
        .mbti-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 30px; }
        .mbti-label { width: 18%; font-weight: bold; font-size: 10pt; }
        .mbti-label.left { text-align: right; }
        .mbti-label.right { text-align: left; }
        .mbti-center { width: 60%; text-align: center; padding: 0 15px; box-sizing: border-box; }
        .mbti-text { font-weight: bold; margin-bottom: 8px; font-size: 5pt; }
        .mbti-track { display: flex; height: 16px; background: #ecf0f1; border-radius: 8px; }
        .mbti-half { width: 10%; display: flex; }
        .mbti-half.left { justify-content: flex-end; border-right: 2px solid #fff; }
        .mbti-half.right { justify-content: flex-start; }
        .mbti-fill { height: 100%; }
        .mbti-fill.left { border-radius: 8px 0 0 8px; }
        .mbti-fill.right { border-radius: 0 8px 8px 0; }
        
        /* Horizontal Bars */
        table.bar-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table.bar-table td { padding: 4px 0; vertical-align: middle; }
        .bar-label { width: 30%; font-size: 9.5pt; text-align: right; padding-right: 15px; font-weight: bold; color: #333; }
        .bar-track-td { width: 60%; }
        .bar-track { width: 100%; height: 18px; background: #f0f0f0; border-radius: 3px; position: relative; }
        .bar-val { height: 18px; border-radius: 3px; }
        .bar-score { width: 10%; text-align: left; padding-left: 10px; font-size: 9.5pt; font-weight: bold; color: #2980b9; }

        /* Clean Data Table */
        table.data-table { width: 100%; border-collapse: collapse; font-size: 9pt; margin-top: 15px; }
        table.data-table th { background: #2c3e50; color: white; padding: 12px 10px; text-align: left; text-transform: uppercase; }
        table.data-table td { border-bottom: 1px solid #ecf0f1; padding: 12px 10px; vertical-align: top; }
        
        .hero-image {
            width: 100%;
            height: auto;
            object-fit: contain;
        }
        /* =========================================================
           6. STRICT PAGINATION & ANTI-BREAK RULES
           ========================================================= */
        /* Prevent tables and rows from being cut in half */
        table { page-break-inside: auto; }
        tr    { page-break-inside: avoid; page-break-after: auto; }
        thead { display: table-header-group; }
        tfoot { display: table-footer-group; }

        /* Use this class to wrap any div that should NEVER split across a page */
        .no-break { 
            page-break-inside: avoid !important; 
            break-inside: avoid !important; 
        }

        /* Force a page break anywhere we need it */
        .page-break { 
            page-break-before: always !important; 
            break-before: page !important; 
        }

        .editorial-box { 
            background: #f8f9fa; 
            border: 1px solid #e9ecef; 
            padding: 20px; 
            margin-bottom: 20px; 
            border-radius: 6px; 
        }

        /* Cover Page Styling */
        .cover-page {
            background: linear-gradient(135deg, #111820 0%, #2c3e50 100%) !important;
            color: white !important;
            padding: 0 !important;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .cover-accent { height: 15px; width: 100%; background: #d35400; }
    </style>
</head>
<body>
<div class="workspace-actions no-print">
    <?php if(session()->get('category') === 'cca'):?>
    <a class="btn-print" href="<?= base_url('cca/downloadPdf/'. esc($stdid)); ?>" target="_blank">
        <?php endif;
        if(session()->get('category') === 'student'):?>
        <a class="btn-print" href="<?= base_url('report/downloadPdf')?>" target="_blank">
            <?php endif;?>
        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/></svg>
        Save as PDF
    </a>
</div>
<?php 
$pageCounter = 1;
// Fallbacks to prevent crashes if certain modules haven't been completed
$advData = $advData ?? [];
// We pull the scores from advData or fallback to the raw controller array
$mbtiScores = $advData['mbti_percentages'] ?? ($mbti['scores'] ?? ['E'=>50,'I'=>50,'S'=>50,'N'=>50,'T'=>50,'F'=>50,'P'=>50,'J'=>50]);

// THE CRITICAL FIX: Pulling Primary Trait directly from the Database Column
$mbtiPrimaryTrait = isset($mbti['trait']) && $mbti['trait'] !== 'Pending' ? $mbti['trait'] : '';

$pctE = $mbtiScores['E'] ?? 50; $pctI = 100 - $pctE;
$pctS = $mbtiScores['S'] ?? 50; $pctN = 100 - $pctS;
$pctT = $mbtiScores['T'] ?? 50; $pctF = 100 - $pctT;
$pctP = $mbtiScores['P'] ?? 50; $pctJ = 100 - $pctP;

// If the database trait is missing, we calculate using Jungian tie-breakers
if (empty($mbtiPrimaryTrait) || strlen($mbtiPrimaryTrait) !== 4) {
    $mbtiPrimaryTrait = ($pctE > 50 ? 'E' : 'I') . ($pctS > 50 ? 'S' : 'N') . ($pctT > 50 ? 'T' : 'F') . ($pctJ > 50 ? 'J' : 'P');
}

// Split the trait into variables perfectly matched with DB result
$trait1 = substr($mbtiPrimaryTrait, 0, 1);
$trait2 = substr($mbtiPrimaryTrait, 1, 1);
$trait3 = substr($mbtiPrimaryTrait, 2, 1);
$trait4 = substr($mbtiPrimaryTrait, 3, 1);
$riasec = $advData['riasec_scores'] ?? [];
$clusters = $advData['cluster_scores'] ?? [];
$paths = $advData['career_paths'] ?? [];
$gardner = $advData['gardner_scores'] ?? [];
$learning = $advData['learning_styles'] ?? [];
$motivators = $advData['motivators'] ?? [];
$skills = $advData['skills'] ?? [];
$academicRoadmap = $advData['academic_roadmap'] ?? [];

// =========================================================================
// NEW: INLINE SVG RADAR CHART GENERATOR
// =========================================================================
if (!function_exists('renderRiasecRadarSVG')) {
    function renderRiasecRadarSVG($studentScores, $targetScores) {
        $size = 360;
        $center = $size / 2;
        $maxRadius = 120;
        $labels = ['Realistic', 'Investigative', 'Artistic', 'Social', 'Enterprising', 'Conventional'];
        
        $svg = '<svg width="100%" height="100%" viewBox="0 0 '.$size.' '.$size.'" xmlns="http://www.w3.org/2000/svg" style="max-width: 400px; display: block; margin: 0 auto;">';
        
        // 1. Draw Background Web (Concentric Polygons)
        for($level=1; $level<=5; $level++) {
            $r = $maxRadius * ($level/5);
            $pts = [];
            foreach($labels as $i => $l) {
                $angle = deg2rad($i * 60);
                $x = $center + $r * sin($angle);
                $y = $center - $r * cos($angle);
                $pts[] = "$x,$y";
            }
            $svg .= '<polygon points="'.implode(' ', $pts).'" fill="none" stroke="#ecf0f1" stroke-width="1.5"/>';
        }
        
        // 2. Draw Axes and Labels
        foreach($labels as $i => $l) {
            $angle = deg2rad($i * 60);
            $x = $center + $maxRadius * sin($angle);
            $y = $center - $maxRadius * cos($angle);
            $svg .= '<line x1="'.$center.'" y1="'.$center.'" x2="'.$x.'" y2="'.$y.'" stroke="#bdc3c7" stroke-width="1.5"/>';
            
            // Positioning Labels
            $lx = $center + ($maxRadius + 25) * sin($angle);
            $ly = $center - ($maxRadius + 15) * cos($angle);
            $anchor = 'middle';
            if ($i == 1 || $i == 2) $anchor = 'start';
            if ($i == 4 || $i == 5) $anchor = 'end';
            
            $svg .= '<text x="'.$lx.'" y="'.$ly.'" text-anchor="'.$anchor.'" fill="#2c3e50" font-size="11" font-weight="bold" font-family="Helvetica, Arial, sans-serif">'.strtoupper($l).'</text>';
        }
        
        // Helper to map scores to X/Y coordinates
        $getPoints = function($scores) use ($labels, $center, $maxRadius) {
            $pts = [];
            foreach($labels as $i => $l) {
                $val = $scores[strtolower($l)] ?? $scores[$l] ?? 0;
                $r = $maxRadius * ($val / 100);
                $angle = deg2rad($i * 60);
                $x = $center + $r * sin($angle);
                $y = $center - $r * cos($angle);
                $pts[] = "$x,$y";
            }
            return implode(' ', $pts);
        };
        
        // 3. Draw Target "Ideal" Profile (Green, Dashed)
        $targetPts = $getPoints($targetScores);
        $svg .= '<polygon points="'.$targetPts.'" fill="rgba(39, 174, 96, 0.15)" stroke="#27ae60" stroke-width="2.5" stroke-dasharray="5,5" style="-webkit-print-color-adjust: exact; print-color-adjust: exact;"/>';
        
        // 4. Draw Student Profile (Blue, Solid)
        $studentPts = $getPoints($studentScores);
        $svg .= '<polygon points="'.$studentPts.'" fill="rgba(41, 128, 185, 0.6)" stroke="#2980b9" stroke-width="2.5" style="-webkit-print-color-adjust: exact; print-color-adjust: exact;"/>';
        
        $svg .= '</svg>';
        return $svg;
    }
}

// =========================================================================
// PREMIUM DUAL-BAR GRAPH GENERATOR
// =========================================================================
if (!function_exists('renderDualBarHTML')) {
    function renderDualBarHTML($leftLabel, $rightLabel, $leftPct, $rightPct, $darkColor, $lightColor) {
        if ($leftPct == 50 && $rightPct == 50) { $leftPct = 49; $rightPct = 51; }
        $isTie = (round($leftPct) == 51 || round($rightPct) == 51);

        $leftBarColor = $lightColor; $rightBarColor = $lightColor;
        $leftTextColor = '#95a5a6'; $rightTextColor = '#95a5a6';

        if ($isTie) {
            $leftTextColor = $darkColor; $rightTextColor = $darkColor;
        } elseif ($leftPct > 51) {
            $leftBarColor = $darkColor; $leftTextColor = $darkColor;
        } elseif ($rightPct > 51) {
            $rightBarColor = $darkColor; $rightTextColor = $darkColor;
        }

        $leftWeight = ($leftPct > 51 || $isTie) ? 'bold' : '600';
        $rightWeight = ($rightPct > 51 || $isTie) ? 'bold' : '600';
        $cleanLeft = trim(preg_replace('/\s*\([^)]*\)/', '', $leftLabel));
        $cleanRight = trim(preg_replace('/\s*\([^)]*\)/', '', $rightLabel));
        $leftVal = round($leftPct); $rightVal = round($rightPct);

        $leftLabelExtra = ($isTie && $leftVal == 51) ? '<div style="font-size: 8pt; color: #7f8c8d; font-weight: normal; margin-top: 2px;">(Balanced)</div>' : '';
        $rightLabelExtra = ($isTie && $rightVal == 51) ? '<div style="font-size: 8pt; color: #7f8c8d; font-weight: normal; margin-top: 2px;">(Balanced)</div>' : '';

        $leftW = $leftPct / 2; $rightW = $rightPct / 2;

        echo '
        <table style="width: 100%; margin-bottom: 35px; border-collapse: collapse; border: none;">
            <tr>
                <td style="width: 20%; text-align: right; padding-right: 20px; vertical-align: middle;">
                    <div style="display: flex; align-items: center; justify-content: flex-end; gap: 12px; color: '.$leftTextColor.';">
                        <div style="text-align: right; line-height: 1.1;">
                            <div style="font-size: 10.5pt; font-weight: '.$leftWeight.'; text-transform: uppercase; letter-spacing: 0.5px;">'.$cleanLeft.'</div>
                            '.$leftLabelExtra.'
                        </div>
                        <div style="font-size: 12pt; font-weight:'.$leftWeight.'; font-family: \'Arial Black\', Arial, sans-serif; letter-spacing: -1px;">'.round($leftPct).'%</div>
                    </div>
                </td>
                
                <td style="width: 60%; vertical-align: middle; position: relative; padding: 1px 0;">
                    <div style="width: 100%; height: 16px; background: #f0f3f4; border-radius: 13px; box-shadow: inset 0 2px 5px rgba(0,0,0,0.06); position: relative; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                        <div style="position: absolute; right: 50%; top: 0; height: 100%; width: '.$leftW.'%; background: '.$leftBarColor.'; border-radius: 13px 0 0 13px; -webkit-print-color-adjust: exact; print-color-adjust: exact;"></div>
                        <div style="position: absolute; left: 50%; top: 0; height: 100%; width: '.$rightW.'%; background: '.$rightBarColor.'; border-radius: 0 13px 13px 0; -webkit-print-color-adjust: exact; print-color-adjust: exact;"></div>
                    </div>
                    <div style="position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); width: 7px; height: 16px; background: #ffffff; border: 2px solid #bdc3c7; border-radius: 7px; z-index: 3; box-shadow: 0 3px 6px rgba(0,0,0,0.15); -webkit-print-color-adjust: exact; print-color-adjust: exact;"></div>
                </td>
                
                <td style="width: 20%; text-align: left; padding-left: 20px; vertical-align: middle;">
                    <div style="display: flex; align-items: center; justify-content: flex-start; gap: 12px; color: '.$rightTextColor.';">
                        <div style="font-size: 12pt; font-weight:'.$rightWeight.'; font-family: \'Arial Black\', Arial, sans-serif; letter-spacing: -1px;">'.round($rightPct).'%</div>
                        <div style="text-align: left; line-height: 1.1;">
                            <div style="font-size: 10.5pt; font-weight: '.$rightWeight.'; text-transform: uppercase; letter-spacing: 0.5px;">'.$cleanRight.'</div>
                            '.$rightLabelExtra.'
                        </div>
                    </div>
                </td>
            </tr>
        </table>';
    }
}
?>

<div class="page cover-page">
    <div class="cover-accent"></div>
    
    <div style="padding: 60px 50px; flex-grow: 1; display: flex; flex-direction: column; justify-content: center;">
        
        <div style="text-align: right; margin-bottom: 80px;">
            <h1 style="font-size: 32pt; margin: 0; color: #fff; letter-spacing: 2px;">PHAROS</h1>
            <p style="margin: 5px 0 0 0; color: #e67e22; font-size: 11pt; letter-spacing: 4px; text-transform: uppercase;">Education Consultancy</p>
        </div>

        <div style="border-left: 5px solid #e67e22; padding-left: 35px; margin-bottom: 80px;">
            <h2 style="font-size: 38pt; margin: 0 0 15px 0; line-height: 1.1; text-transform: uppercase; color: #fff;">Comprehensive<br>Career Analysis</h2>
            <p style="font-size: 13pt; color: #bdc3c7; margin: 0; letter-spacing: 1px;">STRATEGIC BLUEPRINT & PSYCHOMETRIC EVALUATION</p>
        </div>

        <div style="background: rgba(255, 255, 255, 0.05); padding: 40px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1);">
            <p style="font-size: 10pt; color: #95a5a6; text-transform: uppercase; letter-spacing: 2px; margin: 0 0 10px 0;">Prepared Exclusively For</p>
            <h3 style="font-size: 26pt; margin: 0 0 25px 0; color: #fff; text-transform: uppercase;"><?= esc($student_name) ?></h3>
            
            <table style="width: 100%; color: #bdc3c7; font-size: 11pt; border-collapse: collapse; border: none;">
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.1); width: 35%;"><strong>Academic Grade/Age:</strong></td>
                    <td style="padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.1); color: #fff;"><?= esc($age_grade) ?></td>
                </tr>
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.1);"><strong>Candidate Gender:</strong></td>
                    <td style="padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.1); color: #fff;"><?= esc($gender) ?></td>
                </tr>
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.1);"><strong>Registered Email:</strong></td>
                    <td style="padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.1); color: #fff;"><?= esc($user_email) ?></td>
                </tr>
                <tr>
                    <td style="padding: 12px 0;"><strong>Evaluation Date:</strong></td>
                    <td style="padding: 12px 0; color: #fff;"><?= esc($date) ?></td>
                </tr>
            </table>
        </div>
    </div>

    <div style="padding: 30px 50px; background: #111820; display: flex; justify-content: space-between; align-items: center;">
        <p style="margin: 0; font-size: 9pt; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px;">Confidential Document</p>
        <p style="margin: 0; font-size: 9pt; color: #7f8c8d;">Document ID: <?= esc($report_id) ?></p>
    </div>
</div>

<div class="page">
    <div class="page-content">
    <div class="header-strip">PREFACE</div>
    
    <div class="editorial-box no-break" style="font-size: 11pt; line-height: 1.8;">
        <p style="font-weight: bold; font-size: 12pt; color: #2c3e50;">Dear <?= esc($student_name ?? 'Candidate') ?>,</p>
        <p>We, on behalf of Pharos Consultancy, congratulate you on completing the Career Planning Assessment. We understand that navigating educational and career choices can be a complex journey. Pharos caters to your unique needs by providing holistic, data-driven career planning—helping you maximize your potential and ensuring a structured path to a better tomorrow.</p>
        <p>Our proprietary psychometric engine analyzes your behavioral traits, cognitive aptitudes, intrinsic motivators, and vocational interests. By synthesizing this data, we construct a personalized strategy designed to align your innate strengths with real-world occupational demands.</p>
        <p>This dossier is your strategic blueprint. We encourage you to review it thoroughly with your counselors and mentors.</p>
        <p style="margin-top: 30px;">Sincerely,<br><strong class="text-primary" style="font-size: 12pt;">Team<br/> Pharos Education Consultancy</strong></p>
    </div>

    <div style="margin-top: 40px; text-align: center;">
        <img src="<?=base_url("assets/img/preface.webp");?>" alt="Pharos Education">
        <h4 style="color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px;">The Assessment Lifecycle</h4>
        <div style="background: #f4f6f9; padding: 20px; border-radius: 8px; border: 1px solid #ecf0f1; display: inline-block; width: 80%;">
            <p style="font-weight: bold; color: #2980b9;">1. Profiling Analysis &rarr; 2. Option Discovery &rarr; 3. Educational Mapping &rarr; 4. Execution Plan</p>
        </div>
    </div>
    </div>
    <div class="footer">
        <div class="footer-contact">
            <span><i class="fas fa-lighthouse"></i> Pharos Education Consultancy • Confidential Dossier</span>
            <span><i class="fas fa-envelope"></i> support@pharoeducation.in</span>
        </div>
        <div>Page <?= $pageCounter++ ?></div>
    </div>
</div>

<div class="page">
    <div class="page-content">
    <div class="header-strip">YOUR PROFILING STAGE</div>
    <h2>PROFILING </h2>
    
<p>Personal profiling is the first step in career planning. The purpose of profiling is to understand your current
career planning stage. It will help decide your career objective and roadmap. The ultimate aim of the planning
is to take you from the current stage of career planning to the optimized stage of career planning. <br/> 
Personal profiling establishes your current readiness in the career planning lifecycle. Identifying this stage allows 
counselors to calibrate their guidance to your immediate needs.</p>
    
    <div style="margin-top: 60px; text-align: center;">
        <img src="<?=base_url("assets/img/confuse.webp");?>" alt="Pharos Education">
    </div>
<br/>
    <div class="editorial-box no-break" style="border-left: 5px solid #e74c3c; margin-top: 30px;">
        <h3 style="color: #e74c3c; margin-top: 0; text-transform: uppercase;">Current Stage: Exploration / Confused</h3>
        <p><strong>Confused</strong> You are at the confused stage in career planning. We understand that you are having little 
            idea of career planning, but usually confused among various career options. At this stage, you are looking for proper 
            guidance. Generally, at this stage, your career decisions shall be influenced by friends and parents. </p>
        <p><strong>Diagnosis:</strong> You are currently at the exploratory stage in your career planning. You have broad interests 
            but may lack definitive clarity regarding which specific occupational path aligns best with your long-term goals.</p>
        <p><strong>Associated Risks:</strong> Without intervention, there is a risk of misaligned educational investments, career 
            dissatisfaction, or selecting a path based on external pressure rather than internal aptitude.</p>
        <p><strong>Strategic Action Plan:</strong></p>
        <ul>
            <li>Analyze the cognitive and behavioral data in this report.</li>
            <li>Explore the top 3 recommended Career Clusters.</li>
            <li>Align your 11th/12th-grade subject selections with these clusters.</li>
        </ul>
    </div>
    </div>
    <div class="footer">
        <div class="footer-contact">
            <span><i class="fas fa-lighthouse"></i> Pharos Education Consultancy • Confidential Dossier</span>
            <span><i class="fas fa-envelope"></i> support@pharoeducation.in</span>
        </div>
        <div>Page <?= $pageCounter++ ?></div>
    </div>
</div>

<div class="page">
    <div class="header-strip">RESULT OF THE CAREER PERSONALITY </div>
    <div class="editorial-box no-break" style="border-left: 5px solid #e74c3c; margin-top: 30px;">
        <h3 style="color: #2c3e50; margin-top: 0;">Your Personality Type: <span style="color: #e74c3c;"><?= esc($mbtiPrimaryTrait) ?></span></h3>
    <p>Personality Assessment will help you understand yourself as a person. It will help you expand your career
options in alignment with your personality. Self-understanding and awareness can lead you to more
appropriate and rewarding career choices. The Personality Type Model identifies four dimensions of
personality. Each dimension will give you a clear description of your personality. The combination of your most
dominant preferences is used to create your individual personality type. Four dimensions of your personality
are mentioned in this chart. The graph below provides information about the personality type you belong to,
based on the scoring of your responses. Each of the four preferences are based on your answers and are
indicated by a bar chart.</p>
    <p style="margin-bottom: 30px;">The Personality Model identifies four behavioral dimensions. The resulting combinations shape how you interact with your environment, process data, make decisions, and structure your life.</p>
    </div>
    
    <br/><br/><br/><br/>
    
    <?php
    // Render the 4 HTML Bars
    renderDualBarHTML('Introvert (I)', 'Extrovert (E)', $pctI, $pctE, '#2980b9', '#AED6F1');
    renderDualBarHTML('Sensing (S)', 'iNtuitive (N)', $pctS, $pctN, '#e67e22', '#F5CBA7');
    renderDualBarHTML('Thinking (T)', 'Feeling (F)', $pctT, $pctF, '#27ae60', '#ABEBC6');
    renderDualBarHTML('Judging (J)', 'Perceiving (P)', $pctJ, $pctP, '#8e44ad', '#D2B4DE');
    ?>
    <div class="footer">
        <div class="footer-contact">
            <span><i class="fas fa-lighthouse"></i> Pharos Education Consultancy • Confidential Dossier</span>
            <span><i class="fas fa-envelope"></i> support@pharoeducation.in</span>
        </div>
        <div>Page <?= $pageCounter++ ?></div>
    </div>
</div>

<?php
// --- MBTI DATA ARRAYS ---
// This safely stores all the traits and prevents messy HTML if/else statements
$mbtiData = [
    'E' => ['name' => 'Extrovert', 'color' => '#3ab2d6', 'bullets' => ['You are quite talkative, energized and like to spend lots of time with others.', 'Your primary mode of living is focused externally.', 'Your attention may shift quickly when there’s a lot going on.', 'You tend to express your views in a strong and direct way.', 'You quickly adapt to a given situation.', 'You are sometimes described as an attention-seeker.']],
    'I' => ['name' => 'Introvert', 'color' => '#3ab2d6', 'bullets' => ['You mostly get your energy from dealing with ideas, pictures, memories and reactions.', 'You are quiet, reserved and like to spend your time alone.', 'Your primary mode of living is focused internally.', 'You are passionate but not usually aggressive.', 'You are a good listener.', 'You are more of an inside-out person.']],
    'S' => ['name' => 'Sensing', 'color' => '#f3c246', 'bullets' => ['You mostly collect and trust the information that is presented in a detailed and sequential manner.', 'You think more about the present and learn from the past.', 'You like to see the practical use of things and learn best from practice.', 'You notice facts and remember details that are important to you.', 'You solve problems by working through facts until you understand the problem.', 'You create meaning from conscious thought and learn by observation.']],
    'N' => ['name' => 'Intuition', 'color' => '#f3c246', 'bullets' => ['You are very imaginative, open-minded and curious.', 'You prefer to explore and focus on hidden meanings and future possibilities.', 'You are interested in doing things that are new and different.', 'You first like to see the biggest picture, then try to find out facts.', 'You are interested in new things and what might be possible.', 'You solve problems by leaping between different ideas and possibilities.']],
    'T' => ['name' => 'Thinking', 'color' => '#5bb991', 'bullets' => ['You seem to make decisions based on logic rather than the circumstances.', 'You believe telling truth is more important than being tactful.', 'You seem to look for logical explanations or solutions to almost everything.', 'You can often be seen as very task-oriented, uncaring, or indifferent.', 'You are ruled by your head instead of your heart.', 'You are a critical thinker and oriented toward problem solving.']],
    'F' => ['name' => 'Feeling', 'color' => '#5bb991', 'bullets' => ['You seem to make decisions based on your values or the feelings of others involved.', 'You seem to be ruled by your heart instead of your head.', 'In your relationships, you appear caring, warm, and tactful.', 'You look for what is important to others and express concern for others.', 'You tend to judge situations and others based on feelings and circumstances.', 'You seek to please others and want to be appreciated.']],
    'P' => ['name' => 'Perceiving', 'color' => '#9a7ea7', 'bullets' => ['You seem to prefer a flexible and spontaneous way of life.', 'You prefer to adapt to the world rather than organizing it.', 'You like staying open to new experiences and information.', 'You like to approach work as play or mix of work and play.', 'You appear to be casual and like to keep plans to a minimum.', 'You are a random thinker who prefers to keep his/her options open.', 'You are spontaneous and often juggle several tasks at once.']],
    'J' => ['name' => 'Judging', 'color' => '#9a7ea7', 'bullets' => ['You prefer a planned and organized approach to life and work.', 'You like to have things decided, structured, and settled.', 'You feel much more comfortable when decisions are made.', 'You like to make lists of things to do and stick to them.', 'You like to get your work done before playing or relaxing.', 'You plan work to avoid rushing just before a deadline.', 'You focus primarily on completing tasks and achieving goals.']]
];

function renderMBTIBlock($letter, $data, $question) {
    if (!isset($data)) return;
    echo '
    <div class="no-break" style="background: '.$data['color'].'; border-radius: 12px; padding: 20px 20px 15px 20px; margin-bottom: 25px;">
        <h3 style="color: white; margin-top: 0; margin-bottom: 20px; font-size: 13pt; margin-left: 10px;">'.$question.'</h3>
        <table style="width: 100%; border-collapse: collapse; border: none;">
            <tr>
                <td style="width: 25%; text-align: center; vertical-align: middle; color: white;">
                    <div style="font-size: 70pt; font-weight: bold; line-height: 1; font-family: \'Arial Black\', Impact, sans-serif;">'.$letter.'</div>
                    <div style="font-size: 14pt; font-weight: bold; margin-top: 5px;">'.$data['name'].'</div>
                </td>
                <td style="width: 75%; vertical-align: middle;">
                    <div style="background: white; border-radius: 8px; padding: 20px; min-height: 120px;">
                        <ul style="margin: 0; padding-left: 20px; font-size: 10pt; color: #111; line-height: 1.6; font-weight: 500;">';
                        foreach($data['bullets'] as $bullet) {
                            echo '<li style="margin-bottom: 6px;">'.$bullet.'</li>';
                        }
    echo '              </ul>
                    </div>
                </td>
            </tr>
        </table>
    </div>';
}
?>

<div class="page">
    <div class="header-strip">YOUR CAREER PERSONALITY ANALYSIS</div>
    <div style="margin-top: 40px;">
        <?php 
        renderMBTIBlock($trait1, $mbtiData[$trait1] ?? null, 'Where do you prefer to focus your energy and attention?');
        renderMBTIBlock($trait2, $mbtiData[$trait2] ?? null, 'How do you grasp and process the information?');
        ?>
    </div>
   <div class="footer">
        <div class="footer-contact">
            <span><i class="fas fa-lighthouse"></i> Pharos Education Consultancy • Confidential Dossier</span>
            <span><i class="fas fa-envelope"></i> support@pharoeducation.in</span>
        </div>
        <div>Page <?= $pageCounter++ ?></div>
    </div>
</div>

<div class="page">
    <div class="header-strip">YOUR CAREER PERSONALITY ANALYSIS</div>
    <div style="margin-top: 40px;">
        <?php 
        renderMBTIBlock($trait3, $mbtiData[$trait3] ?? null, 'How do you make decisions?');
        renderMBTIBlock($trait4, $mbtiData[$trait4] ?? null, 'How do you prefer to plan your work?');
        
        // --- BONUS: DYNAMIC STRENGTHS CALCULATOR ---
        // Find the user's highest percentage trait to display their core strength
        $allTraits = ['E'=>$pctE, 'I'=>$pctI, 'S'=>$pctS, 'N'=>$pctN, 'T'=>$pctT, 'F'=>$pctF, 'P'=>$pctP, 'J'=>$pctJ];
        arsort($allTraits);
        $topStrengthLetter = array_key_first($allTraits);
        
        $strengthsMap = [
            'E' => ['Rational and expressive', 'Highly sociable and communicative', 'Full of life and energy', 'Broad network builder', 'Quick to adapt and take action'],
            'I' => ['Deep, focused thinker', 'Excellent listener and observer', 'Highly independent worker', 'Processes complex ideas internally', 'Calm and steady presence'],
            'S' => ['Rational and practical', 'Sociable and grounded', 'Full of life and energy', 'Prefer to communicate clearly, with direct and factual answers', 'Love to experiment with new practical solutions'],
            'N' => ['Highly imaginative and innovative', 'Excellent at seeing the big picture', 'Future-oriented and strategic', 'Creative problem solver', 'Notices patterns and hidden meanings'],
            'T' => ['Highly logical and objective', 'Fair and consistent decision maker', 'Excellent critical thinker', 'Unbiased problem solver', 'Values truth and accuracy above all'],
            'F' => ['Highly empathetic and compassionate', 'Values harmony and cooperation', 'Emotionally intelligent', 'Supportive and encouraging to others', 'Makes decisions aligned with core values'],
            'P' => ['Highly adaptable and flexible', 'Spontaneous and open-minded', 'Excellent in crisis or changing situations', 'Resourceful and quick-thinking', 'Comfortable with ambiguity'],
            'J' => ['Highly organized and reliable', 'Structured and methodical', 'Decisive and clear-headed', 'Goal-oriented and driven', 'Excellent at managing time and projects']
        ];
        
        // Render the Strengths Block
        $strengthData = [
            'name' => 'Strength', 
            'color' => '#d98565', // The warm orange color from your screenshot
            'bullets' => $strengthsMap[$topStrengthLetter]
        ];
        
        renderMBTIBlock($topStrengthLetter, $strengthData, 'Your Core Strengths');
        ?>
    </div>
    <div class="footer">
        <div class="footer-contact">
            <span><i class="fas fa-lighthouse"></i> Pharos Education Consultancy • Confidential Dossier</span>
            <span><i class="fas fa-envelope"></i> support@pharoeducation.in</span>
        </div>
        <div>Page <?= $pageCounter++ ?></div>
    </div>
</div>

<div class="page">
    <div class="header-strip">YOUR CAREER INTEREST TYPES (RIASEC)</div>
    <p style="margin-bottom: 30px;">The Holland Code (RIASEC) measures six broad vocational interest patterns. Understanding your top interests helps identify environments where you will be most engaged and satisfied.</p>
    <p>The Career Interest Assessment will help you understand which careers might be the best fit for you. It is
meant to help you find careers that you might enjoy. Understanding your Top career interest will help you
identify a career focus and begin your career planning and career exploration process.
The Career Interest Assessment (CIA) measures six broad interest patterns that can be used to describe your
career interest. Most people’s interests are reflected by two or three themes, combined to form a cluster of
interests. This career interest is directly linked to your occupational interest.</p>
    <div class="editorial-box no-break" style="padding-top: 30px; padding-bottom: 30px;">
        <?php 
        $r_colors = ['Realistic'=>'#16a085', 'Investigative'=>'#2980b9', 'Artistic'=>'#e67e22', 'Conventional'=>'#8e44ad', 'Enterprising'=>'#00acc1', 'Social'=>'#95a5a6'];
        if(!empty($riasec)):
            foreach($riasec as $trait => $score): 
                // CRITICAL FIX: Capitalize the lowercase trait so it matches the color array!
                $displayTrait = ucfirst($trait); 
        ?>
        <table class="bar-table">
            <tr>
                <td class="bar-label"><?= esc($displayTrait) ?></td>
                <td class="bar-track-td">
                    <div class="bar-track">
                        <div class="bar-val" style="width: <?= esc($score) ?>%; background: <?= $r_colors[$displayTrait] ?? '#ccc' ?>;"></div>
                    </div>
                </td>
                <td class="bar-score"><?= esc($score) ?>%</td>
            </tr>
        </table>
        <?php endforeach; else: echo "<p>No RIASEC data available.</p>"; endif; ?>
    </div>
    <div class="footer">
        <div class="footer-contact">
            <span><i class="fas fa-lighthouse"></i> Pharos Education Consultancy • Confidential Dossier</span>
            <span><i class="fas fa-envelope"></i> support@pharoeducation.in</span>
        </div>
        <div>Page <?= $pageCounter++ ?></div>
    </div>
</div>

<div class="page">
    <div class="header-strip">YOUR CAREER INTEREST ANALYSIS</div>
    
    <?php 
    // 1. Dynamic Content Map for all 6 Holland Traits
    $riasecContent = [
        'Realistic' => [
            'color' => '#3ba776', // Green
            'bullets' => [
                'You are active and stable and enjoy hands-on or manual activities.',
                'You prefer to work with things rather than ideas and people.',
                'You tend to communicate in a frank, direct manner and value material things.',
                'You may be uncomfortable or less adept with human relations.',
                'You value practical things that you can see and touch.',
                'You have good skills at handling tools, mechanical drawings, machines or animals.'
            ]
        ],
        'Investigative' => [
            'color' => '#3c6e91', // Blue
            'bullets' => [
                'You are analytical, intellectual, observant and enjoy research.',
                'You enjoy using logic and solving complex problems.',
                'You are interested in occupations that require observation, learning and investigation.',
                'You are introspective and focused on creative problem solving.',
                'You prefer working with ideas and using technology.'
            ]
        ],
        'Artistic' => [
            'color' => '#f47b34', // Orange
            'bullets' => [
                'You are imaginative and enjoy creative activities.',
                'You encourage originality and use of imagination in a flexible, unstructured setting.',
                'You are generally impulsive and emotional.',
                'You tend to communicate in a very expressive and open manner.',
                'You seek opportunities for self-expression through artistic creation.',
                'You like to work with ideas and things.'
            ]
        ],
        'Social' => [
            'color' => '#e65141', // Red/Coral
            'bullets' => [
                'You are helpful, friendly, and trustworthy.',
                'You prefer working with and assisting people.',
                'You tend to communicate in a warm, tactful manner.',
                'You value serving others and social justice.',
                'You excel at teaching, counseling, nursing, or giving information.',
                'You solve problems by discussing them with others.'
            ]
        ],
        'Enterprising' => [
            'color' => '#eab54d', // Yellow/Gold
            'bullets' => [
                'You are ambitious, energetic, and sociable.',
                'You prefer to lead, persuade, and sell things or ideas.',
                'You tend to communicate in an assertive, confident manner.',
                'You value success, politics, leadership, and wealth.',
                'You are skilled at public speaking, managing others, and business strategy.'
            ]
        ],
        'Conventional' => [
            'color' => '#8e44ad', // Purple
            'bullets' => [
                'You are organized, detail-oriented, and reliable.',
                'You prefer working with data, numbers, and established procedures.',
                'You tend to communicate in a highly structured, precise manner.',
                'You value accuracy, stability, and efficiency.',
                'You excel at keeping records, financial tracking, and operating computer systems.'
            ]
        ]
    ];

    if(!empty($riasec)):
        // 2. Extract Top 3 Traits (Sanitizing keys to ucfirst)
        $topTraitsRaw = array_keys($riasec);
        $top3 = [];
        foreach(array_slice($topTraitsRaw, 0, 3) as $t) {
            $top3[] = ucfirst(trim($t));
        }

        // 3. Render Top 3 Dynamic Blocks
        foreach($top3 as $trait):
            $score = $riasec[strtolower($trait)] ?? $riasec[$trait] ?? 0;
            // Add "-HIGH" if the score is dominant (e.g., over 50%)
            $suffix = ($score >= 50) ? '-HIGH' : ''; 
            
            $data = $riasecContent[$trait] ?? $riasecContent['Realistic']; // Fallback
            $color = $data['color'];
            $letter = strtoupper(substr($trait, 0, 1));
    ?>
    
    <div class="no-break" style="background: <?= $color ?>; border-radius: 12px; padding: 15px; margin-bottom: 25px;">
        <table style="width: 100%; border-collapse: collapse; border: none;">
            <tr>
                <td style="width: 25%; text-align: center; vertical-align: middle; color: white;">
                    <div style="font-size: 65pt; font-weight: bold; line-height: 1; font-family: 'Arial Black', Impact, sans-serif;"><?= $letter ?></div>
                    <div style="font-size: 13pt; font-weight: bold; margin-top: 5px;"><?= $trait ?><?= $suffix ?></div>
                </td>
                <td style="width: 75%; vertical-align: middle;">
                    <div style="background: white; border-radius: 8px; padding: 20px; min-height: 120px;">
                        <ul style="margin: 0; padding-left: 20px; font-size: 10pt; color: #111; line-height: 1.6; font-weight: 500;">
                            <?php foreach($data['bullets'] as $bullet): ?>
                                <li style="margin-bottom: 6px;"><?= $bullet ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <?php 
        endforeach; 
    else: 
    ?>
        <div class="editorial-box"><p>No Career Interest data available.</p></div>
    <?php endif; ?>

    <div class="footer">
        <div class="footer-contact">
            <span><i class="fas fa-lighthouse"></i> Pharos Education Consultancy • Confidential Dossier</span>
            <span><i class="fas fa-envelope"></i> support@pharoeducation.in</span>
        </div>
        <div>Page <?= $pageCounter++ ?></div>
    </div>
</div>
<div class="page">
    <div class="header-strip">YOUR CAREER MOTIVATOR TYPES</div>
    
    <p style="margin-bottom: 40px; line-height: 1.6; color: #444; font-size: 10.5pt;">
        Values are the things that are most important to us in our lives and careers. Our values are formed in a variety of ways through our life experiences, our feelings and our families. Being aware of your intrinsic motivators is critical because a career choice that aligns with your core beliefs is significantly more likely to be a lasting, positive choice.
    </p>
    
    <div class="no-break" style="background: #ffffff; border: 1px solid #ecf0f1; border-radius: 8px; padding: 40px 30px; box-shadow: 0 4px 10px rgba(0,0,0,0.02);">
        <?php 
        // --- SMART MOTIVATOR DICTIONARY ---
        $motivatorDescriptions = [
            'achievement' => 'Driven by overcoming challenges, mastery of skills, and delivering tangible results.',
            'independence' => 'Values autonomy, self-direction, and the freedom to execute tasks independently.',
            'recognition' => 'Motivated by career advancement, prestige, leadership, and external validation.',
            'relationship' => 'Thrives on teamwork, altruism, harmony, and creating a positive social impact.',
            'support' => 'Seeks structured environments, competent leadership, and strong organizational backing.',
            'condition' => 'Prioritizes job security, fair compensation, and a healthy work-life balance.',
            'learning' => 'Driven by continuous intellectual growth, curiosity, and acquiring new skill sets.',
            'creativity' => 'Motivated by innovation, artistic expression, and out-of-the-box problem solving.'
        ];

        // Dynamic color palette for a premium look
        $mColors = ['#c0392b', '#d35400', '#e67e22', '#f39c12', '#27ae60', '#2980b9', '#8e44ad', '#34495e'];

        if(!empty($motivators)):
            arsort($motivators); // Sort highest to lowest
            $i = 0;
            
            foreach($motivators as $motivator => $score): 
                $color = $mColors[$i % count($mColors)];
                $i++;
                
                // Fuzzy match the description to the motivator name
                $motDesc = 'Core intrinsic value driving professional engagement and long-term career satisfaction.';
                $lowerMot = strtolower($motivator);
                foreach($motivatorDescriptions as $keyword => $text) {
                    if (strpos($lowerMot, $keyword) !== false) {
                        $motDesc = $text;
                        break;
                    }
                }
        ?>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 28px; border: none;">
            <tr>
                <td style="width: 32%; text-align: right; padding-right: 20px; vertical-align: middle;">
                    <div style="font-weight: bold; font-size: 10.5pt; color: <?= $color ?>; text-transform: uppercase;">
                        <?= esc($motivator) ?>
                    </div>
                    <div style="font-size: 8.5pt; color: #7f8c8d; margin-top: 4px; line-height: 1.4;">
                        <?= esc($motDesc) ?>
                    </div>
                </td>
                
                <td style="width: 58%; vertical-align: middle;">
                    <div style="width: 100%; height: 22px; background: #f4f6f9; border-radius: 4px; overflow: hidden; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                        <div style="width: <?= esc($score) ?>%; height: 100%; background: <?= $color ?>; border-radius: 4px 0 0 4px; -webkit-print-color-adjust: exact; print-color-adjust: exact;"></div>
                    </div>
                </td>
                
                <td style="width: 10%; text-align: left; padding-left: 15px; font-weight: bold; font-size: 11pt; color: <?= $color ?>;">
                    <?= esc($score) ?>%
                </td>
            </tr>
        </table>
        <?php 
            endforeach; 
        else:
        ?>
            <p style="text-align: center; color: #7f8c8d; font-style: italic;">Motivator profiling data is currently pending.</p>
        <?php endif; ?>
    </div>
    
    <div class="footer">
        <span style="float: left; font-weight: 500;">Pharos Education Consultancy • Confidential Dossier</span>
        <span style="float: right; font-weight: bold; color: #34495e;">Page <?= $pageCounter++ ?></span>
        <div style="clear: both;"></div>
    </div>
</div>

<div class="page">
    <div class="header-strip">YOUR CAREER MOTIVATOR ANALYSIS</div>
    
    <?php 
    $topMotivator = array_keys($motivators)[0] ?? 'Achievement'; 
    $topColor = $mColors[0] ?? '#c0392b';

    // --- SMART ANALYSIS DICTIONARY ---
    // Deep psychological profiling based on their #1 intrinsic value
    $motivatorAnalysis = [
        'achievement' => [
            'desc' => 'You are fundamentally driven by results, mastery, and the successful completion of complex tasks. Workplaces that fail to provide challenging goals or fail to utilize your core abilities will quickly lead to disengagement and burnout.',
            'strategies' => [
                'Target roles that offer clear KPIs and performance-based advancement.',
                'Seek out environments that allow you to take projects from conception to completion.',
                'Avoid highly bureaucratic roles where results are slowed down by excessive red tape.',
                'Look for cultures that actively celebrate "wins" and tangible outputs.'
            ]
        ],
        'independence' => [
            'desc' => 'Your primary psychological driver is autonomy. You thrive when given the trust to execute tasks in your own way. Micro-management and rigid, inflexible schedules will severely restrict your potential and morale.',
            'strategies' => [
                'Seek roles offering flexible working hours, remote options, or entrepreneurial freedom.',
                'Target project-based work where you are judged on the final output, not the process.',
                'Consider freelance, consulting, or highly autonomous corporate departments.',
                'Avoid heavily monitored, strictly hierarchical, or heavily regimented roles.'
            ]
        ],
        'recognition' => [
            'desc' => 'You are highly motivated by prestige, leadership potential, and visible career trajectories. Acknowledgment of your contributions is critical to your self-esteem and professional drive.',
            'strategies' => [
                'Target large, prestigious organizations with well-defined corporate ladders.',
                'Seek roles that put you in public-facing or high-visibility leadership positions.',
                'Ensure the career paths you choose have distinct job titles and promotion metrics.',
                'Avoid "behind-the-scenes" or entirely supportive roles with no upward mobility.'
            ]
        ],
        'relationship' => [
            'desc' => 'You evaluate career success based on the quality of human connections and the positive impact you have on others. A toxic or highly competitive culture will drain your energy faster than a heavy workload.',
            'strategies' => [
                'Target organizations known for their strong, supportive, and inclusive cultures.',
                'Seek out roles focused on client success, team building, or social impact.',
                'Prioritize the "who" over the "what" when interviewing for future roles.',
                'Avoid highly cutthroat, isolated, or purely transactional sales environments.'
            ]
        ],
        'support' => [
            'desc' => 'You thrive in environments where leadership provides clear direction, fair policies, and solid backing. You value knowing that the organization stands behind its employees and maintains high ethical standards.',
            'strategies' => [
                'Seek out well-established companies with strong HR policies and training programs.',
                'Look for roles with clear job descriptions and accessible, competent management.',
                'Prioritize stability and organizational reputation over risky startups.',
                'Avoid chaotic work environments with high turnover or absentee leadership.'
            ]
        ],
        'condition' => [
            'desc' => 'Your primary focus is the holistic quality of your working life. Job security, environmental comfort, fair compensation, and preserving your physical and mental health are your non-negotiables.',
            'strategies' => [
                'Target industries with strong union backing or historically stable employment rates.',
                'Prioritize comprehensive benefits packages (healthcare, retirement) over base salary.',
                'Seek roles with predictable hours to protect your personal and family time.',
                'Avoid "hustle-culture" jobs requiring constant overtime or high physical stress.'
            ]
        ]
    ];
    
    // Fuzzy matching for the analysis text
    $lowerTopMot = strtolower($topMotivator);
    $analysisData = $motivatorAnalysis['achievement']; // Default fallback
    foreach($motivatorAnalysis as $keyword => $data) {
        if (strpos($lowerTopMot, $keyword) !== false) {
            $analysisData = $data;
            break;
        }
    }
    ?>
    
    <div class="editorial-box no-break" style="border-left: 6px solid <?= $topColor ?>; padding: 35px; background: #fdfdfd; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
        <h3 style="color: <?= $topColor ?>; text-transform: uppercase; font-size: 15pt; margin-top: 0; margin-bottom: 15px;">
            Primary Driver: <?= esc($topMotivator) ?>
        </h3>
        
        <p style="font-size: 10.5pt; color: #444; line-height: 1.7; margin-bottom: 30px;">
            <?= esc($analysisData['desc']) ?>
        </p>
        
        <div style="background: rgba(0,0,0,0.02); padding: 25px; border-radius: 8px; border: 1px solid #eee;">
            <p style="font-size: 11pt; color: #2c3e50; font-weight: bold; text-transform: uppercase; margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid #ddd; padding-bottom: 8px;">
                <i class="fas fa-compass text-primary" style="margin-right: 8px;"></i> Strategic Career Alignment
            </p>
            <ul style="color: #34495e; font-size: 10pt; line-height: 1.8; margin: 0; padding-left: 20px;">
                <?php foreach($analysisData['strategies'] as $strat): ?>
                    <li style="margin-bottom: 10px;"><?= esc($strat) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    
    <div class="footer">
        <span style="float: left; font-weight: 500;">Pharos Education Consultancy • Confidential Dossier</span>
        <span style="float: right; font-weight: bold; color: #34495e;">Page <?= $pageCounter++ ?></span>
        <div style="clear: both;"></div>
    </div>
</div>


<div class="page">
    <div class="header-strip">YOUR LEARNING STYLE TYPES (VARK)</div>
    
    <p style="margin-bottom: 40px; line-height: 1.6; color: #444; font-size: 10.5pt;">
        Understanding your learning style helps you study smarter, not harder. The VARK model identifies your primary preferences for taking in and processing new academic information. Most people have a dominant style, while others are multimodal and benefit from a combination of methods.
    </p>
    
    <div class="no-break" style="background: #ffffff; border: 1px solid #ecf0f1; border-radius: 8px; padding: 40px 30px; box-shadow: 0 4px 10px rgba(0,0,0,0.02);">
        <?php 
        $varkColors = [
            'Auditory' => '#27ae60',     // Green
            'Read & Write' => '#2980b9', // Blue
            'Visual' => '#e67e22',       // Orange
            'Kinesthetic' => '#8e44ad'   // Purple
        ];

        // --- SMART VARK DICTIONARY ---
        $varkDescriptions = [
            'Auditory' => 'Retains information optimally through listening, speaking, lectures, and verbal discussions.',
            'Read & Write' => 'Processes knowledge best through text-based input, extensive reading, and detailed note-taking.',
            'Visual' => 'Learns most effectively through spatial understanding, diagrams, charts, and visual media.',
            'Kinesthetic' => 'Requires tactile engagement, hands-on experience, and physical movement to anchor learning.'
        ];

        if(!empty($learning)):
            arsort($learning); // Sort highest to lowest
            
            foreach($learning as $style => $score): 
                $color = $varkColors[$style] ?? '#7f8c8d';
                $desc = $varkDescriptions[$style] ?? 'Processes information through specific sensory modalities.';
        ?>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 28px; border: none;">
            <tr>
                <td style="width: 32%; text-align: right; padding-right: 20px; vertical-align: middle;">
                    <div style="font-weight: bold; font-size: 10.5pt; color: <?= $color ?>; text-transform: uppercase;">
                        <?= esc($style) ?>
                    </div>
                    <div style="font-size: 8.5pt; color: #7f8c8d; margin-top: 4px; line-height: 1.4;">
                        <?= esc($desc) ?>
                    </div>
                </td>
                
                <td style="width: 58%; vertical-align: middle;">
                    <div style="width: 100%; height: 22px; background: #f4f6f9; border-radius: 4px; overflow: hidden; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                        <div style="width: <?= esc($score) ?>%; height: 100%; background: <?= $color ?>; border-radius: 4px 0 0 4px; -webkit-print-color-adjust: exact; print-color-adjust: exact;"></div>
                    </div>
                </td>
                
                <td style="width: 10%; text-align: left; padding-left: 15px; font-weight: bold; font-size: 11pt; color: <?= $color ?>;">
                    <?= esc($score) ?>%
                </td>
            </tr>
        </table>
        <?php 
            endforeach; 
        else:
        ?>
            <p style="text-align: center; color: #7f8c8d; font-style: italic;">Learning Style data is currently pending.</p>
        <?php endif; ?>
    </div>
    
    <div class="footer">
        <span style="float: left; font-weight: 500;">Pharos Education Consultancy • Confidential Dossier</span>
        <span style="float: right; font-weight: bold; color: #34495e;">Page <?= $pageCounter++ ?></span>
        <div style="clear: both;"></div>
    </div>
</div>

<div class="page">
    <div class="header-strip">YOUR LEARNING STYLE ANALYSIS</div>
    
    <?php 
    $topLearning = array_keys($learning)[0] ?? 'Auditory'; 
    $topColor = $varkColors[$topLearning] ?? '#333';

    // --- SMART STRATEGY DICTIONARY ---
    // Populates highly specific, actionable study plans based on their winning trait
    $varkStrategies = [
        'Auditory' => [
            'desc' => 'You possess a high sensitivity to spoken language and acoustic nuances. You learn best when information is presented vocally rather than visually.',
            'strategies' => [
                'Record important lectures and listen to them during your commute or downtime.',
                'Read complex textbook passages out loud to anchor the information acoustically.',
                'Participate actively in study groups and debate concepts with peers to reinforce memory.',
                'Use mnemonic devices, rhymes, or rhythms to memorize difficult sequences.'
            ]
        ],
        'Read & Write' => [
            'desc' => 'You have a strong affinity for the written word. You process and retain information most effectively when interacting with structured text, lists, and manuals.',
            'strategies' => [
                'Rewrite and condense your lecture notes into structured, bulleted summaries.',
                'Translate diagrams and charts into descriptive paragraphs.',
                'Utilize multiple textbooks and written resources to gain deeper context.',
                'Write out practice answers and essays repeatedly to build retention.'
            ]
        ],
        'Visual' => [
            'desc' => 'You have a strong spatial memory and process information best when it is presented graphically. You rely on visual cues to understand complex relationships.',
            'strategies' => [
                'Replace standard text notes with mind maps, flowcharts, and diagrams.',
                'Use highlighters and color-coded tabs to categorize different types of information.',
                'Watch video tutorials and documentaries related to your subject matter.',
                'Visualize structures and environments in your mind while reading.'
            ]
        ],
        'Kinesthetic' => [
            'desc' => 'You are a practical, hands-on learner. You anchor theoretical knowledge best when you can physically manipulate objects, move around, or apply it to reality.',
            'strategies' => [
                'Build physical models, use flashcards, and engage in interactive lab work.',
                'Pace the room or use a standing desk while reading or memorizing facts.',
                'Take frequent, short study breaks to prevent physical restlessness.',
                'Relate abstract concepts to real-world, personal experiences and case studies.'
            ]
        ]
    ];
    
    $analysis = $varkStrategies[$topLearning] ?? $varkStrategies['Auditory'];
    ?>
    
    <div class="editorial-box no-break" style="border-left: 6px solid <?= $topColor ?>; padding: 35px; background: #fdfdfd; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
        <h3 style="color: <?= $topColor ?>; text-transform: uppercase; font-size: 15pt; margin-top: 0; margin-bottom: 15px;">
            Dominant Style: <?= esc($topLearning) ?>
        </h3>
        
        <p style="font-size: 10.5pt; color: #444; line-height: 1.7; margin-bottom: 30px;">
            <?= esc($analysis['desc']) ?>
        </p>
        
        <div style="background: rgba(0,0,0,0.02); padding: 25px; border-radius: 8px; border: 1px solid #eee;">
            <p style="font-size: 11pt; color: #2c3e50; font-weight: bold; text-transform: uppercase; margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid #ddd; padding-bottom: 8px;">
                <i class="fas fa-bullseye text-primary" style="margin-right: 8px;"></i> Strategic Execution for Academic Growth
            </p>
            <ul style="color: #34495e; font-size: 10pt; line-height: 1.8; margin: 0; padding-left: 20px;">
                <?php foreach($analysis['strategies'] as $strat): ?>
                    <li style="margin-bottom: 10px;"><?= esc($strat) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    
    <div class="footer">
        <span style="float: left; font-weight: 500;">Pharos Education Consultancy • Confidential Dossier</span>
        <span style="float: right; font-weight: bold; color: #34495e;">Page <?= $pageCounter++ ?></span>
        <div style="clear: both;"></div>
    </div>
</div>


    <div class="page">
    <div class="header-strip">YOUR MULTIPLE INTELLIGENCES (GARDNER)</div>
    
    <p style="margin-bottom: 30px; line-height: 1.6; color: #444; font-size: 10pt;">
        Dr. Howard Gardner's Theory of Multiple Intelligences suggests that traditional IQ testing does not fully capture human ability. We all possess different kinds of minds and learn, remember, perform, and understand in different ways. Understanding your dominant intelligences helps identify careers where your natural talents will be highly valued.
    </p>
    
    <div class="no-break" style="background: #fdfdfd; border: 1px solid #eaeaea; border-radius: 8px; padding: 35px 30px;">
        <?php 
        // 1. Clinical Dictionary for Gardner Traits
        $g_info = [
            'Linguistic' => ['color' => '#2980b9', 'desc' => 'Words, language, and writing.'],
            'Logical' => ['color' => '#27ae60', 'desc' => 'Logic, abstraction, reasoning, and numbers.'],
            'Spatial' => ['color' => '#8e44ad', 'desc' => 'Visualizing the world in 3D.'],
            'Kinesthetic' => ['color' => '#e67e22', 'desc' => 'Physical coordination and dexterity.'],
            'Musical' => ['color' => '#c0392b', 'desc' => 'Rhythm, pitch, meter, and tone.'],
            'Interpersonal' => ['color' => '#16a085', 'desc' => 'Sensing people\'s feelings and motives.'],
            'Intrapersonal' => ['color' => '#34495e', 'desc' => 'Understanding yourself and your goals.'],
            'Naturalistic' => ['color' => '#27ae60', 'desc' => 'Understanding living things and reading nature.']
        ];

        if(!empty($gardner)):
            arsort($gardner); // Sort highest to lowest
            
            foreach($gardner as $trait => $score): 
                // Smart Keyword Matcher for colors and descriptions
                $matchedColor = '#7f8c8d';
                $matchedDesc = 'Cognitive ability indicator.';
                $displayName = ucfirst($trait);
                
                foreach($g_info as $key => $info) {
                    if (strpos(strtolower($trait), strtolower($key)) !== false) {
                        $matchedColor = $info['color'];
                        $matchedDesc = $info['desc'];
                        break;
                    }
                }
        ?>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; border: none;">
            <tr>
                <td style="width: 35%; text-align: right; padding-right: 15px;">
                    <div style="font-weight: bold; font-size: 10.5pt; color: <?= $matchedColor ?>;">
                        <?= esc($displayName) ?>
                    </div>
                    <div style="font-size: 8pt; color: #7f8c8d; margin-top: 2px;">
                        <?= esc($matchedDesc) ?>
                    </div>
                </td>
                
                <td style="width: 55%; vertical-align: middle;">
                    <div style="width: 100%; height: 18px; background: #ecf0f1; border-radius: 4px; overflow: hidden; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                        <div style="width: <?= esc($score) ?>%; height: 100%; background: <?= $matchedColor ?>; border-radius: 4px 0 0 4px; -webkit-print-color-adjust: exact; print-color-adjust: exact;"></div>
                    </div>
                </td>
                
                <td style="width: 10%; text-align: left; padding-left: 15px; font-weight: bold; font-size: 10pt; color: <?= $matchedColor ?>;">
                    <?= esc($score) ?>%
                </td>
            </tr>
        </table>
        <?php 
            endforeach; 
        else: 
        ?>
            <p style="text-align: center; color: #7f8c8d; font-style: italic;">Multiple Intelligences data is currently pending.</p>
        <?php endif; ?>
    </div>
    
    <div class="footer">
        <div class="footer-contact">
            <span><i class="fas fa-lighthouse"></i> Pharos Education Consultancy • Confidential Dossier</span>
            <span><i class="fas fa-envelope"></i> support@pharoeducation.in</span>
        </div>
        <div>Page <?= $pageCounter++ ?></div>
    </div>
</div>

<div class="page">
    <div class="header-strip">YOUR SKILLS AND ABILITIES (APTITUDE)</div>
    
    <table class="layout-table" style="margin-bottom: 25px;">
        <tr>
            <td width="70%">
                <p>Cognitive aptitudes measure your raw capacity to perform specific types of tasks. 
                    The skills & abilities scores will help us to explore and identify different ways to reshape your career direction.
                    This simple graph shows how you have scored on each of these skills and abilities. The graph on the top will
                    show the average score of your overall skills and abilities.
                </p>
            </td>
            <td width="30%" class="text-center" style="background: #fdfdfd; padding: 15px; border: 1px solid #e0e0e0; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <p style="margin: 0; font-size: 9pt; text-transform: uppercase; color: #7f8c8d; font-weight: bold;">Overall Cognitive Score</p>
                <p style="color: #e67e22; font-weight: bold; font-size: 16pt; margin: 5px 0 0 0;">
                    <?= esc($advData['skills_overall']['score'] ?? 0) ?>% <br>
                    <span style="font-size: 11pt; color: #2c3e50;"><?= esc($advData['skills_overall']['band'] ?? 'N/A') ?></span>
                </p>
            </td>
        </tr>
    </table>

    <table class="layout-table">
        <?php 
        // --- SMART APTITUDE DICTIONARY ---
        // Maps specific skill keywords to professional clinical descriptions
        $aptitudeDescriptions = [
            'numerical' => 'Measures capacity to understand, analyze, and manipulate mathematical data. Essential for finance, engineering, and data sciences.',
            'logical' => 'Assesses capability to identify patterns, evaluate arguments, and solve complex problems. Crucial for IT, law, and research fields.',
            'verbal' => 'Evaluates comprehension of written information and articulation of ideas. Key for journalism, management, and communications.',
            'spatial' => 'Measures capacity to visualize and mentally manipulate 2D and 3D objects. Vital for architecture, design, and specialized medical fields.',
            'administrative' => 'Assesses attention to detail, organizational capacity, and ability to follow procedures efficiently. Important for operations and management.',
            'leadership' => 'Evaluates natural propensity to guide, influence, and take charge of group dynamics. Essential for executive and entrepreneurial roles.',
            'social' => 'Measures empathy, emotional intelligence, and ability to navigate human interactions. Vital for human resources, psychology, and sales.',
            'mechanical' => 'Assesses understanding of basic physical principles, machinery, and structural interactions. Important for manufacturing and mechanics.'
        ];

        if(!empty($skills)):
            $skillChunks = array_chunk($skills, 2, true);
            foreach($skillChunks as $row): 
        ?>
        
            <?php foreach($row as $name => $data): 
                $score = $data['score'] ?? 0;
                $color = $data['color'] ?? '#2980b9';
                $dasharray = $score . ", 100"; 
                
                // Fuzzy match the description to the skill name
                $aptDesc = 'Evaluates fundamental cognitive capacity and practical execution related to this specific skill domain.';
                $lowerName = strtolower($name);
                foreach($aptitudeDescriptions as $keyword => $text) {
                    if (strpos($lowerName, $keyword) !== false) {
                        $aptDesc = $text;
                        break;
                    }
                }
            ?>
        <tr>
            <td width="50%" style="padding: 10px;">
                <div class="editorial-box no-break" style="padding: 15px; margin-bottom: 0; min-height: 105px;align-items: center;">
                    <table class="layout-table" style="margin: 0; border: none;">
                        <tr>
                            <td width="85px" style="vertical-align: middle;">
                                <svg width="70" height="70" viewBox="0 0 36 36">
                                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#ecf0f1" stroke-width="5" />
                                    <path stroke-dasharray="<?= $dasharray ?>" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="<?= $color ?>" stroke-width="5" style="-webkit-print-color-adjust: exact; print-color-adjust: exact;" />
                                    <text x="18" y="21.5" text-anchor="middle" font-weight="bold" font-size="8" fill="#2c3e50"><?= $score ?>%</text>
                                </svg>
                            </td>
                        
                            <td style="vertical-align: middle; padding-left: 5px;">
                                <div style="color: <?= $color ?>; font-weight: bold; font-size: 12pt; text-transform: uppercase; margin-bottom: 3px; letter-spacing: 0.5px;">
                                    <?= esc($name) ?>
                                </div>
                                <div style="font-size: 10pt; color: #555; line-height: 1.4; margin-bottom: 6px;">
                                    <?= esc($aptDesc) ?>
                                </div>
                                <div style="font-size: 10pt; color: #7f8c8d; margin: 0; font-weight: 500; background: #f4f6f9; display: inline-block; padding: 2px 6px; border-radius: 4px;">
                                    Diagnostic Level: <strong style="color: <?= $color ?>;"><?= esc($data['band'] ?? 'N/A') ?></strong>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
            </tr>
            <?php endforeach; ?>
        
        <?php endforeach; endif; ?>
    </table>
    
    <div class="footer">
        <span style="float: left; font-weight: 500;">Pharos Education Consultancy • Confidential Dossier</span>
        <span style="float: right; font-weight: bold; color: #34495e;">Page <?= $pageCounter++ ?></span>
        <div style="clear: both;"></div>
    </div>
</div>

<div class="page">
    <div class="header-strip">YOUR CAREER CLUSTERS (O*NET MAPPED)</div>
    <p style="margin-bottom: 30px;">Career Clusters group similar occupations and industries that require analogous skill sets. The following hierarchy ranks the broader industries you are most suited for mathematically.</p>
    
    <div class="editorial-box no-break" style="padding-top: 30px; padding-bottom: 30px;">
        <?php 
        $c_colors = ['#16a085', '#2980b9', '#e67e22', '#8e44ad', '#00acc1', '#c0392b'];
        $i = 0;
        if(!empty($clusters)):
            foreach($clusters as $cluster => $score): 
                $col = $c_colors[$i % count($c_colors)]; $i++;
        ?>
        <table class="bar-table">
            <tr>
                <td class="bar-label" style="width: 40%; font-size: 8.5pt;"><?= esc($cluster) ?></td>
                <td class="bar-track-td" style="width: 50%;">
                    <div class="bar-track">
                        <div class="bar-val" style="width: <?= esc($score) ?>%; background: <?= $col ?>;"></div>
                    </div>
                </td>
                <td class="bar-score" style="width: 10%; color: <?= $col ?>;"><?= esc($score) ?>%</td>
            </tr>
        </table>
        <?php endforeach; endif; ?>
    </div>
    <div class="footer">
        <span style="float: left; font-weight: 500;">Pharos Education Consultancy • Confidential Dossier</span>
        <span style="float: right; font-weight: bold; color: #34495e;">Page <?= $pageCounter++ ?></span>
        <div style="clear: both;"></div>
    </div>
</div>

<div class="page">
    <div class="header-strip">YOUR Top 4 Recommended CAREER CLUSTERS</div>
    <p style="margin-bottom: 20px;">Based on your psychometric algorithm, these are the top 4 macro-industries where your behavioral traits and cognitive aptitudes achieve peak correlation.</p>
    
    <?php 
    if(!empty($clusters)):
        $top4 = array_slice(array_keys($clusters), 0, 4);
        
        // --- SMART CLUSTER DICTIONARY ---
        // Maps O*NET and typical career cluster keywords to professional descriptions
        $clusterDescriptions = [
            'information technology' => 'Focuses on the design, development, support, and management of hardware, software, multimedia, and systems integration services. Your profile indicates a strong aptitude for logical structuring and technological innovation.',
            'health' => 'Involves planning, managing, and providing therapeutic services, diagnostic services, and health informatics. You possess the critical empathy and scientific rigor required for medical and wellness fields.',
            'business' => 'Encompasses planning, organizing, directing, and evaluating functions essential to efficient and productive operations. Your traits align well with leadership, resource allocation, and organizational strategy.',
            'finance' => 'Focuses on services for financial and investment planning, banking, insurance, and business financial management. You show a high correlation with analytical precision and economic forecasting.',
            'architecture' => 'Involves designing, planning, managing, building, and maintaining the built environment. Your spatial aptitude and practical mindset make you highly suited for structural design and execution.',
            'education' => 'Involves planning, managing, and providing education and training services, and related learning support services. Your profile highlights strong communicative and mentoring capabilities.',
            'marketing' => 'Focuses on anticipating, planning, managing, and satisfying consumers\' demand for products, services, and ideas. You exhibit the persuasive and creative traits necessary to drive consumer engagement.',
            'stem' => 'Involves planning, managing, and providing scientific research and professional and technical services including laboratory and testing services. You possess a highly analytical and investigative mindset.',
            'engineering' => 'Involves planning, managing, and providing scientific research and professional and technical services including laboratory and testing services. You possess a highly analytical and investigative mindset.',
            'law' => 'Focuses on planning, managing, and providing legal, public safety, protective services, and homeland security. Your profile aligns with regulatory compliance, justice, and crisis management.',
            'art' => 'Focuses on designing, producing, exhibiting, performing, writing, and publishing multimedia content. You demonstrate high expressive capabilities and out-of-the-box creative thinking.',
            'hospitality' => 'Involves the management, marketing, and operations of restaurants, lodging, attractions, recreation events, and travel related services. Your high interpersonal skills make you a natural fit for customer-centric environments.',
            'agriculture' => 'Focuses on the production, processing, marketing, distribution, financing, and development of agricultural commodities and resources. You show an affinity for natural systems and environmental stewardship.',
            'human services' => 'Involves preparing individuals for employment in career pathways that relate to families and human needs. Your empathy and social orientation make you ideal for counseling and community support.',
            'manufacturing' => 'Focuses on planning, managing, and performing the processing of materials into intermediate or final products. Your profile indicates a strong alignment with process optimization and quality control.',
            'transportation' => 'Involves planning, management, and movement of people, materials, and goods by road, pipeline, air, rail and water. You show an aptitude for logistical coordination and operational efficiency.'
        ];

        foreach($top4 as $index => $clusterName):
            
            // Default Fallback Text
            $clusterDesc = 'Professionals in this cluster are involved in the strategic planning, management, and execution of tasks relevant to this specific industry vertical. Your alignment here indicates a natural propensity for the daily workflows required in this field.';
            
            // Fuzzy search the dictionary for a matching keyword
            $lowerName = strtolower($clusterName);
            foreach($clusterDescriptions as $keyword => $text) {
                if (strpos($lowerName, $keyword) !== false) {
                    $clusterDesc = $text;
                    break;
                }
            }
    ?>
    <div class="editorial-box no-break" style="border-left: 5px solid <?= $c_colors[$index % count($c_colors)] ?>;">
        <h3 style="color: <?= $c_colors[$index % count($c_colors)] ?>; text-transform: uppercase; margin-bottom: 5px; font-size: 12pt;">
            <?= ($index + 1) . '. ' . esc($clusterName) ?>
        </h3>
        <p style="font-size: 9pt; margin-top: 0; line-height: 1.5; color: #34495e;">
            <?= esc($clusterDesc) ?>
        </p>
    </div>
    <?php endforeach; endif; ?>
    
    <div class="footer">
        <span style="float: left; font-weight: 500;">Pharos Education Consultancy • Confidential Dossier</span>
        <span style="float: right; font-weight: bold; color: #34495e;">Page <?= $pageCounter++ ?></span>
        <div style="clear: both;"></div>
    </div>
</div>
<div class="page">
    <div class="header-strip">YOUR ACADEMIC ROADMAP (STREAM SELECTION)</div>
    
    <p style="margin-bottom: 30px; line-height: 1.6; color: #444; font-size: 10.5pt;">
        Based on a rigorous cross-analysis of your <strong>RIASEC Career Interests</strong>, <strong>Cognitive Aptitude</strong>, and <strong>Multiple Intelligences</strong>, our engine has calculated your compatibility with the core academic streams. Choosing the right academic stream is the critical first step in executing your long-term career plan.
    </p>
    
    <div class="no-break" style="background: #fdfdfd; border: 1px solid #eaeaea; border-radius: 8px; padding: 40px 30px;">
        
        <h3 style="color: #2c3e50; font-size: 14pt; margin-top: 0; margin-bottom: 25px; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px;">
            Stream Compatibility Analysis
        </h3>

        <?php 
        if(!empty($academicRoadmap)):
            $rank = 1;
            foreach($academicRoadmap as $streamName => $data): 
                // Determine recommendation level
                $badgeText = '';
                $badgeColor = '';
                if ($rank === 1) {
                    $badgeText = 'Highly Recommended';
                    $badgeColor = '#27ae60';
                } elseif ($data['match_percentage'] >= 75) {
                    $badgeText = 'Good Fit';
                    $badgeColor = '#f39c12';
                } else {
                    $badgeText = 'Challenging Fit';
                    $badgeColor = '#e74c3c';
                }
        ?>
        <div style="margin-bottom: 25px; background: #ffffff; border: 1px solid #ecf0f1; border-left: 5px solid <?= $data['color'] ?>; border-radius: 6px; padding: 20px;">
            <table style="width: 100%; border-collapse: collapse; border: none;">
                <tr>
                    <td style="width: 75%; vertical-align: top;">
                        <div style="font-size: 12pt; font-weight: bold; color: <?= $data['color'] ?>; margin-bottom: 5px;">
                            <?= $rank ?>. <?= esc($streamName) ?>
                        </div>
                        <div style="font-size: 9.5pt; color: #7f8c8d; line-height: 1.5; padding-right: 15px;">
                            <?= esc($data['desc']) ?>
                        </div>
                    </td>
                    <td style="width: 25%; text-align: center; vertical-align: middle; border-left: 1px dashed #bdc3c7;">
                        <div style="font-size: 22pt; font-weight: bold; color: <?= $data['color'] ?>; line-height: 1;">
                            <?= esc($data['match_percentage']) ?>%
                        </div>
                        <div style="font-size: 8pt; font-weight: bold; color: <?= $badgeColor ?>; margin-top: 5px; text-transform: uppercase;">
                            <?= $badgeText ?>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <?php 
                $rank++;
            endforeach; 
        else: 
        ?>
            <p style="text-align: center; color: #7f8c8d; font-style: italic;">Academic roadmap generation is pending further assessment data.</p>
        <?php endif; ?>
        
    </div>
    
    <div style="margin-top: 30px; background: #ebf5fb; border-radius: 8px; padding: 20px;">
        <h4 style="color: #2980b9; margin-top: 0; margin-bottom: 10px; font-size: 11pt;"><i class="fas fa-lightbulb"></i> Counsellor's Note</h4>
        <p style="margin: 0; font-size: 9.5pt; color: #34495e; line-height: 1.5;">
            While your top recommendation represents the path of least resistance mathematically, you should also consider your <strong>Career Motivators</strong>. If a "Good Fit" stream aligns better with your primary life values (e.g., Independence or Creativity), it may ultimately lead to higher long-term satisfaction.
        </p>
    </div>
<div class="footer">
        <span style="float: left; font-weight: 500;">Pharos Education Consultancy • Confidential Dossier</span>
        <span style="float: right; font-weight: bold; color: #34495e;">Page <?= $pageCounter++ ?></span>
        <div style="clear: both;"></div>
    </div>
</div>
<div class="page">
    <div class="header-strip">ACADEMIC & VOCATIONAL SUBJECT MAPPING</div>
    <p>To successfully navigate toward your top career clusters and meet international university admission standards, excelling in specific academic subjects is highly recommended. Below is your 3-Tier subject blueprint.</p>

    <?php 
    if(isset($advData['subject_recommendations']) && !empty($advData['subject_recommendations'])):
        $colors = ['#2980b9', '#27ae60']; // Blue for Track 1, Green for Track 2
        $i = 0;
        
        foreach($advData['subject_recommendations'] as $cluster => $subjects):
            $accent = $colors[$i % count($colors)]; 
            $trackNum = $i + 1;
            $i++;
    ?>
    <div class="editorial-box no-break" style="margin-top: 20px; padding: 25px; border-top: 4px solid <?= $accent ?>; background: #fdfdfd; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
        <h3 style="color: <?= $accent ?>; margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 5px; font-size: 13pt;">
            Track <?= $trackNum ?>: <?= esc($cluster) ?> Focus
        </h3>
        
        <p style="font-size: 9pt; font-weight: bold; color: #2c3e50; margin-top: 15px; text-transform: uppercase;">1. Core Academics (Mandatory)</p>
        <p style="font-size: 8pt; color: #7f8c8d; margin-top: -5px; margin-bottom: 8px;">Foundational subjects required for university admission in this field.</p>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px; border: none;">
            <?php foreach($subjects['core'] as $sub => $val): ?>
            <tr>
                <td style="width: 35%; padding: 5px 0; font-size: 9.5pt; color: #333; font-weight: bold;"><?= esc($sub) ?></td>
                <td style="width: 50%; padding: 5px 15px; vertical-align: middle;">
                    <div style="width: 100%; height: 12px; background: #ecf0f1; border-radius: 6px; position: relative; overflow: hidden; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                        <div style="width: <?= $val ?>%; height: 100%; background: <?= $accent ?>; border-radius: 6px; -webkit-print-color-adjust: exact; print-color-adjust: exact;"></div>
                    </div>
                </td>
                <td style="width: 15%; padding: 5px 0; font-size: 8.5pt; text-align: right; font-weight: bold; color: <?= $accent ?>;">Critical</td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <p style="font-size: 9pt; font-weight: bold; color: #2c3e50; margin-top: 20px; text-transform: uppercase;">2. Recommended Electives</p>
        <p style="font-size: 8pt; color: #7f8c8d; margin-top: -5px; margin-bottom: 8px;">Subjects that strengthen your academic profile and application.</p>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px; border: none;">
            <?php foreach($subjects['elective'] as $sub => $val): ?>
            <tr>
                <td style="width: 35%; padding: 5px 0; font-size: 9.5pt; color: #555;"><?= esc($sub) ?></td>
                <td style="width: 50%; padding: 5px 15px; vertical-align: middle;">
                    <div style="width: 100%; height: 12px; background: #ecf0f1; border-radius: 6px; position: relative; overflow: hidden; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                        <div style="width: <?= $val ?>%; height: 100%; background: #34495e; border-radius: 6px; -webkit-print-color-adjust: exact; print-color-adjust: exact;"></div>
                    </div>
                </td>
                <td style="width: 15%; padding: 5px 0; font-size: 8.5pt; text-align: right; font-weight: bold; color: #34495e;">Recommended</td>
            </tr>
            <?php endforeach; ?>
        </table>

        <p style="font-size: 9pt; font-weight: bold; color: #e67e22; margin-top: 20px; text-transform: uppercase;">3. Applied Skills & Vocational Tracks</p>
        <p style="font-size: 8pt; color: #7f8c8d; margin-top: -5px; margin-bottom: 8px;">Real-world skills that demonstrate early aptitude to admissions officers.</p>
        <table style="width: 100%; border-collapse: collapse; border: none;">
            <?php foreach($subjects['skill'] as $sub => $val): ?>
            <tr>
                <td style="width: 35%; padding: 5px 0; font-size: 9.5pt; color: #e67e22; font-weight: bold;"><?= esc($sub) ?></td>
                <td style="width: 50%; padding: 5px 15px; vertical-align: middle;">
                    <div style="width: 100%; height: 12px; background: #ecf0f1; border-radius: 6px; position: relative; overflow: hidden; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                        <div style="width: <?= $val ?>%; height: 100%; background: #e67e22; border-radius: 6px; -webkit-print-color-adjust: exact; print-color-adjust: exact;"></div>
                    </div>
                </td>
                <td style="width: 15%; padding: 5px 0; font-size: 8.5pt; text-align: right; font-weight: bold; color: #e67e22;">High Value</td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php 
        endforeach;
    else:
        echo "<p style='margin-top:30px; font-style: italic;'>Subject mapping is currently being processed for your unique profile.</p>";
    endif; 
    ?>
    
    <div class="footer">
        <span style="float: left; font-weight: 500;">Pharos Education Consultancy • Confidential Dossier</span>
        <span style="float: right; font-weight: bold; color: #34495e;">Page <?= $pageCounter++ ?></span>
        <div style="clear: both;"></div>
    </div>
</div>
    
    <div class="page">
    <div class="header-strip">MULTI-DIMENSIONAL FIT ANALYSIS</div>
    <p style="margin-bottom: 30px; line-height: 1.6; color: #444;">
        This radar chart plots your unique RIASEC psychometric signature (Blue) against the "Ideal Benchmark" profile (Green) required by your top recommended career path. A tighter overlap indicates a stronger natural propensity for that specific professional environment.
    </p>

    <?php 
    // Dynamically calculate the "Ideal Profile" based on the student's top 2 traits
    // This simulates the perfect candidate for their #1 recommended industry
    $idealScores = [];
    if(!empty($riasec)) {
        $sortedKeys = array_keys($riasec);
        $idealScores[strtolower($sortedKeys[0] ?? 'realistic')] = 95;  // Top trait requirement
        $idealScores[strtolower($sortedKeys[1] ?? 'investigative')] = 80; // Secondary requirement
        $idealScores[strtolower($sortedKeys[2] ?? 'artistic')] = 60;
        $idealScores[strtolower($sortedKeys[3] ?? 'social')] = 35;
        $idealScores[strtolower($sortedKeys[4] ?? 'enterprising')] = 25;
        $idealScores[strtolower($sortedKeys[5] ?? 'conventional')] = 20;
    }
    
    $topPathName = isset($advData['career_paths'][0]['title']) ? explode(' - ', $advData['career_paths'][0]['title'])[0] : 'Your Top Career';
    ?>

    <div class="no-break" style="background: #fdfdfd; border: 1px solid #eaeaea; border-radius: 12px; padding: 40px; display: flex; align-items: center;">
        
        <div style="width: 55%; text-align: center;">
            <?= renderRiasecRadarSVG($riasec, $idealScores) ?>
        </div>
        
        <div style="width: 45%; padding-left: 30px; border-left: 1px solid #ecf0f1;">
            
            <h3 style="color: #2c3e50; font-size: 13pt; margin-top: 0; margin-bottom: 20px;">Profile Alignment</h3>
            
            <div style="display: flex; align-items: flex-start; margin-bottom: 15px;">
                <div style="width: 20px; height: 20px; background: rgba(41, 128, 185, 0.6); border: 2px solid #2980b9; margin-right: 15px; border-radius: 4px; flex-shrink: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact;"></div>
                <div>
                    <div style="font-weight: bold; color: #2980b9; font-size: 11pt;">Your Profile</div>
                    <div style="font-size: 9pt; color: #7f8c8d; line-height: 1.4;">Your actual assessed cognitive and interest footprint.</div>
                </div>
            </div>

            <div style="display: flex; align-items: flex-start; margin-bottom: 25px;">
                <div style="width: 20px; height: 20px; background: rgba(39, 174, 96, 0.15); border: 2px dashed #27ae60; margin-right: 15px; border-radius: 4px; flex-shrink: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact;"></div>
                <div>
                    <div style="font-weight: bold; color: #27ae60; font-size: 11pt;">Benchmark: <?= esc($topPathName) ?></div>
                    <div style="font-size: 9pt; color: #7f8c8d; line-height: 1.4;">The ideal psychological baseline required to thrive in this role.</div>
                </div>
            </div>

            <div style="background: #ebf5fb; padding: 15px; border-radius: 6px; border-left: 4px solid #2980b9;">
                <div style="font-size: 9.5pt; color: #2c3e50; font-weight: bold; margin-bottom: 5px;">Diagnostic Conclusion</div>
                <div style="font-size: 8.5pt; color: #34495e; line-height: 1.5;">
                    The geometry of your profile strongly encompasses the benchmark boundaries. Areas where the blue shape exceeds the green dashed line indicate secondary talents you can leverage for a competitive advantage in this field.
                </div>
            </div>
            
        </div>
    </div>

    <div class="footer">
        <span style="float: left; font-weight: 500;">Pharos Education Consultancy • Confidential Dossier</span>
        <span style="float: right; font-weight: bold; color: #34495e;">Page <?= $pageCounter++ ?></span>
        <div style="clear: both;"></div>
    </div>
</div>

<div class="page">
    <div class="header-strip">YOUR CAREER PATHS (MICRO-MAPPED)</div>
    <p>This matrix isolates specific job titles and evaluates your exact psychological and cognitive correlation to those roles. A "Good Choice" indicates high alignment across both your personality profile and cognitive aptitude baseline.</p>
    
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="40%">Specific Career Path</th>
                <th width="20%">Psych. Fit</th>
                <th width="20%">Aptitude Fit</th>
                <th width="15%">Verdict</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            // 1. DYNAMIC COLOR HELPER
            // This assigns psychological colors to the text and bars based on the band.
            if (!function_exists('getBandColor')) {
                function getBandColor($band) {
                    $b = strtolower(trim($band));
                    if (in_array($b, ['very high', 'high', 'good'])) return '#27ae60'; // Green
                    if (in_array($b, ['average', 'fair'])) return '#f39c12';           // Orange
                    if (in_array($b, ['low', 'improve'])) return '#e74c3c';            // Red
                    return '#7f8c8d'; // Default Gray
                }
            }
            
            if(isset($advData['career_paths']) && !empty($advData['career_paths'])):
                $rank = 1;
                foreach($advData['career_paths'] as $path): 
                    // Extract data and determine colors
                    $psyScore = $path['psy']['score'] ?? 0;
                    $psyBand  = $path['psy']['band'] ?? 'N/A';
                    $psyColor = getBandColor($psyBand);

                    $aptScore = $path['skill']['score'] ?? 0;
                    $aptBand  = $path['skill']['band'] ?? 'N/A';
                    $aptColor = getBandColor($aptBand);
            ?>
            <tr>
                <td style="text-align: center; font-weight: bold; color: #34495e;"><?= $rank++ ?></td>
                
                <td>
                    <div style="font-weight: bold; color: #2980b9; font-size: 10pt; margin-bottom: 3px;"><?= esc($path['title']) ?></div>
                    <div style="font-size: 8pt; color: #7f8c8d; line-height: 1.3;">
                                <?php
                                $roleDesc = esc($path['roles']);
                                echo (strlen($roleDesc) > 150) ? substr($roleDesc, 0, 150) . '...' : $roleDesc;
                                ?>
                    </div>
                </td>
                
                <td style="vertical-align: middle;">
                    <div style="font-weight: bold; font-size: 9pt; color: <?= $psyColor ?>; margin-bottom: 4px;">
                        <?= esc($psyBand) ?>
                    </div>
                    <div style="width: 80%; height: 6px; background: #ecf0f1; border-radius: 3px; position: relative; overflow: hidden; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                        <div style="width: <?= $psyScore ?>%; height: 100%; background: <?= $psyColor ?>; border-radius: 3px; -webkit-print-color-adjust: exact; print-color-adjust: exact;"></div>
                    </div>
                    <div style="font-size: 7.5pt; color: #95a5a6; margin-top: 3px; font-weight: bold;">
                        <?= $psyScore ?>% Match
                    </div>
                </td>
                
                <td style="vertical-align: middle;">
                    <div style="font-weight: bold; font-size: 9pt; color: <?= $aptColor ?>; margin-bottom: 4px;">
                        <?= esc($aptBand) ?>
                    </div>
                    <div style="width: 80%; height: 6px; background: #ecf0f1; border-radius: 3px; position: relative; overflow: hidden; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                        <div style="width: <?= $aptScore ?>%; height: 100%; background: <?= $aptColor ?>; border-radius: 3px; -webkit-print-color-adjust: exact; print-color-adjust: exact;"></div>
                    </div>
                    <div style="font-size: 7.5pt; color: #95a5a6; margin-top: 3px; font-weight: bold;">
                        <?= $aptScore ?>% Match
                    </div>
                </td>
                
                <td style="text-align: center; vertical-align: middle; font-weight: bold; color: <?= getBandColor(($path['comment'] == 'Good Choice') ? 'Good' : 'Improve') ?>;">
                    <?= esc($path['comment']) ?>
                </td>
            </tr>
            <?php 
                endforeach; 
            else: 
            ?>
            <tr>
                <td colspan="5" style="text-align: center; padding: 20px;">No career paths generated yet. Please ensure psychometric tests are completed.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <div class="footer">
        <span style="float: left; font-weight: 500;">Pharos Education Consultancy • Confidential Dossier</span>
        <span style="float: right; font-weight: bold; color: #34495e;">Page <?= $pageCounter++ ?></span>
        <div style="clear: both;"></div>
    </div>
</div>

    <div class="page">
    <div class="header-strip">EXECUTION ROADMAPS (TOP 3 CAREERS)</div>
    <p style="margin-bottom: 30px; line-height: 1.6; color: #444; font-size: 10.5pt;">
        Identifying your ideal career is only the first step. Below are the definitive execution roadmaps for your top three recommended professions. These outlines provide the exact academic pathway required to transition from high school to professional entry.
    </p>

    <?php 
    if(isset($advData['career_paths']) && count($advData['career_paths']) > 0):
        // Extract the absolute top 3 careers
        $top3Roadmaps = array_slice($advData['career_paths'], 0, 3);
        $roadmapColors = ['#2980b9', '#e67e22', '#27ae60']; // Blue, Orange, Green

        foreach($top3Roadmaps as $idx => $path): 
            $color = $roadmapColors[$idx];
            $roadmap = $path['roadmap'] ?? ['stream'=>'Pending', 'degrees'=>['Pending'], 'exams'=>['Pending']];
    ?>
    
    <div class="no-break" style="border: 1px solid <?= $color ?>; border-radius: 8px; margin-bottom: 35px; background: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        
        <div style="background: <?= $color ?>; color: #fff; padding: 12px 20px; font-weight: bold; font-size: 11.5pt; border-radius: 7px 7px 0 0; display: flex; justify-content: space-between;">
            <span>Rank <?= $idx + 1 ?>: <?= esc(explode(' - ', $path['title'])[0]) ?></span>
            <span>Match: <?= round($path['sort_metric']) ?>%</span>
        </div>
        
        <table width="100%" style="border-collapse: collapse; background: #fdfdfd;">
            <tr>
                <td width="33%" style="padding: 20px; border-right: 1px dashed #bdc3c7; vertical-align: top;">
                    <div style="font-size: 8.5pt; color: #7f8c8d; text-transform: uppercase; font-weight: bold; margin-bottom: 8px; letter-spacing: 0.5px;">
                        <span style="background: <?= $color ?>; color: white; padding: 2px 6px; border-radius: 4px; margin-right: 5px;">1</span> High School (11/12)
                    </div>
                    <div style="font-size: 11pt; color: #2c3e50; font-weight: bold; line-height: 1.4;">
                        <?= esc($roadmap['stream']) ?>
                    </div>
                </td>
                
                <td width="33%" style="padding: 20px; border-right: 1px dashed #bdc3c7; vertical-align: top;">
                    <div style="font-size: 8.5pt; color: #7f8c8d; text-transform: uppercase; font-weight: bold; margin-bottom: 8px; letter-spacing: 0.5px;">
                        <span style="background: <?= $color ?>; color: white; padding: 2px 6px; border-radius: 4px; margin-right: 5px;">2</span> Undergrad Degree
                    </div>
                    <div style="font-size: 10.5pt; color: #2c3e50; font-weight: bold; line-height: 1.4;">
                        <?= esc(implode(', ', $roadmap['degrees'])) ?>
                    </div>
                </td>
                
                <td width="34%" style="padding: 20px; vertical-align: top; background: rgba(41, 128, 185, 0.03);">
                    <div style="font-size: 8.5pt; color: #7f8c8d; text-transform: uppercase; font-weight: bold; margin-bottom: 8px; letter-spacing: 0.5px;">
                        <span style="background: <?= $color ?>; color: white; padding: 2px 6px; border-radius: 4px; margin-right: 5px;">3</span> Target Entrance Exams
                    </div>
                    <div style="font-size: 10.5pt; color: #2c3e50; font-weight: bold; line-height: 1.4;">
                        <?= esc(implode(' • ', $roadmap['exams'])) ?>
                    </div>
                </td>
            </tr>
        </table>
        
    </div>

    <?php 
        endforeach; 
    else: 
    ?>
        <p style="text-align: center; color: #7f8c8d; font-style: italic;">No roadmap data available to display.</p>
    <?php endif; ?>
    
    <div style="margin-top: 20px; background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; font-size: 9.5pt; color: #856404;">
        <strong>Disclaimer:</strong> Entrance exams and degree requirements are subject to change based on university regulations and national education policies (such as NEP 2020 / NCF). Always verify exact prerequisites with your target institution.
    </div>

    <div class="footer">
        <span style="float: left; font-weight: 500;">Pharos Education Consultancy • Confidential Dossier</span>
        <span style="float: right; font-weight: bold; color: #34495e;">Page <?= $pageCounter++ ?></span>
        <div style="clear: both;"></div>
    </div>
</div>    

<div class="page">
    <div class="header-strip" style="font-size: 9pt;">OUR CAREER ASSESSMENT IS BASED ON THE CONCEPT OF CORRELATION THEORY AND VARIOUS PSYCHOMETRIC AND STATISTICAL MODELS.</div>
    <p style="margin-bottom: 20px;">The data presented in this dossier is not absolute but probabilistic. It is calculated using proprietary algorithms mapped to international occupational frameworks (such as O*NET and ISCO-08).</p>
    
    <table class="data-table" style="background: #fcfcfc;">
        <tr>
            <th width="30%" style="background: #2c3e50; color: white;">Metric Engine</th>
            <th width="70%" style="background: #2c3e50; color: white;">Algorithmic Mapping Logic Applied</th>
        </tr>
        <tr>
            <td style="font-weight: bold; color: #2980b9;">Career Personality</td>
            <td style="font-family: monospace; color: #555;">
                Engine Result: <strong><?= esc($mbtiPrimaryTrait) ?></strong><br>
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold; color: #2980b9;">Career Interest</td>
            <td style="color: #555;">
                <?php 
                if(!empty($riasec)) {
                    $topRiasecKeys = array_keys($riasec);
                    $t1 = $topRiasecKeys[0] ?? 'N/A'; $s1 = $riasec[$t1] ?? 0;
                    $t2 = $topRiasecKeys[1] ?? 'N/A'; $s2 = $riasec[$t2] ?? 0;
                    $t3 = $topRiasecKeys[2] ?? 'N/A'; $s3 = $riasec[$t3] ?? 0;
                    echo ucfirst($t1) . " (" . $s1 . "%) + " . ucfirst($t2) . " (" . $s2 . "%) + ". ucfirst($t3) . " (" . $s3 . "%)";
                } else { echo "N/A"; }
                ?>
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold; color: #2980b9;">Career Motivator</td>
            <td style="color: #555;">Primary Node Identified: <?= esc(array_keys($motivators)[0] ?? 'N/A') ?></td>
        </tr>
        <tr>
            <td style="font-weight: bold; color: #2980b9;">Learning Style</td>
            <td style="color: #555;">Primary Modality: <?= esc(array_keys($learning)[0] ?? 'N/A') ?></td>
        </tr>
        <tr>
            <td style="font-weight: bold; color: #2980b9;">Skills & Abilities</td>
            <td style="font-size: 8pt; color: #555; line-height: 1.6;">
                Numerical Ability [<?= esc($skills['Numerical Ability']['score'] ?? 0) ?>%] + Logical Ability [<?= esc($skills['Logical Ability']['score'] ?? 0) ?>%] + Verbal Ability [<?= esc($skills['Verbal Ability']['score'] ?? 0) ?>%] + Administrative Skills [<?= esc($skills['Administrative Skills']['score'] ?? 0) ?>%] + Spatial Ability [<?= esc($skills['Spatial Ability']['score'] ?? 0) ?>%] + Leadership Skills [<?= esc($skills['Leadership Skills']['score'] ?? 0) ?>%] + Social Skills [<?= esc($skills['Social Skills']['score'] ?? 0) ?>%] + Mechanical Abilities [<?= esc($skills['Mechanical Abilities']['score'] ?? 0) ?>%]
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold; color: #2980b9;">Selected Clusters</td>
            <td style="color: #555;">
                <?php 
                if(!empty($clusters)) {
                    $top = array_slice(array_keys($clusters), 0, 4);
                    echo implode(" + ", $top);
                } else {
                    echo "N/A";
                }
                ?>
            </td>
        </tr>
    </table>
    
    <div style="margin-top: 50px; text-align: center; border-top: 1px solid #ccc; padding-top: 20px;">
        <h4 style="color: #7f8c8d; margin-bottom: 5px;">*** END OF REPORT ***</h4>
        <p style="font-size: 8pt; color: #999;">Confidential Document. Generated securely via Pharos Education Consultancy.</p>
    </div>
    <div class="footer">
        <span style="float: left; font-weight: 500;">Pharos Education Consultancy • Confidential Dossier</span>
        <span style="float: right; font-weight: bold; color: #34495e;">Page <?= $pageCounter++ ?></span>
        <div style="clear: both;"></div>
    </div>
</div>

</body>
</html>