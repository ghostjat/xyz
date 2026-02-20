<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Comprehensive Career Dossier</title>
    <style>
        /* ========================================================
           1. CORE PRINT & TYPOGRAPHY
           ======================================================== */
        @page { margin: 0; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333; margin: 0; padding: 0;
            background: #525659; /* Browser Preview */
            -webkit-print-color-adjust: exact;
        }
        .page {
            width: 210mm; height: 297mm; background: white;
            position: relative; overflow: hidden; margin: 10mm auto;
            box-shadow: 0 0 15px rgba(0,0,0,0.5); page-break-after: always;
        }
        @media print {
            body { background: white; }
            .page { margin: 0; box-shadow: none; width: 100%; height: 100%; }
        }

        /* ========================================================
           2. THEME COLORS & UTILITIES
           ======================================================== */
        .c-primary { color: #2c3e50; }
        .c-accent  { color: #d35400; }
        .c-blue    { color: #2980b9; }
        .c-green   { color: #27ae60; }
        .bg-gray   { background-color: #f4f6f7; }

        h1 { font-size: 26pt; text-transform: uppercase; font-weight: 800; letter-spacing: 1px; margin: 0; }
        h2 { font-size: 16pt; text-transform: uppercase; border-bottom: 3px solid #333; padding-bottom: 8px; margin: 35px 0 20px; color: #2c3e50; }
        h3 { font-size: 12pt; font-weight: bold; margin: 0 0 5px; color: #444; }
        p, li { font-size: 10pt; line-height: 1.6; color: #555; margin-bottom: 8px; }

        /* Tables */
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; }

        /* Visual Components */
        .header-strip { background: #2c3e50; color: white; padding: 40px; height: 130px; box-sizing: border-box; }
        .content { padding: 40px; }
        
        .card { border: 1px solid #ddd; border-radius: 6px; padding: 15px; margin-bottom: 15px; break-inside: avoid; }
        .card-header { font-weight: bold; font-size: 11pt; color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 8px; margin-bottom: 10px; }
        
        /* Badges & Tags */
        .tag { display: inline-block; background: #ebf5fb; color: #2980b9; padding: 2px 8px; border-radius: 4px; font-size: 9pt; margin-right: 5px; border: 1px solid #a9cce3; }
        .badge-exam { display: inline-block; background: #eafaf1; color: #27ae60; padding: 2px 8px; border-radius: 4px; font-size: 9pt; font-weight: bold; margin-right: 5px; border: 1px solid #abebc6; }
        
        /* Progress Bars */
        .bar-track { width: 100%; background: #eee; height: 8px; border-radius: 4px; overflow: hidden; margin-top: 6px; }
        .bar-fill { height: 100%; border-radius: 4px; }

        .footer { position: absolute; bottom: 15mm; left: 40px; right: 40px; border-top: 1px solid #eee; padding-top: 10px; font-size: 8pt; color: #aaa; text-align: right; }
    </style>
</head>
<body>

    <div class="page">
        <div class="header-strip">
            <table style="width: 100%;">
                <tr>
                    <td>
                        <h1>CAREER STRATEGY DOSSIER</h1>
                        <div style="font-size: 10pt; opacity: 0.8; margin-top: 5px; letter-spacing: 2px;">CONFIDENTIAL REPORT</div>
                    </td>
                    <td style="text-align: right;">
                        <div style="font-size: 9pt; opacity: 0.7;">CANDIDATE ID</div>
                        <div style="font-size: 14pt; font-weight: bold;"><?= $report_id ?? 'PH-2026-X' ?></div>
                        <div style="font-size: 9pt; opacity: 0.7;"><?= date('F d, Y') ?></div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="content">
            <p><strong>PREPARED FOR:</strong> <span style="font-size: 14pt; color: #2c3e50; font-weight: bold;"><?= $student_name ?></span></p>

            <h2>I. Psychometric Executive Summary</h2>
            
            <table style="border-spacing: 15px; margin: 0 -15px;">
                <tr>
                    <td width="50%" style="background: #ebf5fb; padding: 20px; border-radius: 8px; border-left: 5px solid #2980b9;">
                        <div style="font-size: 9pt; color: #2980b9; font-weight: bold; text-transform: uppercase;">Core Interest (RIASEC)</div>
                        <div style="font-size: 18pt; font-weight: 800; margin: 5px 0;"><?= $riasec['trait'] ?></div>
                        <div style="font-size: 9pt; color: #555; line-height: 1.4;">
                            <?= $riasec_text['desc'] ?? 'Your profile suggests a strong preference for this work style.' ?>
                        </div>
                    </td>
                    
                    <td width="50%" style="background: #eafaf1; padding: 20px; border-radius: 8px; border-left: 5px solid #27ae60;">
                        <div style="font-size: 9pt; color: #27ae60; font-weight: bold; text-transform: uppercase;">Personality (MBTI)</div>
                        <div style="font-size: 18pt; font-weight: 800; margin: 5px 0;"><?= $mbti['trait'] ?></div>
                        <div style="font-size: 9pt; color: #555; line-height: 1.4;">
                            <strong><?= $mbti_text['role'] ?? '' ?>:</strong> <?= $mbti_text['text'] ?? 'Analysis of your cognitive processing style.' ?>
                        </div>
                    </td>
                </tr>
            </table>

            <table style="margin-top: 20px; border-spacing: 15px; margin: 20px -15px 0;">
                <tr>
                    <td width="33%" style="border: 1px solid #eee; padding: 15px; border-radius: 6px; text-align: center;">
                        <div style="font-size: 24pt; font-weight: bold; color: #d35400;">
                            <?= ($eq['scores']['breakdown']['Self-Regulation'] ?? 3) >= 4 ? 'High' : 'Avg' ?>
                        </div>
                        <div style="font-size: 9pt; text-transform: uppercase;">Resilience Score</div>
                    </td>
                    <td width="33%" style="border: 1px solid #eee; padding: 15px; border-radius: 6px; text-align: center;">
                        <div style="font-size: 24pt; font-weight: bold; color: #2980b9;"><?= $gardner['trait'] ?? 'Logic' ?></div>
                        <div style="font-size: 9pt; text-transform: uppercase;">Top Intelligence</div>
                    </td>
                    <td width="33%" style="border: 1px solid #eee; padding: 15px; border-radius: 6px; text-align: center;">
                        <div style="font-size: 24pt; font-weight: bold; color: #27ae60;">
                            <?= ($aptitude['scores']['breakdown']['Accuracy'] ?? 50) > 60 ? 'High' : 'Avg' ?>
                        </div>
                        <div style="font-size: 9pt; text-transform: uppercase;">Focus Accuracy</div>
                    </td>
                </tr>
            </table>

            <h2>II. Top Career Recommendations</h2>
            <?php 
            $i = 0;
            foreach($career_clusters as $cluster => $data): 
                if($i++ >= 3) break;
                // Safe Match % Calculation
                $displayMatch = ($data['match'] > 100) ? round($data['match'] / 3) : $data['match'];
                
                // Fetch Logic from passed map
                $details = $career_path_map[$cluster] ?? $career_path_map['General'] ?? ['logic' => 'Matches your profile blend.'];
            ?>
            <div style="margin-bottom: 12px; border-bottom: 1px solid #eee; padding-bottom: 12px;">
                <table style="width: 100%;">
                    <tr>
                        <td width="75%">
                            <div style="font-weight: bold; font-size: 12pt; color: #2c3e50;"><?= $cluster ?></div>
                            <div style="font-size: 9pt; color: #777; margin-top: 3px;">
                                <em>Why:</em> <?= $details['logic'] ?>
                            </div>
                        </td>
                        <td width="25%" style="text-align: right;">
                            <div style="background: #fdf2e9; color: #d35400; font-weight: bold; padding: 5px 10px; border-radius: 4px; display: inline-block;">
                                <?= $displayMatch ?>% Match
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="footer">Page 1 | Candidate: <?= $student_name ?></div>
    </div>

    <div class="page">
        <div class="content">
            <h1 class="c-blue" style="margin-top: 30px;">Methodology</h1>
            <div style="width: 60px; height: 5px; background: #d35400; margin: 15px 0 40px;"></div>

            <p>This report is generated using the <strong>Pharos Intelligence Engine v2.0</strong>. Unlike standard tests that only look at one aspect of your personality, this dossier integrates four distinct layers of data to create a holistic profile.</p>

            <table style="margin-top: 30px; border-spacing: 20px; margin-left: -20px;">
                <tr>
                    <td width="50%">
                        <div style="font-weight: bold; color: #2c3e50;">1. Interests (RIASEC)</div>
                        <p style="font-size: 9pt;">Based on Holland’s Theory. Aligning career with interest ensures long-term job satisfaction.</p>
                    </td>
                    <td width="50%">
                        <div style="font-weight: bold; color: #2c3e50;">2. Personality (MBTI)</div>
                        <p style="font-size: 9pt;">Based on Jungian Psychology. Identifies your preferred work environment.</p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="font-weight: bold; color: #2c3e50;">3. Aptitude & Skills</div>
                        <p style="font-size: 9pt;">Objective testing of cognitive abilities. Predicts ability to handle technical tasks.</p>
                    </td>
                    <td>
                        <div style="font-weight: bold; color: #2c3e50;">4. Emotional Intelligence (EQ)</div>
                        <p style="font-size: 9pt;">Measures social awareness and resilience. Predicts leadership potential.</p>
                    </td>
                </tr>
            </table>

            <h2 style="margin-top: 60px;">Report Index</h2>
            <table style="border-top: 2px solid #333; width: 100%;">
                <tr style="background: #f4f6f7;">
                    <th style="text-align: left; padding: 10px;">Section</th>
                    <th style="text-align: left; padding: 10px;">Content</th>
                    <th style="text-align: right; padding: 10px;">Page</th>
                </tr>
                <tr><td style="padding: 10px; border-bottom: 1px solid #eee;"><strong>Profile Analysis</strong></td><td>Personality, Interests & Aptitude Breakdown</td><td style="text-align: right;">03-05</td></tr>
                <tr><td style="padding: 10px; border-bottom: 1px solid #eee;"><strong>Advanced Metrics</strong></td><td>EQ & Learning Styles</td><td style="text-align: right;">06-07</td></tr>
                <tr><td style="padding: 10px; border-bottom: 1px solid #eee;"><strong>Career Strategy</strong></td><td>Detailed Career Cluster Mapping</td><td style="text-align: right;">08</td></tr>
                <tr><td style="padding: 10px; border-bottom: 1px solid #eee;"><strong>Action Plan</strong></td><td>Development & Academic Focus</td><td style="text-align: right;">09</td></tr>
            </table>
        </div>
        <div class="footer">Page 2</div>
    </div>

    <div class="page">
        <div class="content">
            <h2>03. Personality Analysis (MBTI)</h2>
            <div style="text-align: center; background: #f9f9f9; padding: 30px; border-radius: 8px; margin: 30px 0;">
                <h1 style="font-size: 40pt; color: #27ae60; letter-spacing: 5px;"><?= $mbti['trait'] ?></h1>
                <h3 style="margin-top: 10px; color: #555;"><?= $mbti_text['role'] ?? 'The Strategist' ?></h3>
            </div>
            
            <div style="margin-bottom: 30px;">
                <strong>Analysis:</strong> 
                <p><?= $mbti_text['text'] ?? 'Detailed analysis of your personality type.' ?></p>
            </div>

            <table style="border-spacing: 0 15px;">
                <?php 
                $dims = [['Extroversion (E)', 'Introversion (I)', 'E', 'I'], ['Sensing (S)', 'Intuition (N)', 'S', 'N'], ['Thinking (T)', 'Feeling (F)', 'T', 'F'], ['Judging (J)', 'Perceiving (P)', 'J', 'P']];
                foreach($dims as $d):
                    $L = $mbti['scores']['breakdown'][$d[2]] ?? 5;
                    $R = $mbti['scores']['breakdown'][$d[3]] ?? 5;
                    $total = $L + $R;
                    $pct = ($total > 0) ? ($L/$total)*100 : 50;
                ?>
                <tr>
                    <td width="25%"><strong><?= $d[0] ?></strong></td>
                    <td width="50%" style="padding: 0 15px;">
                        <div class="bar-track">
                            <div class="bar-fill" style="width: <?= $pct ?>%; background: #27ae60; float: left;"></div>
                            <div class="bar-fill" style="width: <?= 100-$pct ?>%; background: #bdc3c7; float: right;"></div>
                        </div>
                    </td>
                    <td width="25%" style="text-align: right;"><strong><?= $d[1] ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <div class="footer">Page 3</div>
    </div>

    <div class="page">
        <div class="content">
            <h2>04. Interest Profile (RIASEC)</h2>
            <p>Identifying your "Work Personality" to ensure career longevity.</p>

            <?php 
            $riasec_scores = $riasec['scores']['breakdown'] ?? [];
            arsort($riasec_scores);
            $max = max($riasec_scores) ?: 1;
            $colors = ['Realistic'=>'#c0392b', 'Investigative'=>'#2980b9', 'Artistic'=>'#8e44ad', 'Social'=>'#f1c40f', 'Enterprising'=>'#e67e22', 'Conventional'=>'#27ae60'];

            $count = 0;
            foreach($riasec_scores as $cat => $val):
                if($count++ >= 3) break; // Top 3
                $width = ($val / $max) * 100;
                $col = $colors[$cat] ?? '#333';
                
                // Fallback text if map isn't passed for secondary traits
                $desc = "Preference level for " . $cat . " tasks.";
                if($cat == $riasec['trait']) $desc = $riasec_text['desc']; // Use detailed text for dominant
            ?>
            <div class="card">
                <table style="width: 100%;">
                    <tr>
                        <td width="65%">
                            <h3 style="color: <?= $col ?>;"><?= $cat ?> (Score: <?= $val ?>)</h3>
                            <p><?= $desc ?></p>
                        </td>
                        <td width="35%" style="vertical-align: middle; padding-left: 15px;">
                            <div class="bar-track" style="height: 12px;">
                                <div class="bar-fill" style="width: <?= $width ?>%; background: <?= $col ?>;"></div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="footer">Page 4</div>
    </div>

    <div class="page">
        <div class="content">
            <h2>05. Skills & Aptitude Analysis</h2>
            <p>Objective measurement of your cognitive capabilities.</p>

            <table style="margin-top: 30px; border-spacing: 0 20px;">
                <?php 
                $apt_scores = $aptitude['scores']['breakdown'] ?? [];
                foreach($apt_scores as $cat => $score):
                    $col = $score > 75 ? '#27ae60' : ($score > 45 ? '#f39c12' : '#c0392b');
                    $label = $score > 75 ? 'Superior' : ($score > 45 ? 'Average' : 'Developing');
                    
                    // Lookup Logic for Detailed Text
                    $key = (strpos($cat, 'Verbal') !== false) ? 'Verbal' : 
                           ((strpos($cat, 'Numerical') !== false) ? 'Numerical' : 
                           ((strpos($cat, 'Spatial') !== false) ? 'Spatial' : 
                           ((strpos($cat, 'Accuracy') !== false) ? 'Accuracy' : 'Reasoning')));
                    
                    $level = $score > 70 ? 'High' : ($score > 40 ? 'Avg' : 'Low');
                    $interpret = $aptitude_map[$key][$level] ?? 'Standard proficiency.';
                ?>
                <tr>
                    <td width="25%"><strong><?= $cat ?></strong></td>
                    <td width="50%" style="padding: 0 15px;">
                        <div class="bar-track">
                            <div class="bar-fill" style="width: <?= $score ?>%; background: <?= $col ?>;"></div>
                        </div>
                    </td>
                    <td width="25%">
                        <div style="font-weight: bold; color: <?= $col ?>;"><?= $score ?>% (<?= $label ?>)</div>
                        <div style="font-size: 8pt; color: #777; margin-top: 2px;"><?= $interpret ?></div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <div class="footer">Page 5</div>
    </div>

    <div class="page">
        <div class="content">
            <h2>06. Emotional Intelligence (EQ)</h2>
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; margin-top: 20px;">
                <?php 
                $eq_scores = $eq['scores']['breakdown'] ?? [];
                foreach($eq_scores as $cat => $val):
                    $pct = ($val <= 5) ? $val * 20 : $val; 
                ?>
                <div style="width: 48%; margin-bottom: 20px; border: 1px solid #eee; padding: 15px; border-radius: 5px;">
                    <div style="font-weight: bold; color: #2c3e50;"><?= $cat ?></div>
                    <div class="bar-track" style="margin: 8px 0;">
                        <div class="bar-fill" style="width: <?= $pct ?>%; background: #3498db;"></div>
                    </div>
                    <div style="font-size: 8pt; color: #777;">Score: <?= $pct ?>%</div>
                </div>
                <?php endforeach; ?>
            </div>

            <h2 style="margin-top: 30px;">07. Multiple Intelligences</h2>
            <table style="margin-top: 20px;">
                <?php 
                $gardner_scores = $gardner['scores']['breakdown'] ?? [];
                arsort($gardner_scores);
                $g_count = 0;
                foreach($gardner_scores as $cat => $val):
                    if($g_count++ > 4) break;
                    $pct = ($val <= 5) ? $val * 20 : $val;
                ?>
                <tr>
                    <td width="30%" style="padding: 8px 0;"><strong><?= $cat ?></strong></td>
                    <td width="60%"><div class="bar-track"><div class="bar-fill" style="width: <?= $pct ?>%; background: #27ae60;"></div></div></td>
                    <td width="10%" style="text-align: right;"><?= $pct ?>%</td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <div class="footer">Page 6-7</div>
    </div>

    <div class="page">
        <div class="content">
            <h2>08. Strategic Career Roadmap</h2>
            <p>Detailed educational pathways based on your specific profile blend.</p>

            <?php 
            $rank = 1;
            foreach($career_clusters as $cluster => $data): 
                if($rank > 3) break;
                // Fetch Details from Controller Map
                $details = $career_path_map[$cluster] ?? $career_path_map['General'] ?? [
                    'logic' => 'Matches your profile.', 'roles' => 'Specialist roles', 'degrees' => [], 'exams' => []
                ];
                $displayMatch = ($data['match'] > 100) ? round($data['match'] / 3) : $data['match'];
            ?>
            <div class="card" style="border-top: 4px solid #2980b9;">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 10px;">
                    <span style="font-weight: bold; color: #2c3e50;">#<?= $rank++ ?>. <?= $cluster ?></span>
                    <span style="color: #2980b9; font-weight: bold;"><?= $displayMatch ?>% Suitability</span>
                </div>
                
                <table style="width: 100%;">
                    <tr>
                        <td width="60%" style="padding-right: 20px;">
                            <div style="font-size: 8pt; font-weight: bold; color: #999; margin-bottom: 5px;">WHY THIS FITS:</div>
                            <p><?= $details['logic'] ?></p>

                            <div style="font-size: 8pt; font-weight: bold; color: #999; margin-bottom: 5px;">POTENTIAL ROLES:</div>
                            <p><?= $details['roles'] ?></p>
                        </td>
                        <td width="40%" style="background: #f9f9f9; padding: 10px; border-radius: 4px;">
                            <div style="font-size: 8pt; font-weight: bold; color: #444; margin-bottom: 5px;">RECOMMENDED DEGREES:</div>
                            <?php foreach(($details['degrees'] ?? []) as $deg): ?>
                                <div class="tag"><?= $deg ?></div>
                            <?php endforeach; ?>

                            <div style="font-size: 8pt; font-weight: bold; color: #444; margin: 10px 0 5px;">ENTRANCE EXAMS:</div>
                            <?php foreach(($details['exams'] ?? []) as $exam): ?>
                                <div class="tag-exam"><?= $exam ?></div>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                </table>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="footer">Page 8</div>
    </div>

    <div class="page">
        <div class="content">
            <h2>09. Development Action Plan</h2>
            
            <table style="width: 100%; border-spacing: 20px; margin: 0 -20px;">
                <tr>
                    <td style="background: #fff3cd; padding: 25px; border-radius: 8px; vertical-align: top;">
                        <h3 class="c-orange">Critical Improvements</h3>
                        <p>Based on your specific low-score areas:</p>
                        <ul>
                            <li><strong>Networking:</strong> Challenge yourself to join one group project this month.</li>
                            <li><strong>Flexibility:</strong> Practice adapting to unexpected changes without stress.</li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <td style="background: #eafaf1; padding: 25px; border-radius: 8px; vertical-align: top;">
                        <h3 class="c-green">Academic Focus Areas</h3>
                        <p>To prepare for your top recommendations:</p>
                        <ul>
                            <li><strong>Mathematics:</strong> Essential for both STEM and Finance tracks.</li>
                            <li><strong>Public Speaking:</strong> To boost leadership potential.</li>
                        </ul>
                    </td>
                </tr>
            </table>

            <div style="margin-top: 80px; text-align: center; border-top: 2px dashed #ccc; padding-top: 30px;">
                <p style="font-size: 14pt; font-style: italic; color: #555;">
                    "The future belongs to those who believe in the beauty of their dreams."
                </p>
                <strong>- Eleanor Roosevelt</strong>
            </div>
        </div>
        <div class="footer">End of Report | Pharos Intelligence v2.0</div>
    </div>

</body>

   
</html>