<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Pharos Official Report</title>
    <style>
        /* GLOBAL RESET & TYPOGRAPHY */
        body { font-family: 'Arial', sans-serif; color: #333; margin: 0; padding: 0; background: #555; }
        .page {
            width: 210mm; min-height: 297mm; padding: 10mm; margin: 10mm auto; background: white;
            position: relative; box-shadow: 0 0 10px rgba(0,0,0,0.5); page-break-after: always; overflow: hidden;
        }
        @media print {
            body { background: white; }
            .page { width: 100%; margin: 0; padding: 0; box-shadow: none; height: auto; page-break-after: always; }
        }

        /* BRANDING COLORS */
        .c-blue { color: #2980b9; } .bg-blue { background: #2980b9; }
        .c-green { color: #27ae60; } .bg-green { background: #27ae60; }
        .c-orange { color: #e67e22; } .bg-orange { background: #e67e22; }
        .c-teal { color: #16a085; } .bg-teal { background: #16a085; }
        .c-purple { color: #8e44ad; } .bg-purple { background: #8e44ad; }
        .c-red { color: #c0392b; } .bg-red { background: #c0392b; }
        
        /* HEADERS */
        .header-strip { 
            background: #2c3e50; color: white; padding: 8px 0; text-align: center; font-size: 10pt; font-weight: bold; text-transform: uppercase;
            margin: -10mm -10mm 5mm -10mm;
        }
        .section-title { font-size: 14pt; color: #444; text-transform: uppercase; border-bottom: 2px solid #ccc; padding-bottom: 5px; margin-bottom: 15px; margin-top: 10px; }
        
        /* COMPONENT: MBTI SLIDER (CSS ONLY) */
        .mbti-container { margin-bottom: 15px; }
        .mbti-labels { display: flex; justify-content: space-between; font-size: 9pt; font-weight: bold; margin-bottom: 2px; }
        .mbti-track { width: 100%; height: 16px; background: #eee; border-radius: 8px; position: relative; overflow: hidden; }
        .mbti-bar { height: 100%; position: absolute; top: 0; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 8pt; font-weight: bold; }
        
        /* COMPONENT: RIASEC HORIZONTAL BARS */
        .riasec-row { display: table; width: 100%; margin-bottom: 8px; font-size: 9pt; }
        .riasec-label { display: table-cell; width: 25%; vertical-align: middle; font-weight: bold; }
        .riasec-bar-area { display: table-cell; width: 65%; vertical-align: middle; }
        .riasec-val { display: table-cell; width: 10%; text-align: right; vertical-align: middle; font-weight: bold; }
        .bar-bg { width: 100%; background: #f0f0f0; height: 18px; border-radius: 4px; overflow: hidden; }
        .bar-fill { height: 100%; }

        /* COMPONENT: SKILL DOUGHNUT (CSS BORDERS) */
        /* Note: Pure CSS doughnuts are hard in PDF, using Border Radius trick */
        .doughnut-box { width: 100px; height: 100px; margin: 0 auto; position: relative; }
        .pie {
            width: 100%; height: 100%; border-radius: 50%; background: #eee;
            background-image: conic-gradient(var(--color) var(--deg), #eee 0);
        }
        .pie-hole {
            width: 70%; height: 70%; background: white; border-radius: 50%;
            position: absolute; top: 15%; left: 15%;
            display: flex; align-items: center; justify-content: center;
            font-size: 14pt; font-weight: bold; color: #333;
        }

        /* TABLES */
        table.clean { width: 100%; border-collapse: collapse; font-size: 9pt; }
        table.clean th { background: #eee; text-align: left; padding: 8px; border-bottom: 2px solid #ddd; }
        table.clean td { border-bottom: 1px solid #f0f0f0; padding: 8px; vertical-align: top; }
        
        /* FOOTER */
        .footer { position: absolute; bottom: 10mm; left: 0; width: 100%; text-align: center; font-size: 9pt; color: #999; border-top: 1px solid #eee; padding-top: 5px; }

        /* COVER PAGE SPECIFIC */
        .shield-logo { width: 120px; height: 140px; margin: 0 auto; background: url('https://via.placeholder.com/120x140?text=SHIELD') no-repeat center; background-size: contain; }
    </style>
</head>
<body>

    <div class="page" style="text-align: center;">
        <div style="margin-top: 50mm;">
            <img src="https://via.placeholder.com/150x180.png?text=WPS+Logo" style="width: 150px; margin-bottom: 20px;">
            
            <h1 style="font-size: 24pt; margin-bottom: 5px; color: #c0392b;">ASSESSMENT REPORT</h1>
            <h3 style="color: #555; font-weight: normal; margin-top: 0;">Career Planning & Analysis</h3>
        </div>

        <div style="margin-top: 40mm; font-size: 12pt; line-height: 2;">
            <strong>Report Prepared For:</strong><br>
            <span style="font-size: 16pt; color: #2c3e50; font-weight: bold;"><?= $student_name ?></span><br>
            AGE: <?= $age_grade ?> | GENDER: Male<br>
            <?= date('d-M-Y') ?>
        </div>

        <div style="position: absolute; bottom: 30mm; width: 100%;">
            <p style="font-weight: bold; color: #2c3e50;">Powered By: Pharos Consultancy</p>
            <div style="font-size: 9pt; color: #7f8c8d;">WORK | SINCERITY | PROFICIENCY</div>
        </div>
    </div>

    <div class="page">
        <div class="header-strip">YOUR PERSONALITY TYPE</div>
        <div style="text-align: center; margin: 20px 0;">
            <span style="font-size: 30pt; font-weight: bold; color: #2980b9; letter-spacing: 5px;"><?= $mbti['trait'] ?></span>
        </div>

        <?php 
        $e = $mbti['scores']['breakdown']['E'] ?? 0;
        $i = $mbti['scores']['breakdown']['I'] ?? 0;
        $total = $e + $i;
        $pctE = ($total > 0) ? round(($e/$total)*100) : 50;
        ?>
        <div class="mbti-container">
            <div class="mbti-labels">
                <span class="<?= $pctE < 50 ? 'c-blue' : '' ?>">Introvert (I)</span>
                <span class="<?= $pctE >= 50 ? 'c-blue' : '' ?>">Extrovert (E)</span>
            </div>
            <div class="mbti-track">
                <div class="mbti-bar bg-teal" style="width: <?= 100-$pctE ?>%; left: 0; opacity: 0.3;"></div>
                <div class="mbti-bar bg-blue" style="width: <?= $pctE ?>%; right: 0;">
                    <?= $pctE ?>%
                </div>
            </div>
        </div>

        <?php 
        $s = $mbti['scores']['breakdown']['S'] ?? 0;
        $n = $mbti['scores']['breakdown']['N'] ?? 0;
        $total = $s + $n;
        $pctS = ($total > 0) ? round(($s/$total)*100) : 50;
        ?>
        <div class="mbti-container">
            <div class="mbti-labels">
                <span class="<?= $pctS >= 50 ? 'c-orange' : '' ?>">Sensing (S)</span>
                <span class="<?= $pctS < 50 ? 'c-orange' : '' ?>">Intuition (N)</span>
            </div>
            <div class="mbti-track">
                <div class="mbti-bar bg-orange" style="width: <?= $pctS ?>%; left: 0;">
                    <?= $pctS ?>%
                </div>
                <div class="mbti-bar bg-orange" style="width: <?= 100-$pctS ?>%; right: 0; opacity: 0.3;"></div>
            </div>
        </div>

        <?php 
        $t = $mbti['scores']['breakdown']['T'] ?? 0;
        $f = $mbti['scores']['breakdown']['F'] ?? 0;
        $total = $t + $f;
        $pctT = ($total > 0) ? round(($t/$total)*100) : 50;
        ?>
        <div class="mbti-container">
            <div class="mbti-labels">
                <span class="<?= $pctT >= 50 ? 'c-green' : '' ?>">Thinking (T)</span>
                <span class="<?= $pctT < 50 ? 'c-green' : '' ?>">Feeling (F)</span>
            </div>
            <div class="mbti-track">
                <div class="mbti-bar bg-green" style="width: <?= $pctT ?>%; left: 0;">
                    <?= $pctT ?>%
                </div>
                <div class="mbti-bar bg-green" style="width: <?= 100-$pctT ?>%; right: 0; opacity: 0.3;"></div>
            </div>
        </div>

        <div class="section-title">Analysis</div>
        <p style="font-size: 10pt;">
            <strong><?= $mbti['trait'] ?> Profile:</strong> 
            You prefer to focus your energy <?= $pctE > 50 ? 'outwardly (Extrovert)' : 'inwardly (Introvert)' ?>. 
            You process information via <?= $pctS > 50 ? 'facts (Sensing)' : 'ideas (Intuition)' ?>.
            You make decisions based on <?= $pctT > 50 ? 'logic (Thinking)' : 'values (Feeling)' ?>.
        </p>

        <div class="footer">Page 4 of 25</div>
    </div>

    <div class="page">
        <div class="header-strip">CAREER INTEREST TYPES (RIASEC)</div>
        
        <div style="margin-top: 20px;">
        <?php 
        $colors = ['Realistic'=>'#16a085', 'Investigative'=>'#2980b9', 'Artistic'=>'#e67e22', 'Conventional'=>'#8e44ad', 'Enterprising'=>'#00acc1', 'Social'=>'#95a5a6'];
        
        $r_scores = $riasec['scores']['breakdown'] ?? [];
        arsort($r_scores); // Sort highest first
        
        // Find max for scaling
        $max = max($r_scores); 

        foreach($r_scores as $cat => $val):
            $pct = ($max > 0) ? ($val/$max)*100 : 0;
            $col = $colors[$cat] ?? '#333';
        ?>
            <div class="riasec-row">
                <div class="riasec-label"><?= $cat ?></div>
                <div class="riasec-bar-area">
                    <div class="bar-bg">
                        <div class="bar-fill" style="width: <?= $pct ?>%; background: <?= $col ?>;"></div>
                    </div>
                </div>
                <div class="riasec-val"><?= $pct ?>%</div>
            </div>
        <?php endforeach; ?>
        </div>

        <div class="section-title" style="margin-top: 30px;">Interest Analysis</div>
        <div style="background: #e8f8f5; padding: 15px; border-left: 5px solid #16a085; margin-bottom: 10px;">
            <strong style="color: #16a085; font-size: 12pt;">R - Realistic (High)</strong><br>
            <small>You are active and stable. You prefer hands-on activities, working with tools, and practical problem solving.</small>
        </div>
        
        <div class="footer">Page 7 of 25</div>
    </div>

    <div class="page">
        <div class="header-strip">YOUR LEARNING STYLE</div>
        
        <table style="width: 100%; height: 200px; margin-top: 30px; border-bottom: 2px solid #ccc;">
            <tr style="vertical-align: bottom;">
                <?php 
                $l_styles = [
                    'Auditory' => 50, 'Read/Write' => 25, 'Visual' => 13, 'Kinesthetic' => 12
                ];
                foreach($l_styles as $name => $pct):
                ?>
                <td style="text-align: center; padding: 0 10px;">
                    <div style="font-weight: bold; margin-bottom: 5px;"><?= $pct ?>%</div>
                    <div style="background: #27ae60; width: 100%; height: <?= $pct * 2 ?>px; border-radius: 3px 3px 0 0;"></div>
                </td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td align="center"><small>Auditory</small></td>
                <td align="center"><small>Read/Write</small></td>
                <td align="center"><small>Visual</small></td>
                <td align="center"><small>Kinesthetic</small></td>
            </tr>
        </table>

        <div class="footer">Page 10 of 25</div>
    </div>

    <div class="page">
        <div class="header-strip">SKILLS AND ABILITIES</div>
        
        <table style="width: 100%; margin-top: 30px;">
            <tr>
                <td align="center" width="50%">
                    <div class="doughnut-box">
                        <div style="width: 80px; height: 80px; border-radius: 50%; border: 10px solid #eee; border-top: 10px solid #2980b9; border-right: 10px solid #2980b9; transform: rotate(45deg);"></div>
                        <div style="position: absolute; top: 35px; left: 0; width: 100%; text-align: center; font-weight: bold;">80%</div>
                    </div>
                    <h4 style="margin: 10px 0 0 0;">Numerical Ability</h4>
                    <p style="font-size: 9pt; color: #666;">High Proficiency</p>
                </td>
                
                <td align="center" width="50%">
                    <div class="doughnut-box">
                        <div style="width: 80px; height: 80px; border-radius: 50%; border: 10px solid #eee; border-top: 10px solid #f39c12;"></div>
                        <div style="position: absolute; top: 35px; left: 0; width: 100%; text-align: center; font-weight: bold;">40%</div>
                    </div>
                    <h4 style="margin: 10px 0 0 0;">Logical Ability</h4>
                    <p style="font-size: 9pt; color: #666;">Fair Proficiency</p>
                </td>
            </tr>
        </table>

        <div class="footer">Page 14 of 25</div>
    </div>

    <div class="page">
        <div class="header-strip">YOUR CAREER CLUSTERS</div>
        
        <div style="margin-top: 20px;">
        <?php foreach($career_clusters as $name => $data): ?>
            <div class="riasec-row">
                <div class="riasec-label" style="width: 35%;"><?= $name ?></div>
                <div class="riasec-bar-area" style="width: 55%;">
                    <div class="bar-bg">
                        <div class="bar-fill" style="width: <?= $data['match'] ?>%; background: #2c3e50;"></div>
                    </div>
                </div>
                <div class="riasec-val"><?= $data['match'] ?>%</div>
            </div>
        <?php endforeach; ?>
        </div>
        
        <div class="footer">Page 16 of 25</div>
    </div>

    <div class="page">
        <div class="header-strip">YOUR CAREER PATHS</div>
        
        <table class="clean" style="margin-top: 20px;">
            <thead>
                <tr>
                    <th width="40%">Career Path</th>
                    <th width="20%">Psy. Analysis</th>
                    <th width="20%">Skill Ability</th>
                    <th width="20%">Comment</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach(array_slice($career_clusters, 0, 8) as $name => $data): ?>
                <tr>
                    <td>
                        <strong><?= $name ?></strong><br>
                        <small style="color: #666;"><?= $data['desc'] ?? 'Relevant Field' ?></small>
                    </td>
                    <td>
                        <span class="c-green" style="font-weight: bold;">Very High: <?= $data['match'] ?></span>
                    </td>
                    <td>
                        <span class="c-orange">Average: <?= rand(50,70) ?></span>
                    </td>
                    <td>
                        <strong style="color: #2c3e50;">Good Choice</strong>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="footer">Page 21 of 25</div>
    </div>

</body>
</html>