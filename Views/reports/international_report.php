<?php
// Load Logic
$engine = new \App\Libraries\PharosInternationalEngine();
// Assuming $riasec and $aptitude come from Controller
$reportData = $engine->generateReportData(
    $riasec['scores']['breakdown'] ?? [], 
    $aptitude['scores']['breakdown'] ?? []
);

$paths = $reportData['paths'];
$subjects = $reportData['subjects'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>International Career Dossier</title>
    <style>
        /* CORE SETTINGS */
        @page { margin: 0; }
        body { font-family: 'Arial', sans-serif; background: #e0e0e0; margin: 0; padding: 0; color: #333; -webkit-print-color-adjust: exact; }
        .page { width: 210mm; min-height: 297mm; background: white; margin: 10mm auto; padding: 20mm; position: relative; page-break-after: always; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        
        /* HEADERS */
        h2 { font-size: 18pt; text-transform: uppercase; color: #2c3e50; border-bottom: 2px solid #2c3e50; padding-bottom: 10px; margin-bottom: 20px; }
        h3 { font-size: 14pt; color: #e67e22; margin-bottom: 15px; }

        /* TABLE STYLING (Matching Page 21) */
        .career-table { width: 100%; border-collapse: collapse; font-size: 10pt; }
        .career-table th { background-color: #f4f4f4; border: 1px solid #ddd; padding: 12px 8px; text-align: left; font-weight: bold; }
        .career-table td { border: 1px solid #ddd; padding: 10px 8px; vertical-align: top; }
        
        /* SCORE TEXT STYLES */
        .score-psy { color: #27ae60; font-weight: bold; font-size: 11pt; } /* Green */
        .score-skill { color: #2980b9; font-weight: bold; font-size: 11pt; } /* Blue */
        .score-low { color: #c0392b; font-weight: bold; } /* Red */
        
        .sub-text { display: block; font-size: 14pt; font-weight: bold; margin-bottom: 5px; } /* The Number (98) */
        
        /* COMMENT PILLS */
        .pill { display: inline-block; padding: 4px 10px; border-radius: 4px; font-size: 9pt; font-weight: bold; }
        .pill-good { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .pill-develop { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }

        /* SUBJECT BARS (Matching Page 19) */
        .subject-container { margin-bottom: 12px; }
        .subject-label { display: flex; justify-content: space-between; font-size: 9pt; font-weight: bold; margin-bottom: 3px; }
        .bar-bg { width: 100%; background: #eee; height: 18px; border-radius: 3px; overflow: hidden; }
        .bar-fg { height: 100%; text-align: right; color: white; font-size: 8pt; line-height: 18px; padding-right: 5px; }
        .bg-mandatory { background: #003366; } /* Dark Blue */
        .bg-optional { background: #0066cc; }  /* Lighter Blue */

    </style>
</head>
<body>

    <div class="page">
        <h2>Recommended Subjects</h2>
        <p>Based on your aptitude and interest profile, these subjects are most relevant for your top career path.</p>

        <div style="display: flex; gap: 40px; margin-top: 30px;">
            
            <div style="width: 50%;">
                <h3 style="color: #d35400;">Mandatory Subjects</h3>
                <?php foreach($subjects['Mandatory'] as $sub => $score): ?>
                <div class="subject-container">
                    <div class="subject-label">
                        <span><?= $sub ?></span>
                        <span><?= $score ?></span>
                    </div>
                    <div class="bar-bg">
                        <div class="bar-fg bg-mandatory" style="width: <?= $score ?>%;"><?= $score ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div style="width: 50%;">
                <h3 style="color: #27ae60;">Optional Subjects</h3>
                <?php foreach($subjects['Optional'] as $sub => $score): ?>
                <div class="subject-container">
                    <div class="subject-label">
                        <span><?= $sub ?></span>
                        <span><?= $score ?></span>
                    </div>
                    <div class="bar-bg">
                        <div class="bar-fg bg-optional" style="width: <?= $score ?>%;"><?= $score ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

        </div>
    </div>

    <div class="page">
        <h2>Your Career Paths</h2>
        <p>Detailed analysis comparing your Psychometric Interest vs. Actual Skill Capability.</p>

        <table class="career-table">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="35%">Career Path / Cluster</th>
                    <th width="20%">Psy. Analysis<br><small>(Interest Level)</small></th>
                    <th width="20%">Skill Ability<br><small>(Aptitude Level)</small></th>
                    <th width="20%">Comment</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $i = 1;
                foreach($paths as $p): 
                    if($i > 10) break; // Limit to fit page
                    // Determine styling based on score logic
                    $skillClass = $p['skill_val'] < 40 ? 'score-low' : 'score-skill';
                    $commentClass = $p['comment'] == 'Develop' ? 'pill-develop' : 'pill-good';
                ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td>
                        <strong style="font-size: 11pt;"><?= $p['role'] ?></strong><br>
                        <span style="color: #777; font-size: 9pt;"><?= $p['cluster'] ?></span>
                    </td>
                    
                    <td>
                        <span class="score-psy"><?= $p['psy_txt'] ?>: <span class="sub-text"><?= $p['psy_val'] ?></span></span>
                    </td>

                    <td>
                        <span class="<?= $skillClass ?>"><?= $p['skill_txt'] ?>: <span class="sub-text"><?= $p['skill_val'] ?></span></span>
                    </td>

                    <td style="vertical-align: middle;">
                        <span class="pill <?= $commentClass ?>"><?= $p['comment'] ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>
</html>