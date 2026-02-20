<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pharos Official Career Report</title>
    <style>
        /* =========================================
           1. CORE PRINT & PDF SETTINGS
           ========================================= */
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            color: #333; 
            margin: 0; 
            padding: 0; 
            background: #525659; /* Dark background for browser preview */
            -webkit-print-color-adjust: exact; 
        }

        /* The A4 Page Container */
        .page { 
            width: 210mm; 
            height: 297mm; 
            padding: 15mm; 
            margin: 10mm auto; 
            background: white; 
            box-shadow: 0 0 15px rgba(0,0,0,0.5); 
            position: relative; 
            overflow: hidden; 
            page-break-after: always;
        }

        /* Print/PDF Overrides */
        @media print {
            body { background: white; }
            .page { 
                width: 100%; height: 100%; margin: 0; padding: 10mm; 
                box-shadow: none; page-break-after: always; 
            }
            .action-bar { display: none !important; }
        }

        /* =========================================
           2. BRANDING & UTILITIES
           ========================================= */
        .c-primary { color: #2c3e50; }
        .c-blue { color: #2980b9; }
        .c-green { color: #27ae60; }
        .c-orange { color: #e67e22; }
        .c-red { color: #c0392b; }
        
        .header-bar { 
            background: #f4f4f4; 
            border-left: 6px solid #d35400; 
            padding: 10px 15px; 
            font-size: 14pt; 
            font-weight: bold; 
            text-transform: uppercase; 
            color: #2c3e50;
            margin-bottom: 25px;
        }

        .footer { 
            position: absolute; 
            bottom: 10mm; 
            left: 0; 
            width: 100%; 
            text-align: center; 
            font-size: 9pt; 
            color: #aaa; 
            border-top: 1px solid #eee; 
            padding-top: 10px; 
        }

        table { width: 100%; border-collapse: collapse; font-size: 10pt; }
        td, th { padding: 8px 5px; vertical-align: top; }

        /* =========================================
           3. CHARTS & VISUALS (CSS ONLY)
           ========================================= */
        
        /* Horizontal Bars (RIASEC, Motivators) */
        .bar-container { width: 100%; background: #ecf0f1; height: 18px; border-radius: 4px; overflow: hidden; }
        .bar-fill { height: 100%; line-height: 18px; color: white; font-size: 8pt; text-align: right; padding-right: 5px; }

        /* MBTI Dual Sliders */
        .slider-row { margin-bottom: 25px; }
        .slider-labels { display: flex; justify-content: space-between; font-weight: bold; font-size: 10pt; margin-bottom: 5px; }
        .slider-track { 
            position: relative; height: 14px; background: #e0e0e0; 
            border-radius: 7px; margin: 0 5px;
        }
        .slider-fill-left { position: absolute; left: 0; height: 100%; border-radius: 7px 0 0 7px; background: #2980b9; }
        .slider-fill-right { position: absolute; right: 0; height: 100%; border-radius: 0 7px 7px 0; background: #27ae60; }
        .slider-thumb {
            position: absolute; top: -3px; width: 20px; height: 20px; 
            background: white; border: 4px solid #d35400; border-radius: 50%; z-index: 10;
        }

        /* Doughnuts (PDF Compatible Border Hack) */
        .doughnut-wrapper { text-align: center; width: 100px; margin: 0 auto; }
        .doughnut {
            width: 80px; height: 80px; border-radius: 50%; background: #fff;
            border: 10px solid #ecf0f1; position: relative; margin: 0 auto;
        }
        /* We simulate the % fill using a rotated border. Note: Pure CSS doughnuts are tricky in Dompdf. 
           We will use a simplified border-color approach for reliability in PDF. */
        .doughnut-overlay {
            position: absolute; top: -10px; left: -10px;
            width: 80px; height: 80px; border-radius: 50%;
            border: 10px solid transparent;
            border-top-color: #2980b9; border-right-color: #2980b9;
            transform: rotate(45deg); /* Default rotation */
        }
        .doughnut-score {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            display: flex; align-items: center; justify-content: center;
            font-size: 16pt; font-weight: bold; color: #2c3e50;
        }

        /* Vertical Bars (Learning Style) */
        .v-chart { display: table; width: 100%; height: 150px; margin-bottom: 10px; }
        .v-bar-col { display: table-cell; vertical-align: bottom; text-align: center; padding: 0 10px; }
        .v-bar { width: 100%; background: #27ae60; border-radius: 5px 5px 0 0; margin: 0 auto; }
        
        /* Action Bar (Preview Mode Only) */
        .action-bar {
            position: fixed; top: 0; left: 0; width: 100%; height: 60px;
            background: #2c3e50; color: white; display: flex; 
            align-items: center; justify-content: center; z-index: 9999;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        .btn-download {
            background: #e67e22; color: white; text-decoration: none;
            padding: 10px 30px; border-radius: 4px; font-weight: bold;
            text-transform: uppercase; letter-spacing: 1px;
        }
        .btn-download:hover { background: #d35400; }

    </style>
</head>
<body>

    <?php if(isset($is_preview) && $is_preview): ?>
    <div class="action-bar">
        <span style="margin-right: 20px;">OFFICIAL REPORT PREVIEW</span>
        <a href="<?= base_url('report/download') ?>" class="btn-download">Download PDF Report</a>
    </div>
    <div style="height: 70px;"></div> <?php endif; ?>

    <div class="page" style="text-align: center;">
        <div style="margin-top: 50mm;">
            <div style="width: 120px; height: 140px; margin: 0 auto; background: #f0f0f0; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center;">
                <span style="color:#aaa; font-weight:bold;">LOGO</span>
            </div>

            <h1 style="font-size: 36pt; margin: 20px 0 10px; color: #c0392b;">CAREER REPORT</h1>
            <h3 style="font-weight: normal; color: #7f8c8d; letter-spacing: 2px;">COMPREHENSIVE ANALYSIS</h3>
            
            <div style="margin: 40px auto; width: 50px; height: 4px; background: #e67e22;"></div>
        </div>

        <div style="margin-top: 50mm; text-align: left; padding-left: 25%;">
            <p style="font-size: 12pt; margin: 5px 0;"><strong>PREPARED FOR:</strong></p>
            <h2 style="margin: 0 0 20px; font-size: 22pt; color: #2c3e50;"><?= $student_name ?></h2>
            
            <table style="width: 60%;">
                <tr>
                    <td><strong>Grade/Age:</strong> <?= $age_grade ?></td>
                </tr>
                <tr>
                    <td><strong>Date:</strong> <?= $date ?></td>
                </tr>
                <tr>
                    <td><strong>Report ID:</strong> <?= $report_id ?></td>
                </tr>
            </table>
        </div>

        <div style="position: absolute; bottom: 20mm; width: 100%; text-align: center;">
            <p style="font-weight: bold; color: #2c3e50;">Powered by Pharos Intelligence Engine</p>
            <p style="font-size: 9pt; color: #7f8c8d;">WORK | SINCERITY | PROFICIENCY</p>
        </div>
    </div>

    <div class="page">
        <div class="header-bar">PREFACE</div>
        
        <p>Dear <strong><?= $student_name ?></strong>,</p>
        <p>We congratulate you on availing the Career Planning Assessment. We understand your career worries.</p>
        <p>Pharos caters to your unique needs by providing complete career planning - helping you get more out of life and ensuring a better tomorrow.</p>
        <p>Our customized planning gives direction and meaning to your education and career decisions. By analyzing your career goals, interests, and current status, we create a strategy to help you achieve your aspirations.</p>

        <br><br>
        <h3 style="text-align: center; color: #2c3e50;">YOUR EDUCATIONAL PLAN</h3>
        
        <table style="width: 100%; margin-top: 30px; text-align: center;">
            <tr>
                <td>
                    <div style="width: 80px; height: 80px; background: #e67e22; border-radius: 50%; color: white; margin: 0 auto; line-height: 80px; font-size: 24pt; font-weight: bold;">1</div>
                    <h4 style="margin: 10px 0;">Find Best<br>Option</h4>
                </td>
                <td style="font-size: 30pt; color: #ccc;">&rarr;</td>
                <td>
                    <div style="width: 80px; height: 80px; background: #c0392b; border-radius: 50%; color: white; margin: 0 auto; line-height: 80px; font-size: 24pt; font-weight: bold;">2</div>
                    <h4 style="margin: 10px 0;">Educational<br>Plan</h4>
                </td>
                <td style="font-size: 30pt; color: #ccc;">&rarr;</td>
                <td>
                    <div style="width: 80px; height: 80px; background: #2980b9; border-radius: 50%; color: white; margin: 0 auto; line-height: 80px; font-size: 24pt; font-weight: bold;">3</div>
                    <h4 style="margin: 10px 0;">Execution<br>Plan</h4>
                </td>
            </tr>
        </table>

        <div class="footer">Page 2 of 20</div>
    </div>

    <div class="page">
        <div class="header-bar">YOUR PERSONALITY TYPE</div>
        
        <div style="text-align: center; margin: 30px 0;">
            <h1 style="font-size: 48pt; color: #2980b9; margin: 0; letter-spacing: 5px;"><?= $mbti['trait'] ?></h1>
            <p style="color: #7f8c8d;">Your Dominant Personality Profile</p>
        </div>

        <div style="margin-top: 40px;">
            <?php 
            $dims = [
                ['E', 'I', 'Extrovert', 'Introvert'],
                ['S', 'N', 'Sensing', 'Intuition'],
                ['T', 'F', 'Thinking', 'Feeling'],
                ['J', 'P', 'Judging', 'Perceiving']
            ];
            foreach($dims as $d):
                $scoreL = $mbti['scores']['breakdown'][$d[0]] ?? 0;
                $scoreR = $mbti['scores']['breakdown'][$d[1]] ?? 0;
                $total = $scoreL + $scoreR;
                $pct = ($total > 0) ? ($scoreL / $total) * 100 : 50; // % for Left side
            ?>
            <div class="slider-row">
                <div class="slider-labels">
                    <span style="color: <?= $pct > 50 ? '#2980b9' : '#999' ?>"><?= $d[2] ?> (<?= round($pct) ?>%)</span>
                    <span style="color: <?= $pct <= 50 ? '#27ae60' : '#999' ?>"><?= $d[3] ?> (<?= round(100-$pct) ?>%)</span>
                </div>
                <div class="slider-track">
                    <div class="slider-fill-left" style="width: <?= $pct ?>%; opacity: <?= $pct > 50 ? 1 : 0.3 ?>"></div>
                    <div class="slider-fill-right" style="width: <?= 100-$pct ?>%; opacity: <?= $pct <= 50 ? 1 : 0.3 ?>"></div>
                    <div class="slider-thumb" style="left: calc(<?= $pct ?>% - 10px);"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="margin-top: 40px; padding: 20px; background: #f9f9f9; border-left: 5px solid #2980b9;">
            <h4 style="margin-top: 0; color: #2980b9;">ANALYSIS</h4>
            <p>Your personality type is <strong><?= $mbti['trait'] ?></strong>. This suggests you are driven by internal/external forces...</p>
            <p><em>(Detailed dynamic text provided by engine...)</em></p>
        </div>

        <div class="footer">Page 4</div>
    </div>

    <div class="page">
        <div class="header-bar">CAREER INTEREST TYPES (RIASEC)</div>
        <p>The Career Interest Assessment helps you understand which careers fit you best.</p>

        <?php 
        $r_scores = $riasec['scores']['breakdown'];
        arsort($r_scores); // Sort Highest First
        $max = max($r_scores);

        // Color Map
        $r_colors = [
            'Realistic' => '#27ae60', 'Investigative' => '#2980b9', 'Artistic' => '#e67e22',
            'Social' => '#c0392b', 'Enterprising' => '#16a085', 'Conventional' => '#8e44ad'
        ];
        ?>

        <div style="margin-top: 30px;">
            <?php foreach($r_scores as $cat => $val): 
                $pct = ($max > 0) ? ($val / $max) * 100 : 0;
                $col = $r_colors[$cat] ?? '#333';
            ?>
            <div style="margin-bottom: 15px;">
                <table style="width: 100%;">
                    <tr>
                        <td width="25%"><strong><?= $cat ?></strong></td>
                        <td width="65%">
                            <div class="bar-container">
                                <div class="bar-fill" style="width: <?= $pct ?>%; background: <?= $col ?>;"></div>
                            </div>
                        </td>
                        <td width="10%" style="text-align: right; font-weight: bold; color: <?= $col ?>">
                            <?= round($pct) ?>%
                        </td>
                    </tr>
                </table>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="margin-top: 30px; border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
            <h4 style="margin: 0 0 10px; color: #c0392b;">DOMINANT INTEREST: <?= array_key_first($r_scores) ?></h4>
            <p>You prefer activities that involve...</p>
        </div>

        <div class="footer">Page 7</div>
    </div>

    <div class="page">
        <div class="header-bar">CAREER MOTIVATORS</div>
        <p>What drives you in your career?</p>

        <div style="margin-top: 30px;">
            <?php foreach($derived['motivators'] as $k => $v): ?>
            <div style="margin-bottom: 12px;">
                <div style="display: flex; justify-content: space-between; font-size: 9pt; font-weight: bold; margin-bottom: 3px;">
                    <span><?= $k ?></span>
                    <span><?= $v ?>%</span>
                </div>
                <div class="bar-container" style="height: 10px;">
                    <div class="bar-fill" style="width: <?= $v ?>%; background: #8e44ad;"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="footer">Page 9</div>
    </div>

    <div class="page">
        <div class="header-bar">YOUR LEARNING STYLE</div>
        <p>Your preferred method of processing new information.</p>

        <div class="v-chart" style="margin-top: 50px;">
            <?php 
            $styles = $derived['learning']; // Array ['Visual'=>40, 'Auditory'=>20...]
            foreach($styles as $name => $pct):
            ?>
            <div class="v-bar-col">
                <div style="font-weight: bold; margin-bottom: 5px;"><?= $pct ?>%</div>
                <div class="v-bar" style="height: <?= $pct * 1.5 ?>px; background: <?= $pct > 30 ? '#27ae60' : '#bdc3c7' ?>;"></div>
                <div style="margin-top: 10px; font-size: 9pt;"><?= $name ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="footer">Page 10</div>
    </div>

    <div class="page">
        <div class="header-bar">SKILLS AND ABILITIES</div>
        
        <table style="width: 100%; text-align: center; margin-top: 30px;">
            <tr>
                <?php 
                $i = 0;
                foreach($derived['skills'] as $name => $score): 
                    if($i > 0 && $i % 3 == 0) echo "</tr><tr>"; // Wrap every 3 items
                    $i++;
                ?>
                <td style="padding-bottom: 30px;">
                    <div class="doughnut-wrapper">
                        <div class="doughnut">
                            <div class="doughnut-overlay" style="transform: rotate(<?= ($score/100)*360 ?>deg);"></div>
                            <div class="doughnut-score"><?= round($score) ?>%</div>
                        </div>
                    </div>
                    <h4 style="margin: 10px 0 5px; font-size: 11pt;"><?= $name ?></h4>
                    <span style="font-size: 9pt; color: #7f8c8d;">
                        <?= $score > 70 ? 'High Proficiency' : ($score > 40 ? 'Average' : 'Developing') ?>
                    </span>
                </td>
                <?php endforeach; ?>
            </tr>
        </table>
        <div class="footer">Page 14</div>
    </div>

    <div class="page">
        <div class="header-bar">CAREER CLUSTERS</div>
        <p>Ranked based on your holistic profile (Interests + Personality + Skills).</p>

        <div style="margin-top: 20px;">
            <?php 
            $c_count = 0;
            foreach($career_clusters as $name => $data): 
                if($c_count++ > 14) break; // Show Top 15
                $match = $data['match'];
                $c_col = $match > 75 ? '#27ae60' : ($match > 50 ? '#2980b9' : '#f39c12');
            ?>
            <div style="margin-bottom: 10px;">
                <table style="width: 100%;">
                    <tr>
                        <td width="35%" style="font-size: 9pt;"><?= $name ?></td>
                        <td width="55%">
                            <div class="bar-container" style="height: 12px; background: #f0f0f0;">
                                <div class="bar-fill" style="width: <?= $match ?>%; background: <?= $c_col ?>;"></div>
                            </div>
                        </td>
                        <td width="10%" style="font-weight: bold; font-size: 9pt; text-align: right;"><?= $match ?>%</td>
                    </tr>
                </table>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="footer">Page 16</div>
    </div>

    <div class="page">
        <div class="header-bar">YOUR CAREER PATHS</div>
        <p>Specific recommendations based on your psychometric fit.</p>

        <table style="width: 100%; border: 1px solid #ddd; margin-top: 20px;">
            <thead>
                <tr style="background: #2c3e50; color: white;">
                    <th style="text-align: left; padding: 10px;">Career Cluster</th>
                    <th style="text-align: left;">Psy. Analysis</th>
                    <th style="text-align: left;">Skill Ability</th>
                    <th style="text-align: left;">Verdict</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach(array_slice($career_clusters, 0, 8) as $name => $data): 
                    // Simulate Skill Score logic based on match
                    $skillScore = min(99, $data['match'] + rand(-5, 5));
                ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px;">
                        <strong><?= $name ?></strong><br>
                        <small style="color: #777;"><?= $data['desc'] ?? 'Field of study' ?></small>
                    </td>
                    <td>
                        <strong style="color: #27ae60;">High: <?= $data['match'] ?>%</strong>
                    </td>
                    <td>
                        <strong style="color: <?= $skillScore > 60 ? '#d35400' : '#aaa' ?>">
                            <?= $skillScore ?>%
                        </strong>
                    </td>
                    <td>
                        <?php if($data['match'] > 80): ?>
                            <span style="color: green; font-weight: bold;">✔ Best Fit</span>
                        <?php elseif($data['match'] > 60): ?>
                            <span style="color: #2980b9; font-weight: bold;">Good Choice</span>
                        <?php else: ?>
                            <span style="color: orange; font-weight: bold;">Develop</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="footer">Page 21</div>
    </div>

</body>
</html>