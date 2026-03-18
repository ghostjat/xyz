<?php
    $c = $career ?? [];
    
    // SMART FORMATTER: Prevents "Array to string conversion" crashes
    $safePrint = function($val) {
        if (empty($val)) return 'N/A';
        
        if (is_array($val)) {
            // If it's an associative array (e.g., ["logical" => 5, "spatial" => 4])
            if (array_keys($val) !== range(0, count($val) - 1)) {
                $parts = [];
                foreach ($val as $k => $v) {
                    $vStr = is_array($v) ? json_encode($v) : $v; 
                    // Formats key nicely (e.g., "logical-mathematical" -> "Logical-mathematical")
                    $cleanKey = str_replace('-', ' ', ucfirst($k));
                    $parts[] = '<strong>' . esc($cleanKey) . '</strong>: ' . esc($vStr) . '/5';
                }
                return implode('<br>', $parts);
            }
            // Flat array (e.g., ["ISTP", "ESTP", "ISTJ"])
            $flat = array_map(function($v) { return is_array($v) ? json_encode($v) : esc($v); }, $val);
            return implode(', ', $flat);
        }
        
        // Standard String
        return esc($val);
    };

    // Safely format Salary (New schema stores this as a direct string like "$80,000 - $140,000+")
    $salary = !empty($c['salary']) ? esc($c['salary']) : 'Variable/Unspecified';
    
    // Safely format RIASEC (New schema uses "riasec" and stores arrays like ["RCI"])
    $riasec = 'N/A';
    if (!empty($c['riasec'])) {
        $riasec = is_array($c['riasec']) ? implode(', ', $c['riasec']) : $c['riasec'];
        // Remove formatting brackets if it came over as a raw string
        $riasec = str_replace(['"', '[', ']'], '', $riasec);
    }
?>

<tr><th class="bg-light" style="width:30%;">Category</th><td><span class="badge bg-primary"><?= esc($c['career_category'] ?? 'Uncategorized') ?></span></td></tr>
<tr><th class="bg-light">Introduction</th><td><?= $safePrint($c['intro'] ?? null) ?></td></tr>
<tr><th class="bg-light">Key Roles & Duties</th><td><small><?= $safePrint($c['roles'] ?? null) ?></small></td></tr>
<tr><th class="bg-light">Top Opportunities</th><td><small><?= $safePrint($c['opportunities'] ?? null) ?></small></td></tr>

<tr><th colspan="2" class="bg-secondary text-white fw-bold"><i class="fas fa-graduation-cap me-2"></i> Requirements & Skills</th></tr>
<tr><th class="bg-light">Academic Path</th><td><?= $safePrint($c['path'] ?? null) ?></td></tr>
<tr><th class="bg-light">Preparation Level</th><td><?= $safePrint($c['prep_level'] ?? null) ?></td></tr>
<tr><th class="bg-light">Colleges / Training</th><td><?= $safePrint($c['colleges'] ?? null) ?></td></tr>
<tr><th class="bg-light">Core Skills</th><td><?= $safePrint($c['skills'] ?? null) ?></td></tr>

<tr><th colspan="2" class="bg-info text-dark fw-bold"><i class="fas fa-brain me-2"></i> Psychometric Profile</th></tr>
<tr><th class="bg-light">Holland Code (RIASEC)</th><td class="fw-bold text-primary"><?= esc($riasec) ?></td></tr>
<tr><th class="bg-light">MBTI Personality Fit</th><td><?= $safePrint($c['mbti'] ?? null) ?></td></tr>
<tr><th class="bg-light">Aptitude Weights</th><td><small><?= $safePrint($c['aptitude_weights'] ?? null) ?></small></td></tr>
<tr><th class="bg-light">Dominant Intelligences</th><td><small><?= $safePrint($c['gardner_requirements'] ?? null) ?></small></td></tr>
<tr><th class="bg-light">EQ Requirements</th><td><small><?= $safePrint($c['eq_requirements'] ?? null) ?></small></td></tr>

<tr><th colspan="2" class="bg-success text-white fw-bold"><i class="fas fa-chart-line me-2"></i> Market Data</th></tr>
<tr><th class="bg-light">Expected Salary</th><td class="text-success fw-bold"><?= $salary ?></td></tr>
<tr><th class="bg-light">Education Costs (Fees)</th><td><?= $safePrint($c['fess'] ?? null) ?></td></tr>
<tr><th class="bg-light">Market Demand</th><td><?= $safePrint($c['demand'] ?? null) ?></td></tr>
<tr><th class="bg-light">Stress Level</th><td><?= $safePrint($c['s_level'] ?? null) ?></td></tr>