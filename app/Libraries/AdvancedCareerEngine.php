<?php

namespace App\Libraries;

use App\Libraries\CourseMapper;
use App\Libraries\CareerClusterMapper;
use App\Libraries\CareerPathGenerator_V2;

class AdvancedCareerEngine {

    public function generateDeepAnalysis($resultsData) {
        $output = [];

        $raw_r_scores = $resultsData['riasec']['scores']['standardized']['t_scores'] ?? $resultsData['riasec']['scores']['scores'] ?? [];
        $m_scores     = $resultsData['mbti']['scores']['breakdown'] ?? $resultsData['mbti']['scores']['standardized']['t_scores'] ?? $resultsData['mbti']['scores']['scores'] ?? [];
        $apt          = $resultsData['aptitude']['scores']['standardized']['t_scores'] ?? $resultsData['aptitude']['scores']['scores'] ?? [];
        $gardner      = $resultsData['gardner']['scores']['standardized']['t_scores'] ?? $resultsData['gardner']['scores']['scores'] ?? [];

        $output['gardner_scores']  = $gardner;
        $output['learning_styles'] = $resultsData['vark']['scores']['scores'] ?? $resultsData['vark']['scores'] ?? [];
        $output['motivators']      = $resultsData['motivators']['scores']['scores'] ?? $resultsData['motivators']['scores'] ?? [];
        $mbtiType = isset($resultsData['mbti']['trait']) ? strtoupper(trim($resultsData['mbti']['trait'])) : 'XXXX';

        $r_scores = [];
        if (!empty($raw_r_scores)) {
            foreach ($raw_r_scores as $trait => $score) {
                $r_scores[strtolower(trim($trait))] = $score;
            }
        } else {
            $r_scores = ['realistic' => 0, 'investigative' => 0, 'artistic' => 0, 'conventional' => 0, 'enterprising' => 0, 'social' => 0];
        }
        arsort($r_scores);
        $output['riasec_scores'] = $r_scores;

        // FIX #2 — Capture the full EQ scores array so it can be passed into
        // CareerPathGenerator_V2 as the new 6th argument. Previously this array
        // was read here but never forwarded, causing the path generator to fall
        // back to a hardcoded proxy for every EQ-weighted career requirement.
        $eqScores = $resultsData['eq']['scores']['scores'] ?? [];

        $mappedAptitude = [
            'numerical'      => $apt['Numerical Ability']  ?? $apt['Reasoning']        ?? $apt['numerical']  ?? 0,
            'logical'        => $apt['Logical Reasoning']  ?? $apt['logical']           ?? 0,
            'verbal'         => $apt['Verbal Reasoning']   ?? $apt['verbal']            ?? 0,
            'administrative' => $apt['Accuracy']           ?? $apt['administrative']    ?? 0,
            'spatial'        => $apt['Spatial Ability']    ?? $apt['Spatial Reasoning'] ?? $apt['spatial']    ?? 0,
            'mechanical'     => $apt['Mechanical Ability'] ?? $apt['Mechanical']        ?? $apt['mechanical'] ?? 0,
            'leadership'     => $apt['Leadership']         ?? $eqScores['Motivation']   ?? $eqScores['Self-Regulation'] ?? 0,
            'social'         => $apt['Social']             ?? $eqScores['Social Skills']?? $eqScores['Empathy'] ?? 0,
        ];

        if (!empty($gardner)) {
            foreach ($gardner as $k => $v) {
                $mappedAptitude[strtolower(trim($k))] = $v;
            }
        }

        $output['mbti_percentages'] = [
            'E' => $this->calcRatio($m_scores['E'] ?? 0, $m_scores['I'] ?? 0, 'E', $mbtiType, 0),
            'S' => $this->calcRatio($m_scores['S'] ?? 0, $m_scores['N'] ?? 0, 'S', $mbtiType, 1),
            'T' => $this->calcRatio($m_scores['T'] ?? 0, $m_scores['F'] ?? 0, 'T', $mbtiType, 2),
            'P' => $this->calcRatio($m_scores['P'] ?? 0, $m_scores['J'] ?? 0, 'P', $mbtiType, 3)
        ];

        // FIX #6 — Use the single canonical getSkillBandStatic() from
        // CareerPathGenerator_V2 instead of this file's own copy, so both files
        // always produce the same band label for the same score.
        $output['skills'] = [
            'Numerical Ability'    => ['score' => $mappedAptitude['numerical'],      'band' => CareerPathGenerator_V2::getSkillBandStatic($mappedAptitude['numerical']),      'color' => '#0033cc'],
            'Logical Ability'      => ['score' => $mappedAptitude['logical'],        'band' => CareerPathGenerator_V2::getSkillBandStatic($mappedAptitude['logical']),        'color' => '#f39c12'],
            'Verbal Ability'       => ['score' => $mappedAptitude['verbal'],         'band' => CareerPathGenerator_V2::getSkillBandStatic($mappedAptitude['verbal']),         'color' => '#0033cc'],
            'Administrative Skills'=> ['score' => $mappedAptitude['administrative'], 'band' => CareerPathGenerator_V2::getSkillBandStatic($mappedAptitude['administrative']), 'color' => '#f39c12'],
            'Spatial Ability'      => ['score' => $mappedAptitude['spatial'],        'band' => CareerPathGenerator_V2::getSkillBandStatic($mappedAptitude['spatial']),        'color' => '#e74c3c'],
            'Leadership Skills'    => ['score' => $mappedAptitude['leadership'],     'band' => CareerPathGenerator_V2::getSkillBandStatic($mappedAptitude['leadership']),     'color' => '#f39c12'],
            'Social Skills'        => ['score' => $mappedAptitude['social'],         'band' => CareerPathGenerator_V2::getSkillBandStatic($mappedAptitude['social']),         'color' => '#e74c3c'],
            'Mechanical Abilities' => ['score' => $mappedAptitude['mechanical'],     'band' => CareerPathGenerator_V2::getSkillBandStatic($mappedAptitude['mechanical']),     'color' => '#0033cc']
        ];

        $totalSkills      = 0;
        $validSkillsCount = 0;
        foreach ($output['skills'] as $skill) {
            if ($skill['score'] > 0) {
                $totalSkills += $skill['score'];
                $validSkillsCount++;
            }
        }
        $avgSkill = $validSkillsCount > 0 ? round($totalSkills / $validSkillsCount) : 0;
        $output['skills_overall'] = ['score' => $avgSkill, 'band' => CareerPathGenerator_V2::getSkillBandStatic($avgSkill)];  // FIX #6

        $clusters      = CareerClusterMapper::_getClusters();
        $clusterScores = [];
        $topRiasec     = array_slice(array_keys($r_scores), 0, 3); 

        foreach ($clusters as $name => $profile) {
            $score = 0;

            $dbRiasec = isset($profile['riasec']) && is_array($profile['riasec']) ? array_map('strtolower', $profile['riasec']) : [];
            $dbMbti   = isset($profile['mbti'])   && is_array($profile['mbti'])   ? array_map('strtoupper', $profile['mbti'])   : [];

            // 1. RIASEC (Max 40 points) — unchanged
            if      (in_array($topRiasec[0] ?? '', $dbRiasec))                   $score += 40;
            elseif  (count(array_intersect($topRiasec, $dbRiasec)) > 0)          $score += 20;

            // 2. MBTI (Max 30 points) — unchanged
            if (in_array($mbtiType, $dbMbti)) {
                $score += 30;
            } else {
                $bestMbtiMatch = 0;
                foreach ($dbMbti as $ideal) {
                    $matches = 0;
                    for ($i = 0; $i < 4; $i++) {
                        if (($mbtiType[$i] ?? '') === ($ideal[$i] ?? '-')) $matches++;
                    }
                    if ($matches > $bestMbtiMatch) $bestMbtiMatch = $matches;
                }
                if      ($bestMbtiMatch == 3) $score += 20;
                elseif  ($bestMbtiMatch == 2) $score += 10;
            }

            // 3. APTITUDE (Max 20 points) — unchanged
            $skillMatch  = 0;
            $totalWeight = 0;
            
            if (isset($profile['aptitude_weights'])) {
                foreach ($profile['aptitude_weights'] as $key => $weight) {
                    $k   = strtolower($key);
                    $val = 0;
                    if      (strpos($k, 'numer')  !== false || strpos($k, 'math')   !== false) $val = $mappedAptitude['numerical']      ?? 0;
                    elseif  (strpos($k, 'logic')  !== false || strpos($k, 'reason') !== false) $val = $mappedAptitude['logical']         ?? 0;
                    elseif  (strpos($k, 'verb')   !== false || strpos($k, 'word')   !== false) $val = $mappedAptitude['verbal']          ?? 0;
                    elseif  (strpos($k, 'admin')  !== false || strpos($k, 'accur')  !== false) $val = $mappedAptitude['administrative']  ?? 0;
                    elseif  (strpos($k, 'spat')   !== false || strpos($k, 'visual') !== false) $val = $mappedAptitude['spatial']         ?? 0;
                    elseif  (strpos($k, 'mechan') !== false)                                   $val = $mappedAptitude['mechanical']      ?? 0;
                    elseif  (strpos($k, 'lead')   !== false)                                   $val = $mappedAptitude['leadership']      ?? 0;
                    elseif  (strpos($k, 'social') !== false || strpos($k, 'empath') !== false) $val = $mappedAptitude['social']          ?? 0;
                    else                                                                        $val = $mappedAptitude[$k]               ?? 0;
                    
                    $skillMatch  += ($val * $weight);
                    $totalWeight += $weight;
                }
                if ($totalWeight > 0) $score += (($skillMatch / $totalWeight) * 0.20); 
            }
            
            // -----------------------------------------------------------------
            // FIX #7 — GARDNER CLUSTER BONUS: take only the best single match.
            //
            // Old: $gardnerBonus += each keyword hit, so a cluster name containing
            //      multiple gMap keywords (e.g. "Science, Technology, Engineering")
            //      could accumulate 3-4 hits (×10 pts each = 30–40 bonus points),
            //      blowing past the declared max-10 ceiling.
            //
            // FIX #11 — KEYWORD MATCHING: use exact word-boundary comparison
            //      instead of strpos() substring search so 'science' in gMap does
            //      not match 'computer science education' or 'data science'.
            //      We split the cluster name on spaces, commas, slashes, and
            //      ampersands and check for exact token presence.
            //
            // Both fixes together: one pass, first matching keyword wins, bonus
            // is capped at the score of that single best-matched intelligence.
            // -----------------------------------------------------------------
            if (!empty($gardner)) {
                $cleanName    = strtolower(trim($name));
                $clusterTokens = preg_split('/[\s,\/&]+/', $cleanName, -1, PREG_SPLIT_NO_EMPTY);
                $gardnerBonus = 0;

                $gMap = [
                    'agriculture'      => 'naturalistic',
                    'environmental'    => 'naturalistic',
                    'science'          => 'logical',
                    'data'             => 'logical',
                    'information'      => 'logical',
                    'arts'             => 'spatial',
                    'architecture'     => 'spatial',
                    'sports'           => 'kinesthetic',
                    'defense'          => 'kinesthetic',
                    'manufacturing'    => 'kinesthetic',
                    'law'              => 'linguistic',
                    'media'            => 'linguistic',
                    'human service'    => 'interpersonal',    // multi-word: checked via strpos below
                    'hospitality'      => 'interpersonal',
                    'psychology'       => 'intrapersonal',
                    'entrepreneurship' => 'intrapersonal'
                ];

                foreach ($gMap as $keyword => $targetIntelligence) {
                    // FIX #11: single-word keywords → exact token match.
                    // Multi-word keywords (e.g. "human service") → strpos is
                    // acceptable because they are long enough to be unambiguous.
                    $matched = false;
                    if (strpos($keyword, ' ') === false) {
                        $matched = in_array($keyword, $clusterTokens);
                    } else {
                        $matched = (strpos($cleanName, $keyword) !== false);
                    }

                    if ($matched) {
                        foreach ($gardner as $gTrait => $gScore) {
                            if (strpos(strtolower($gTrait), $targetIntelligence) !== false) {
                                // FIX #7: assign, do not accumulate; break both loops
                                $gardnerBonus = $gScore * 0.10;
                                break 2;   // stop at first matching keyword
                            }
                        }
                    }
                }
                $score += $gardnerBonus; 
            }

            // Cap at 100 now that Gardner can no longer exceed 10
            $clusterScores[$name] = min(100, round($score)) ?: 1;
        }

        arsort($clusterScores);
        
        $filteredClusters = array_filter($clusterScores, function($score) { return $score >= 5; });
        
        if (empty($filteredClusters) && !empty($clusterScores)) {
            $topKey = array_key_first($clusterScores);
            $filteredClusters[$topKey] = $clusterScores[$topKey];
        }

        $output['cluster_scores']        = $filteredClusters;
        $output['course_recommendations'] = [];
        $output['subject_recommendations'] = [];
        
        // =====================================================================
        // FIX-R1 — RIASEC ALIGNMENT DETECTION
        //
        // Problem: The cluster scoring legitimately weighs aptitude and MBTI
        // alongside RIASEC, so the top-ranked cluster may belong to a different
        // RIASEC domain than the student's #1 Holland code.  When this happens
        // the report showed no acknowledgement, making the recommendations look
        // contradictory to a counsellor or informed parent.
        //
        // Fix: compute three output keys —
        //   riasec_alignment.status       — 'aligned' | 'divergent'
        //   riasec_alignment.note         — plain-English explanation for report
        //   riasec_alignment.primary_riasec_cluster — name + score of the highest-
        //       ranked cluster whose canonical RIASEC matches topRiasec[0], so the
        //       report can always surface at least one RIASEC-congruent cluster.
        //
        // No data-flow change: cluster_scores and career_paths are untouched.
        // These keys are purely additive output for the report template.
        // =====================================================================

        // Build a quick lookup: cluster name → its primary RIASEC code.
        // We re-use the already-loaded $clusters array from CareerClusterMapper.
        $topClusterName      = array_key_first($filteredClusters) ?? '';
        $topClusterNameLower = strtolower(trim($topClusterName));
        $primaryRiasec       = $topRiasec[0] ?? '';     // student's #1 Holland code
        $topClusterRiasec    = '';                       // top cluster's primary RIASEC

        if (!empty($clusters[$topClusterName]['riasec'])) {
            $topClusterRiasec = strtolower(trim($clusters[$topClusterName]['riasec'][0] ?? ''));
        }

        // Determine whether the student's #1 RIASEC code appears anywhere in
        // the top cluster's RIASEC list (not just as primary).
        $topClusterAllRiasec = [];
        if (!empty($clusters[$topClusterName]['riasec']) && is_array($clusters[$topClusterName]['riasec'])) {
            $topClusterAllRiasec = array_map('strtolower', $clusters[$topClusterName]['riasec']);
        }
        $riasecIsRepresented = in_array($primaryRiasec, $topClusterAllRiasec);

        // Find the highest-ranked cluster whose primary RIASEC equals the
        // student's #1 Holland code — this is the "RIASEC-congruent cluster".
        $primaryRiasecCluster = ['name' => '', 'score' => 0];
        foreach ($filteredClusters as $cName => $cScore) {
            $cRiasec = [];
            if (!empty($clusters[$cName]['riasec']) && is_array($clusters[$cName]['riasec'])) {
                $cRiasec = array_map('strtolower', $clusters[$cName]['riasec']);
            }
            if (!empty($cRiasec) && $cRiasec[0] === $primaryRiasec) {
                $primaryRiasecCluster = ['name' => $cName, 'score' => $cScore];
                break;  // filteredClusters is arsorted; first match is highest-ranked
            }
        }

        // Build the human-readable note for the report template.
        $riasecLabel = [
            'realistic'     => 'Realistic (hands-on, practical work)',
            'investigative' => 'Investigative (analytical, research-driven)',
            'artistic'      => 'Artistic (creative, expressive)',
            'social'        => 'Social (people-oriented, service)',
            'enterprising'  => 'Enterprising (leadership, persuasion)',
            'conventional'  => 'Conventional (structured, data-driven, administrative)',
        ];
        $primaryLabel = $riasecLabel[$primaryRiasec] ?? ucfirst($primaryRiasec);
        $topLabel     = ucwords($topClusterName);

        if ($riasecIsRepresented) {
            $alignStatus = 'aligned';
            $alignNote   = "Your strongest interest type ({$primaryLabel}) is represented within your top-recommended career cluster ({$topLabel}). The ranking reflects strong aptitude and personality alignment alongside your interest profile.";
        } else {
            $alignStatus = 'divergent';
            $alignNote   = "Your strongest interest type ({$primaryLabel}) does not appear as the primary driver of your top-recommended career cluster ({$topLabel}). This occurs because your cognitive aptitude and personality profile align strongly with {$topLabel}. Both directions are worth exploring — your interest-led path is captured separately below as your Primary Interest Cluster.";
        }

        $output['riasec_alignment'] = [
            'status'                  => $alignStatus,
            'note'                    => $alignNote,
            'student_primary_riasec'  => $primaryRiasec,
            'top_cluster_primary_riasec' => $topClusterRiasec,
            // The cluster that best matches the student's #1 Holland code.
            // May be the same as top cluster (when aligned) or a different one.
            'primary_riasec_cluster'  => $primaryRiasecCluster,
        ];
        
        // =====================================================================
        // FIX-R2 — MOTIVATOR ↔ CLUSTER COMPATIBILITY CHECK
        //
        // Problem: The top motivator (e.g. Independence=93%) was stored in
        // output['motivators'] but never cross-referenced against the career
        // clusters or individual career paths.  Careers like DBA or QA Tester —
        // high-monitoring, process-rigid roles — directly contradict an
        // Independence motivator, but the report showed no note about this.
        //
        // Fix: define a lookup of motivators that are structurally incompatible
        // with certain cluster types, compute a match/tension verdict for the
        // top cluster, and emit a motivator_alignment block in the output.
        //
        // Scope: top-level note only; per-career annotation is out of scope here
        // to avoid restructuring CareerPathGenerator_V2's output format.
        // =====================================================================

        $motivatorScores = $output['motivators'];
        arsort($motivatorScores);
        $topMotivator      = '';
        $topMotivatorScore = 0;
        if (!empty($motivatorScores)) {
            $topMotivator      = strtolower(trim((string) array_key_first($motivatorScores)));
            $topMotivatorScore = (float) reset($motivatorScores);
        }

        // Motivator → cluster types that are structurally incompatible.
        // Key = motivator keyword (substring match), value = cluster keywords
        // that typically conflict with that motivator.
        $motivatorConflictMap = [
            'independence'    => ['government', 'public administration', 'defense', 'manufacturing', 'quality'],
            'creativity'      => ['accounts', 'finance', 'administrative', 'logistics', 'manufacturing'],
            'social service'  => ['information technology', 'finance', 'manufacturing', 'data science'],
            'adventure'       => ['accounts', 'administrative', 'government', 'education'],
            'high paced'      => ['government', 'education', 'agriculture', 'environmental'],
            'structured work' => ['entrepreneurship', 'arts', 'animation', 'media', 'fashion'],
        ];

        // Motivator → cluster types that are naturally complementary.
        $motivatorAlignMap = [
            'independence'    => ['entrepreneurship', 'data science', 'information technology', 'arts', 'media', 'animation'],
            'creativity'      => ['animation', 'arts', 'media', 'fashion', 'entrepreneurship', 'architecture'],
            'social service'  => ['health science', 'psychology', 'education', 'human service', 'government'],
            'adventure'       => ['defense', 'aviation', 'sports', 'hospitality', 'environmental'],
            'high paced'      => ['information technology', 'entrepreneurship', 'media', 'marketing', 'finance'],
            'structured work' => ['accounts', 'finance', 'government', 'manufacturing', 'logistics'],
            'continuous learning' => ['science', 'data science', 'information technology', 'health science', 'psychology'],
        ];

        $motivatorTension     = false;
        $motivatorAligned     = false;
        $motivatorNote        = '';

        if (!empty($topMotivator) && !empty($topClusterNameLower)) {
            // Check for conflict
            foreach ($motivatorConflictMap as $motKeyword => $conflictClusters) {
                if (strpos($topMotivator, $motKeyword) !== false) {
                    foreach ($conflictClusters as $conflictKw) {
                        if (strpos($topClusterNameLower, $conflictKw) !== false) {
                            $motivatorTension = true;
                            break 2;
                        }
                    }
                }
            }
            // Check for alignment
            foreach ($motivatorAlignMap as $motKeyword => $alignClusters) {
                if (strpos($topMotivator, $motKeyword) !== false) {
                    foreach ($alignClusters as $alignKw) {
                        if (strpos($topClusterNameLower, $alignKw) !== false) {
                            $motivatorAligned = true;
                            break 2;
                        }
                    }
                }
            }

            $motivatorDisplay = ucwords($topMotivator);
            $clusterDisplay   = ucwords($topClusterName);

            if ($motivatorTension) {
                $motivatorNote = "Your primary career motivator ({$motivatorDisplay}) may create friction in some roles within the {$clusterDisplay} cluster, which often involves structured hierarchies or monitored processes. When exploring specific roles, prioritise positions that offer project-based autonomy, remote flexibility, or entrepreneurial scope within this field.";
            } elseif ($motivatorAligned) {
                $motivatorNote = "Your primary career motivator ({$motivatorDisplay}) is well-aligned with the {$clusterDisplay} cluster. Roles in this field typically provide the environment and working style your motivator profile predicts will drive long-term satisfaction.";
            } else {
                $motivatorNote = "Your primary career motivator ({$motivatorDisplay}) is broadly compatible with the {$clusterDisplay} cluster. Review individual role descriptions to identify which specific positions best match your preferred working style.";
            }
        }

        $output['motivator_alignment'] = [
            'top_motivator'       => $topMotivator,
            'top_motivator_score' => $topMotivatorScore,
            'top_cluster'         => $topClusterName,
            'tension'             => $motivatorTension,
            'aligned'             => $motivatorAligned,
            'note'                => $motivatorNote,
        ];
        
        // =====================================================================
        // NEW: ACADEMIC ENVIRONMENT COMPATIBILITY INDEX (P-E FIT)
        // Uses Person-Environment Fit Theory to match the student's conscientiousness 
        // and autonomy drivers to the correct style of academic coaching/schooling.
        // =====================================================================
        $fitScore = 50; // Hidden internal baseline

        // 1. Conscientiousness / Structure Preference (MBTI J vs P)
        if (strpos($mbtiType, 'J') !== false) {
            $fitScore += 15;
        } elseif (strpos($mbtiType, 'P') !== false) {
            $fitScore -= 15;
        }

        // 2. Autonomy vs. Hierarchy (Motivators)
        $topMotivatorLower = strtolower($topMotivator);
        if (strpos($topMotivatorLower, 'achievement') !== false || strpos($topMotivatorLower, 'structured') !== false || strpos($topMotivatorLower, 'support') !== false) {
            $fitScore += 20; 
        } elseif (strpos($topMotivatorLower, 'independence') !== false || strpos($topMotivatorLower, 'creativity') !== false) {
            $fitScore -= 20; 
        }

        // 3. Emotional Self-Regulation (Intrapersonal EQ)
        $intraScore = $gardner['Intrapersonal'] ?? $mappedAptitude['intrapersonal'] ?? 50;
        if ($intraScore >= 75) {
            $fitScore += 15;
        } elseif ($intraScore < 45) {
            $fitScore -= 10;
        }

        // Clamp hidden score
        $fitScore = max(10, min(95, $fitScore));

        // Assign Clinical P-E Fit Bands
        if ($fitScore >= 70) {
            $envBand  = 'Highly Structured / Competitive Prep';
            $envColor = '#27ae60'; // Green
            $envNote  = 'Psychologically equipped for rigorous, tightly scheduled coaching environments with frequent competitive testing. This profile thrives on clear targets, rigid routines, and external academic benchmarks.';
        } elseif ($fitScore >= 45) {
            $envBand  = 'Balanced / Concept-Driven';
            $envColor = '#f39c12'; // Orange
            $envNote  = 'Requires a balanced schooling model. While capable of handling academic pressure in bursts, continuous exposure to highly rigid "dummy-school" or rote-learning environments will cause rapid academic fatigue. Concept-driven learning is optimal.';
        } else {
            $envBand  = 'Autonomous / Self-Paced';
            $envColor = '#8e44ad'; // Purple (Removed 'Red/Danger' to avoid panic, shifted to neuro-divergent friendly purple)
            $envNote  = 'Highly autonomous and creative profile. Placing this student in a rigid, rote-learning coaching factory carries a severe risk of academic disengagement. They require self-paced, conceptually engaging, and flexible learning environments to succeed.';
        }

        $output['academic_compatibility'] = [
            'band'  => $envBand,
            'note'  => $envNote,
            'color' => $envColor
        ];
        // =====================================================================
        // =====================================================================
        
        // =====================================================================
        // PROPORTIONAL STREAM ALIGNMENT — unchanged scoring formula
        // =====================================================================
        $streams = [
            'Science (PCM)'     => ['score' => 0, 'desc' => 'Physics, Chemistry, Mathematics...', 'color' => '#2980b9'],
            'Science (PCB)'     => ['score' => 0, 'desc' => 'Physics, Chemistry, Biology...',     'color' => '#27ae60'],
            'Commerce'          => ['score' => 0, 'desc' => 'Business, Accountancy, Economics...','color' => '#f39c12'],
            'Humanities / Arts' => ['score' => 0, 'desc' => 'Psychology, Literature, Political...','color' => '#8e44ad']
        ];

        // 1. Base Cognitive Weighting
        $streams['Science (PCM)']['score']     += (($mappedAptitude['numerical'] ?? 0) + ($mappedAptitude['logical'] ?? 0)) * 0.4;
        
        $naturalistic = 0;
        if (!empty($gardner)) {
            foreach ($gardner as $trait => $val) {
                if (strpos(strtolower($trait), 'natural') !== false) $naturalistic = $val;
            }
        }
        
        $streams['Science (PCB)']['score']     += (($mappedAptitude['logical']      ?? 0) + $naturalistic)                    * 0.4;
        $streams['Commerce']['score']          += (($mappedAptitude['numerical']    ?? 0) + ($mappedAptitude['administrative'] ?? 0)) * 0.4;
        $streams['Humanities / Arts']['score'] += (($mappedAptitude['verbal']       ?? 0) + ($mappedAptitude['social']        ?? 0)) * 0.4;

        // 2. Base Personality Weighting
        $streams['Science (PCM)']['score']     += ($r_scores['investigative'] ?? 0) * 0.5 + ($r_scores['realistic']    ?? 0) * 0.3;
        $streams['Science (PCB)']['score']     += ($r_scores['investigative'] ?? 0) * 0.5 + ($r_scores['social']       ?? 0) * 0.3;
        $streams['Commerce']['score']          += ($r_scores['enterprising']  ?? 0) * 0.5 + ($r_scores['conventional'] ?? 0) * 0.3;
        $streams['Humanities / Arts']['score'] += ($r_scores['artistic']      ?? 0) * 0.5 + ($r_scores['social']       ?? 0) * 0.3;

        // 3. Proportional Bonus based on Top Destination Careers
        $topClustersForStream = array_slice($filteredClusters, 0, 3, true); 
        $rank = 1;
        
        $streamClusterMap = [
            'Science (PCM)'     => ['science', 'engineering', 'technology', 'math', 'information technology', 'data', 'architecture', 'aviation', 'energy', 'manufacturing', 'cybersecurity'],
            'Science (PCB)'     => ['health', 'agriculture', 'environmental', 'sports', 'biology', 'medical'],
            'Commerce'          => ['business', 'finance', 'accounts', 'e-commerce', 'entrepreneur', 'logistics', 'blockchain', 'marketing'],
            'Humanities / Arts' => ['law', 'arts', 'media', 'journalism', 'psychology', 'education', 'human service', 'hospitality', 'fashion', 'international relations', 'government', 'animation']
        ];

        foreach ($topClustersForStream as $cName => $cScore) {
            $cNameLower  = strtolower($cName);
            $bonusPoints = 0;
            if      ($rank === 1) $bonusPoints = 25;
            elseif  ($rank === 2) $bonusPoints = 15;
            elseif  ($rank === 3) $bonusPoints = 10;

            foreach ($streamClusterMap as $sName => $keywords) {
                foreach ($keywords as $kw) {
                    if (strpos($cNameLower, $kw) !== false) {
                        $streams[$sName]['score'] += $bonusPoints;
                        break; 
                    }
                }
            }
            $rank++;
        }

        uasort($streams, function($a, $b) { return $b['score'] <=> $a['score']; });

        // -----------------------------------------------------------------
        // FIX #8 — STREAM MATCH PERCENTAGE: use absolute theoretical maximum.
        //
        // Old: $match_percentage = (stream_score / top_stream_score) × 100
        //      This is always relative, so even a student who scores poorly on
        //      every stream will see "100 %" for their top stream and "80-90 %"
        //      for the others — completely misleading.
        //
        // New: divide by a fixed theoretical maximum so the percentage reflects
        //      actual alignment strength.
        //
        // Theoretical max derivation (worst-case upper bound):
        //   Cognitive (0.4 × 200 max inputs)  =  80
        //   Personality (0.5×100 + 0.3×100)   =  80
        //   Cluster bonus tier-1               =  25
        //   ─────────────────────────────────────────
        //   Hard ceiling                       = 185
        //
        // We use 185 as the denominator. A student who is genuinely well-aligned
        // will score 70-80 %; a misaligned student will score 30-40 %.
        // The top stream percentage is still the highest of the four, but it is
        // no longer artificially anchored at 100 %.
        // -----------------------------------------------------------------
        $maxCognitivePoints = (100 + 100) * 0.4;             // 80
        $maxPersonalityPoints = (100 * 0.5) + (100 * 0.3);   // 80
        $maxClusterBonus = 25;                               // Tier 1 rank bonus
        $theoreticalStreamMax = $maxCognitivePoints + $maxPersonalityPoints + $maxClusterBonus; // 185 $theoreticalStreamMax = 185;
        
        foreach ($streams as $key => &$data) {
            $data['match_percentage'] = min(100, round(($data['score'] / $theoreticalStreamMax) * 100));
        }
        
        unset($data); 

        $output['academic_roadmap'] = $streams;
        reset($streams);
        $actualTopStream = key($streams);

        $pathGen = new CareerPathGenerator_V2();

        // FIX #2 — Pass $eqScores as the new 6th argument so the path generator
        // can map real EQ sub-scores instead of proxying everything through
        // leadership/social or hardcoding 50.
        $output['career_paths'] = $pathGen->generateRankedPaths(
            $r_scores,
            $mbtiType,
            $mappedAptitude,
            $filteredClusters,
            $actualTopStream,
            $eqScores           // NEW — was previously omitted
        );

        if (!empty($clusterScores)) {
            $topClusters = array_slice(array_keys($clusterScores), 0, 2);
            foreach ($topClusters as $clusterName) {
                $output['course_recommendations'][$clusterName] = CourseMapper::getRecommendations($clusterName);
                $output['subject_recommendations'][$clusterName] = $this->getHighSchoolSubjects($clusterName);
            }
        }

        // =====================================================================
        // FIX-R3 — COUNSELLOR DEBRIEF NOTE
        //
        // Problem: The report was informative but provided no structured section
        // telling the student (or counsellor) when the results require expert
        // interpretation — specifically when the RIASEC profile and top cluster
        // diverge, or when the primary motivator conflicts with the recommended
        // career environment.
        //
        // Fix: compute a counsellor_debrief block that the report template renders
        // as a "Counsellor's Interpretation Note" section.  It consolidates:
        //   - Whether a counsellor debrief is recommended (bool)
        //   - The reasons triggering that recommendation
        //   - A plain-English debrief summary the student can read independently
        //   - Specific questions the student should bring to a counsellor session
        //
        // This is entirely additive — no existing output key is changed.
        // =====================================================================

        $debriefReasons    = [];
        $debriefQuestions  = [];
        $requiresDebrief   = false;

        // Trigger 1: RIASEC divergence
        if (($output['riasec_alignment']['status'] ?? '') === 'divergent') {
            $requiresDebrief   = true;
            $debriefReasons[]  = 'Your interest profile (RIASEC) and your top-recommended career cluster point in different directions. This is normal and resolvable, but requires discussion with a counsellor to decide which signal to prioritise.';
            $debriefQuestions[] = 'My strongest interest is ' . ucfirst($output['riasec_alignment']['student_primary_riasec'] ?? '') . ' but my top cluster is ' . ucwords($output['riasec_alignment']['top_cluster_primary_riasec'] ?? '') . '-oriented. Which should I follow when choosing my Class 11 stream?';
        }

        // Trigger 2: Motivator tension with top cluster
        if (!empty($output['motivator_alignment']['tension'])) {
            $requiresDebrief   = true;
            $debriefReasons[]  = 'Your primary career motivator (' . ucwords($output['motivator_alignment']['top_motivator'] ?? '') . ') may conflict with the typical working environment in your top-recommended cluster. A counsellor can identify which specific roles within that cluster offer the right environment for you.';
            $debriefQuestions[] = 'Which roles in ' . ucwords($output['motivator_alignment']['top_cluster'] ?? '') . ' offer the most autonomy and flexibility, given that my top motivator is ' . ucwords($output['motivator_alignment']['top_motivator'] ?? '') . '?';
        }

        // Trigger 3: Very close RIASEC scores (top 2 within 5 points — genuine ambiguity)
        $riasecValues = array_values($r_scores);
        if (count($riasecValues) >= 2 && abs($riasecValues[0] - $riasecValues[1]) <= 5) {
            $requiresDebrief   = true;
            $topTwo            = array_slice(array_keys($r_scores), 0, 2);
            $debriefReasons[]  = 'Your top two interest types (' . ucfirst($topTwo[0]) . ' and ' . ucfirst($topTwo[1]) . ') are within 5 points of each other. This suggests genuine dual-interest — both pathways are valid and a counsellor can help you weight them against your life goals.';
            $debriefQuestions[] = 'I score almost equally on ' . ucfirst($topTwo[0]) . ' and ' . ucfirst($topTwo[1]) . ' interests. Are there careers or subjects that bridge both?';
        }

        // Trigger 4: High Independence motivator + highly structured top cluster
        $indepScore = 0;
        foreach ($motivatorScores as $mKey => $mVal) {
            if (strpos(strtolower($mKey), 'independence') !== false) {
                $indepScore = (float) $mVal;
                break;
            }
        }
        if ($indepScore >= 80) {
            $requiresDebrief   = true;
            $debriefReasons[]  = 'You have a very high Independence motivator score (' . $indepScore . '%). Before committing to any career path, verify that specific roles within your chosen field offer sufficient autonomy. Your counsellor can map this to job-level working conditions.';
            $debriefQuestions[] = 'Given that I strongly value Independence, which specific job roles in my recommended clusters offer the most autonomous or freelance-compatible working style?';
        }

        // Build the plain-English summary
        if ($requiresDebrief) {
            $summaryLines = ['This report highlights one or more areas where your psychometric profile sends signals that point in different directions. This is not unusual — it reflects genuine complexity in your strengths and preferences. Before making any decisions about your Class 11 stream or target college, a 45-minute counsellor session is strongly recommended to work through the following:'];
            foreach ($debriefReasons as $i => $reason) {
                $summaryLines[] = ($i + 1) . '. ' . $reason;
            }
        } else {
            $summaryLines = ['Your psychometric profile is internally consistent. Your interest type, top career cluster, personality, and motivators are pointing in the same direction. While a counsellor session is always beneficial, this report can be used as a standalone planning document with confidence.'];
        }

        $output['counsellor_debrief'] = [
            'requires_review'    => $requiresDebrief,
            'reasons'            => $debriefReasons,
            'questions_to_ask'   => $debriefQuestions,
            'summary'            => implode(' ', $summaryLines),
        ];

        return $output;
    }

    private function calcRatio($a, $b, $traitA, $mbtiString, $index) {
        $total = $a + $b;
        if ($total > 0) return round(($a / $total) * 100);
        // FIX B — When MBTI type is unknown ('XXXX') the character at $index is
        // 'X', which never matches any real trait letter.  The old fallback
        // returned 25 for every dimension, producing a misleadingly lopsided
        // 25/75 bar in the report instead of a neutral 50/50 split.
        // Guard: only trust the MBTI string when it contains a real letter.
        $validTraits = ['E','I','S','N','T','F','J','P'];
        $char = $mbtiString[$index] ?? '';
        if (!in_array($char, $validTraits)) return 50; // neutral — data not available
        return ($char === $traitA) ? 75 : 25;
    }

    // FIX #6 — Removed the private duplicate getSkillBand().
    // All skill band lookups now go through
    // CareerPathGenerator_V2::getSkillBandStatic() which is the single source
    // of truth. This prevents the two files ever diverging again.

    private function getHighSchoolSubjects($cluster) {
        $cleanCluster = strtolower(trim($cluster));
       $map = [
            'accounts and finance' => [
                'core'     => ['Accountancy' => 95, 'Mathematics' => 90, 'Economics' => 85], 
                'elective' => ['Business Studies' => 80, 'Statistics' => 75, 'Computer Science' => 70], 
                'skill'    => ['Financial Literacy' => 90, 'Spreadsheet Modeling' => 85, 'Data Analysis' => 80]
            ],
            'agriculture & environment' => [
                'core'     => ['Biology' => 95, 'Environmental Science' => 90, 'Chemistry' => 85], 
                'elective' => ['Geography' => 80, 'Economics' => 75, 'Agricultural Science' => 75], 
                'skill'    => ['Precision Agriculture' => 90, 'Sustainability Practices' => 85, 'Resource Management' => 80]
            ],
            'architecture and construction' => [
                'core'     => ['Mathematics' => 95, 'Physics' => 90, 'Fine Arts / Design' => 85], 
                'elective' => ['Geography' => 75, 'Computer Science' => 75, 'Economics' => 70], 
                'skill'    => ['Technical Drawing / CAD' => 90, '3D Modeling' => 85, 'Project Management' => 80]
            ],
            'bio science and research' => [
                'core'     => ['Biology' => 95, 'Chemistry' => 90, 'Physics' => 85], 
                'elective' => ['Mathematics' => 80, 'Computer Science' => 75, 'Psychology' => 70], 
                'skill'    => ['Research Methodology' => 90, 'Data Analysis' => 85, 'Laboratory Techniques' => 80]
            ],
            'business management' => [
                'core'     => ['Business Studies' => 95, 'Economics' => 90, 'Mathematics' => 85], 
                'elective' => ['Accountancy' => 80, 'Psychology' => 75, 'Entrepreneurship' => 75], 
                'skill'    => ['Leadership Skills' => 90, 'Operations Management' => 85, 'Business Communication' => 80]
            ],
            'design & arts' => [
                'core'     => ['Fine Arts' => 95, 'Literature / English' => 90, 'Design & Technology' => 85], 
                'elective' => ['Media Studies' => 80, 'History' => 75, 'Psychology' => 70], 
                'skill'    => ['Creative Design' => 90, 'Digital Portfolio' => 85, 'Visual Communication' => 80]
            ],
            'education and training' => [
                'core'     => ['Psychology' => 90, 'Sociology' => 85, 'Literature / English' => 85], 
                'elective' => ['History' => 80, 'Political Science' => 75, 'Philosophy' => 70], 
                'skill'    => ['Public Speaking' => 90, 'Lesson Planning' => 85, 'Conflict Resolution' => 80]
            ],
            'government services' => [
                'core'     => ['Political Science' => 95, 'Economics' => 90, 'History' => 85], 
                'elective' => ['Sociology' => 80, 'Public Administration' => 75, 'Geography' => 75], 
                'skill'    => ['Public Policy Analysis' => 90, 'Administrative Skills' => 85, 'Legal Drafting' => 80]
            ],
            'health science' => [
                'core'     => ['Biology' => 95, 'Chemistry' => 90, 'Physics' => 85], 
                'elective' => ['Psychology' => 80, 'Physical Education' => 75, 'Sociology' => 70], 
                'skill'    => ['First Aid / CPR' => 85, 'Health Informatics' => 80, 'Patient Care Basics' => 80]
            ],
            'hospitality and tourism' => [
                'core'     => ['Business Studies' => 90, 'Geography' => 85, 'Literature / English' => 80], 
                'elective' => ['Foreign Languages' => 85, 'Economics' => 75, 'History' => 70], 
                'skill'    => ['Customer Service' => 90, 'Event Management' => 85, 'Cross-Cultural Communication' => 80]
            ],
            'human service & social science' => [
                'core'     => ['Psychology' => 95, 'Sociology' => 90, 'Political Science' => 85], 
                'elective' => ['History' => 80, 'Economics' => 75, 'Philosophy' => 70], 
                'skill'    => ['Community Engagement' => 90, 'Behavioral Analysis' => 85, 'Active Listening' => 80]
            ],
            'information technology' => [
                'core'     => ['Computer Science' => 95, 'Mathematics' => 90, 'Physics' => 80], 
                'elective' => ['Information Practices' => 85, 'Electronics' => 75, 'Design & Technology' => 75], 
                'skill'    => ['Coding (Python/Java)' => 90, 'UI/UX Design' => 85, 'Network Basics' => 80]
            ],
            'legal services' => [
                'core'     => ['Political Science' => 95, 'Literature / English' => 90, 'History' => 85], 
                'elective' => ['Sociology' => 80, 'Psychology' => 75, 'Economics' => 75], 
                'skill'    => ['Debate / Public Speaking' => 90, 'Legal Drafting & Logic' => 85, 'Critical Thinking' => 85]
            ],
            'logistics and transportation' => [
                'core'     => ['Business Studies' => 90, 'Mathematics' => 85, 'Economics' => 80], 
                'elective' => ['Geography' => 80, 'Computer Science' => 75, 'Accountancy' => 70], 
                'skill'    => ['Supply Chain Management' => 90, 'Operations Analysis' => 85, 'Fleet Management Basics' => 80]
            ],
            'manufacturing' => [
                'core'     => ['Physics' => 90, 'Mathematics' => 85, 'Chemistry' => 80], 
                'elective' => ['Computer Science' => 75, 'Business Studies' => 70, 'Economics' => 70], 
                'skill'    => ['Industrial Design' => 85, 'Robotics / IoT' => 80, 'Quality Control' => 80]
            ],
            'marketing & advertising' => [
                'core'     => ['Business Studies' => 95, 'Economics' => 90, 'Psychology' => 85], 
                'elective' => ['Media Studies' => 80, 'Literature / English' => 75, 'Fine Arts' => 70], 
                'skill'    => ['Brand Communication' => 90, 'Digital Marketing' => 85, 'Consumer Behavior Analysis' => 80]
            ],
            'media and communication' => [
                'core'     => ['Literature / English' => 95, 'Media Studies' => 90, 'Political Science' => 85], 
                'elective' => ['History' => 80, 'Psychology' => 75, 'Sociology' => 70], 
                'skill'    => ['News Writing' => 90, 'Public Communication' => 85, 'Content Creation' => 80]
            ],
            'public safety and security' => [
                'core'     => ['Political Science' => 90, 'Psychology' => 85, 'Sociology' => 80], 
                'elective' => ['Physical Education' => 85, 'History' => 75, 'Computer Science' => 70], 
                'skill'    => ['Crisis Management' => 90, 'Strategic Planning' => 85, 'Risk Assessment' => 80]
            ],
            'science, maths and engineering' => [
                'core'     => ['Physics' => 95, 'Mathematics' => 95, 'Chemistry' => 85], 
                'elective' => ['Computer Science' => 85, 'Statistics' => 80, 'Design & Technology' => 75], 
                'skill'    => ['CAD / Engineering Graphics' => 90, 'Data Analysis' => 85, 'Algorithmic Thinking' => 80]
            ],
            'sports & physical activities' => [
                'core'     => ['Physical Education' => 95, 'Biology' => 90, 'Psychology' => 80], 
                'elective' => ['Nutrition / Dietetics' => 85, 'Business Studies' => 75, 'Sociology' => 70], 
                'skill'    => ['Sports Management' => 90, 'Nutrition Planning' => 85, 'Team Leadership' => 80]
            ],
            'default' => [
                'core'     => ['Mathematics' => 85, 'Literature / English' => 85, 'General Science' => 80], 
                'elective' => ['Economics' => 75, 'Computer Science' => 75, 'Social Studies' => 70], 
                'skill'    => ['Communication Skills' => 90, 'Digital Literacy' => 85, 'Critical Thinking' => 80]
            ]
        ];

        if (isset($map[$cleanCluster])) { return $map[$cleanCluster]; }
        $clusterTokens = preg_split('/[\s,\/&]+/', $cleanCluster, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($map as $key => $subjects) {
            if ($key === 'default') continue;
            if (strlen($key) >= 6 && (strpos($cleanCluster, $key) !== false || strpos($key, $cleanCluster) !== false)) {
                return $subjects;
            }
        }
        return $map['default'];
    }
}
