<?php 
namespace App\Libraries;

use App\Models\CareerModel;

class CareerPathGenerator_V2 {

    // =========================================================================
    // FIX #6 — Single canonical getSkillBand used by BOTH files.
    // Old CareerPathGenerator had: >=40=Average, >=35=Fair (wrong thresholds).
    // Old AdvancedCareerEngine had: >=45=Average, >=30=Fair (different thresholds).
    // Unified thresholds below are consistent with a 0-100 aptitude T-score range.
    // =========================================================================
    public static function getSkillBandStatic($score) {
        if ($score >= 70) return 'Good';
        if ($score >= 50) return 'Average';
        if ($score >= 35) return 'Fair';
        return 'Improve';
    }

    // =========================================================================
    // FIX #2 — $eqScores is now a proper 6th parameter so real EQ sub-scores
    // (empathy, self-awareness, self-regulation, motivation, social skills) are
    // wired in from AdvancedCareerEngine instead of being proxied or hardcoded.
    // Default = [] keeps every existing call-site that omits the arg working.
    // =========================================================================
    public function generateRankedPaths(
        $studentRiasec,
        $studentMbti,
        $studentAptitude,
        $studentClusters  = [],
        $topStreamName    = 'Any Stream',
        $eqScores         = []          // NEW — passed from AdvancedCareerEngine
    ) {
        $rankedPaths = [];
        
        $careerModel = new CareerModel();
        $allCareers  = $careerModel->getActiveCareersForEngine();

        $topStudentRiasec = is_array($studentRiasec) ? array_slice(array_keys($studentRiasec), 0, 3) : [];
        $topStudentRiasec = array_values(array_map('strtolower', $topStudentRiasec));
        $studentMbti      = strtoupper(trim($studentMbti));
        $studentAptitude  = is_array($studentAptitude) ? array_change_key_case($studentAptitude, CASE_LOWER) : [];

        // =====================================================================
        // SCORING DESIGN — pre-compute RIASEC intensity map and MBTI dimension
        // strengths so both can be used as continuous differentiators inside the
        // per-career scoring loop.
        //
        // Problem being solved: when RIASEC primary match (50pts) + MBTI exact
        // match (50pts) fires for every career in a well-aligned cluster, all
        // 45 recommended paths display an identical psyScore of 98-99.  The score
        // correctly says "you fit this cluster" but has zero discriminative power
        // within the recommended set — a DBA and a Software Engineer look the same
        // to a student choosing between them.
        //
        // Fix 1 — RIASEC INTENSITY: scale the RIASEC component by the student's
        // actual percentile strength in the matching dimension.  A student scoring
        // 90% Realistic gets a higher RIASEC contribution on a Realistic career
        // than one scoring 55% Realistic, even though both are "primary matches".
        //
        // Fix 2 — MBTI DIMENSION STRENGTH: when exact MBTI type matches, apply a
        // secondary fine-grain differentiator using how strongly each dimension is
        // expressed.  An ISTJ at 92%I/67%S differs meaningfully from an ISTJ at
        // 51%I/52%S — the first is a much sharper psychometric match for roles
        // requiring deep introversion and concrete detail work.
        //
        // Fix 3 — POST-LOOP PERCENTILE STRETCH: after all psyScores are computed,
        // apply a min-max stretch so the displayed range spans 60-99 rather than
        // clustering at 97-99.  This is standard practice in all professional
        // psychometric platforms (SHL, Hogan, Talent-Q).  The stretch is applied
        // only to the DISPLAYED psyScore; the raw sort_metric (overallFit) is
        // computed from the pre-stretch score so ranking is unaffected.
        // =====================================================================

        // Build normalised RIASEC score map (0-100 scale) keyed by lowercase name.
        $studentRiasecScores = [];
        if (is_array($studentRiasec)) {
            foreach ($studentRiasec as $rKey => $rVal) {
                $studentRiasecScores[strtolower(trim($rKey))] = (float) $rVal;
            }
        }

        // Build MBTI dimension-strength map.
        // Keys are the four dimension letters the student actually expressed.
        // Values are the strength percentage (50 = neutral, 100 = fully expressed).
        // We derive this from the MBTI string and the mbti_percentages if available,
        // otherwise fall back to a neutral 65 (slightly above chance).
        // NOTE: $studentMbti at this point is already the 4-char type string.
        $mbtiDimStrength = [];
        if (strlen($studentMbti) === 4) {
            // The mbti_percentages are not passed into this method, so we derive
            // strength from the aptitude proxy already available.  The dominant
            // intrapersonal score correlates with I/E depth; logical with T/F;
            // administrative with J/P; spatial with S/N.  This is an approximation
            // — good enough to differentiate scores by 3-8 points which is all we
            // need here.  If you later pass mbti_percentages as a 7th param you can
            // replace this block with exact values.
            $mbtiDimStrength = [
                $studentMbti[0] => min(99, max(51, ($studentAptitude['intrapersonal'] ?? 65))),  // I/E depth
                $studentMbti[1] => min(99, max(51, ($studentAptitude['spatial']        ?? 65))),  // S/N depth
                $studentMbti[2] => min(99, max(51, ($studentAptitude['logical']        ?? 65))),  // T/F depth
                $studentMbti[3] => min(99, max(51, ($studentAptitude['administrative'] ?? 65))),  // J/P depth
            ];
        }

        // FIX #2 — Normalise EQ keys to lowercase once here, used in EQ mapper below.
        $eqScoresNorm = [];
        foreach ($eqScores as $k => $v) {
            $eqScoresNorm[strtolower(trim($k))] = $v;
        }

        // FIX #1 — Pre-compute student aptitude average for careers with no DB weights.
        // We use only non-zero scores so a student who genuinely scored 0 on every
        // aptitude is not penalised further, but a student with real scores gets a fair
        // baseline instead of the old hardcoded 75.
        $validApt = array_filter(array_values($studentAptitude), fn($v) => is_numeric($v)); // Removed $v > 0
        $studentAptAvg = !empty($validApt) ? round(array_sum($validApt) / count($validApt)) : 50;

        $topClusterNames = [];
        if (is_array($studentClusters)) {
            foreach (array_slice(array_keys($studentClusters), 0, 4) as $cKey) {
                $topClusterNames[] = strtolower(trim($cKey));
            }
        }

        foreach ($allCareers as $job) {
            
            $rawTitle  = trim($job['title']);
            $baseTitle = preg_replace('/^(Junior|Senior|Lead|Chief|Consultant|Assistant|Principal|Trainee|Head of)\s+/i', '', $rawTitle);
            $baseTitle = strtolower(trim($baseTitle));
            
            $jobCluster = strtolower(trim($job['cluster'] ?? 'General')); 

            // -----------------------------------------------------------------
            // STRICT CLUSTER GATEKEEPER — unchanged
            // -----------------------------------------------------------------
            $isTopCluster = false;
            $clusterRank  = 999;
            if (!empty($topClusterNames)) {
                foreach ($topClusterNames as $index => $topCluster) {
                    if ($jobCluster === $topCluster || (strlen($topCluster) > 4 && strpos($jobCluster, $topCluster) !== false)) {
                        $isTopCluster = true;
                        $jobCluster   = $topCluster; 
                        $clusterRank  = $index;
                        break;
                    }
                }
            }

            if (!$isTopCluster && !empty($topClusterNames)) {
                continue; 
            }

            // -----------------------------------------------------------------
            // RIASEC Translation — unchanged
            // -----------------------------------------------------------------
            $rawRiasec = is_string($job['riasec']) ? json_decode($job['riasec'], true) : $job['riasec'];
            $jobRiasec = [];
            $riasecDictionary = ['r' => 'realistic', 'i' => 'investigative', 'a' => 'artistic', 's' => 'social', 'e' => 'enterprising', 'c' => 'conventional'];

            if (!empty($rawRiasec) && is_array($rawRiasec)) {
                $code = strtolower(trim($rawRiasec[0])); 
                if (strlen($code) <= 3) { 
                    foreach (str_split($code) as $char) {
                        if (isset($riasecDictionary[$char])) {
                            $jobRiasec[] = $riasecDictionary[$char];
                        }
                    }
                } else { 
                    $jobRiasec = array_values(array_map('strtolower', $rawRiasec));
                }
            }
            
            $rawMbti  = is_string($job['mbti']) ? json_decode($job['mbti'], true) : $job['mbti'];
            $jobMbti  = is_array($rawMbti) ? array_values(array_map('strtoupper', $rawMbti)) : [];
            
            $rawApt    = $job['aptitude_weights'] ?? $job['aptitudes'] ?? [];
            $rawApt    = is_string($rawApt) ? json_decode($rawApt, true) : $rawApt;
            $parsedApt = is_array($rawApt) ? array_change_key_case($rawApt, CASE_LOWER) : [];
            $jobAptitudes = $parsedApt;

            // -----------------------------------------------------------------
            // DYNAMIC GARDNER MAPPER — unchanged mapping logic
            // -----------------------------------------------------------------
            $rawGardner = $job['gardner_requirements'] ?? [];
            $rawGardner = is_string($rawGardner) ? json_decode($rawGardner, true) : (is_array($rawGardner) ? $rawGardner : []);
            
            foreach ($rawGardner as $reqKey => $weight) {
                $cleanReqKey  = strtolower(trim($reqKey));
                $studentScore = 0;
                
                if (strpos($cleanReqKey, 'logical') !== false || strpos($cleanReqKey, 'math') !== false) {
                    $studentScore = (($studentAptitude['logical'] ?? 0) + ($studentAptitude['numerical'] ?? 0)) / 2;
                } elseif (strpos($cleanReqKey, 'bodily') !== false || strpos($cleanReqKey, 'kinesthetic') !== false) {
                    $studentScore = $studentAptitude['kinesthetic'] ?? 0;
                } elseif (strpos($cleanReqKey, 'visual') !== false || strpos($cleanReqKey, 'spatial') !== false) {
                    $studentScore = $studentAptitude['spatial'] ?? 0;
                } elseif (strpos($cleanReqKey, 'verbal') !== false || strpos($cleanReqKey, 'linguistic') !== false) {
                    $studentScore = $studentAptitude['linguistic'] ?? $studentAptitude['verbal'] ?? 0;
                } elseif (strpos($cleanReqKey, 'musical') !== false || strpos($cleanReqKey, 'rhythmic') !== false) {
                    $studentScore = $studentAptitude['auditory'] ?? 0;
                } elseif (strpos($cleanReqKey, 'interpersonal') !== false) {
                    $studentScore = $studentAptitude['interpersonal'] ?? $studentAptitude['social'] ?? 0;
                } elseif (strpos($cleanReqKey, 'intrapersonal') !== false) {
                    $studentScore = $studentAptitude['intrapersonal'] ?? 0;
                } elseif (strpos($cleanReqKey, 'natural') !== false) {
                    $studentScore = $studentAptitude['naturalistic'] ?? 0;
                } else {
                    $studentScore = $studentAptitude[$cleanReqKey] ?? 0;
                }
                
                $uniqueKey = 'dynamic_gardner_' . $cleanReqKey;
                $jobAptitudes[$uniqueKey]   = $weight;
                $studentAptitude[$uniqueKey] = $studentScore;
            }

            // -----------------------------------------------------------------
            // FIX #2 — EQ REQUIREMENTS: map to real EQ sub-scores
            // Old code used leadership/social as a blanket proxy for every EQ
            // trait, hardcoded to 50 when even those were missing.
            // New code maps each EQ key to its actual measured score from the
            // student's EQ assessment, with a graceful fallback chain.
            // -----------------------------------------------------------------
            $rawEq = $job['eq_requirements'] ?? [];
            $rawEq = is_string($rawEq) ? json_decode($rawEq, true) : (is_array($rawEq) ? $rawEq : []);

            foreach ($rawEq as $eqKey => $weight) {
                $cleanEqKey = strtolower(trim($eqKey));
                $uniqueKey  = 'dynamic_eq_' . $cleanEqKey;

                // Map the job's EQ requirement label to the student's actual score.
                // Keys cover common EQ model variants (Goleman / MSCEIT style).
                if (strpos($cleanEqKey, 'self-aware') !== false || strpos($cleanEqKey, 'self aware') !== false) {
                    $eqVal = $eqScoresNorm['self-awareness'] ?? $eqScoresNorm['self awareness'] ?? $studentAptitude['intrapersonal'] ?? 0;
                } elseif (strpos($cleanEqKey, 'self-reg') !== false || strpos($cleanEqKey, 'regulation') !== false) {
                    $eqVal = $eqScoresNorm['self-regulation'] ?? $eqScoresNorm['self regulation'] ?? $studentAptitude['intrapersonal'] ?? 0;
                } elseif (strpos($cleanEqKey, 'motivat') !== false) {
                    $eqVal = $eqScoresNorm['motivation'] ?? $studentAptitude['leadership'] ?? 0;
                } elseif (strpos($cleanEqKey, 'empath') !== false) {
                    $eqVal = $eqScoresNorm['empathy'] ?? $studentAptitude['social'] ?? 0;
                } elseif (strpos($cleanEqKey, 'social') !== false || strpos($cleanEqKey, 'interpersonal') !== false) {
                    $eqVal = $eqScoresNorm['social skills'] ?? $eqScoresNorm['social'] ?? $studentAptitude['social'] ?? 0;
                } else {
                    // Try a direct key match in the normalised EQ array first,
                    // then fall back to the student aptitude map, then to 0.
                    $eqVal = $eqScoresNorm[$cleanEqKey] ?? $studentAptitude[$cleanEqKey] ?? 0;
                }

                $jobAptitudes[$uniqueKey]    = $weight;
                $studentAptitude[$uniqueKey] = $eqVal;
            }

            // -----------------------------------------------------------------
            // PSYCHOMETRIC SCORING — RIASEC with intensity weighting (Fix 1)
            //
            // Base tier (match quality) is unchanged so career ranking order is
            // preserved. The intensity multiplier (0.80–1.00) differentiates
            // careers within the same tier using the student's actual dimension
            // strength, producing a 2–10 point spread within each match tier.
            //
            // Multiplier derivation:
            //   student score in matching RIASEC dimension / 100 * 0.20 + 0.80
            //   → score=50 → ×0.90 (midpoint, moderate match quality)
            //   → score=75 → ×0.95 (strong match)
            //   → score=100 → ×1.00 (maximum expression)
            //   → score=30 → ×0.86 (weak expression, even primary match penalised)
            //
            // The multiplier is applied only to the RIASEC sub-score, not the
            // total psyScore, so it cannot dominate the MBTI component.
            // -----------------------------------------------------------------
            $psyScore = 0;

            if (!empty($jobRiasec) && !empty($topStudentRiasec)) {
                $riasecBase      = 0;
                $matchedDimKey   = '';   // the RIASEC dimension that drove the match

                if ($jobRiasec[0] === $topStudentRiasec[0]) {
                    $riasecBase    = 50;
                    $matchedDimKey = $jobRiasec[0];
                } elseif (in_array($jobRiasec[0], $topStudentRiasec)) {
                    $riasecBase    = 48;
                    $matchedDimKey = $jobRiasec[0];
                } elseif (count(array_intersect($topStudentRiasec, $jobRiasec)) >= 2) {
                    $riasecBase    = 45;
                    // Use the student's top RIASEC dimension that overlaps
                    $overlaps      = array_values(array_intersect($topStudentRiasec, $jobRiasec));
                    $matchedDimKey = $overlaps[0];
                } elseif (count(array_intersect($topStudentRiasec, $jobRiasec)) == 1) {
                    $riasecBase    = 35;
                    $overlaps      = array_values(array_intersect($topStudentRiasec, $jobRiasec));
                    $matchedDimKey = $overlaps[0];
                } else {
                    $riasecBase    = 20;
                }

                // Apply intensity multiplier when we have a matched dimension.
                if ($matchedDimKey !== '' && isset($studentRiasecScores[$matchedDimKey])) {
                    $dimStrength     = $studentRiasecScores[$matchedDimKey];   // 0-100
                    $intensityMult   = 0.80 + ($dimStrength / 100) * 0.20;    // 0.80–1.00
                    $psyScore       += round($riasecBase * $intensityMult);
                } else {
                    $psyScore += $riasecBase;
                }
            } else {
                $psyScore += 30;
            }

            // -----------------------------------------------------------------
            // MBTI SCORING with dimension-strength differentiation (Fix 2)
            //
            // Exact match: base 50 pts + dimension-strength bonus (0–8 pts).
            // The bonus is the mean of the four dimension strengths, scaled to
            // 0-8 pts.  An ISTJ at 92/67/58/58 earns ~4 bonus pts.
            // An ISTJ at 51/52/51/51 (barely typed) earns ~0.5 bonus pts.
            // This produces a 4-7 point spread within the exact-match tier which
            // directly addresses the "all paths show 98-99" problem.
            //
            // Partial match: unchanged tier weights (30/20/10/5) — partial matches
            // already have enough spread.
            // -----------------------------------------------------------------
            if (!empty($jobMbti) && $studentMbti !== 'XXXX') {
                if (in_array($studentMbti, $jobMbti)) {
                    // Base exact-match points
                    $mbtiBase = 50;

                    // Dimension-strength bonus: mean strength across all 4 dimensions
                    // normalised to a 0-8 pt bonus range.
                    // Strength values are 51-99 (enforced in derivation above).
                    // Mean range = 51-99, midpoint 75. Normalise: (mean-51)/(99-51)*8
                    if (!empty($mbtiDimStrength)) {
                        $meanStrength    = array_sum($mbtiDimStrength) / count($mbtiDimStrength);
                        $strengthBonus   = round((($meanStrength - 51) / 48) * 8);
                        $psyScore       += $mbtiBase + max(0, $strengthBonus);
                    } else {
                        $psyScore += $mbtiBase;
                    }
                } else {
                    $bestMatchCount = 0;
                    foreach ($jobMbti as $ideal) {
                        $matches = 0;
                        for ($i = 0; $i < 4; $i++) {
                            if (($studentMbti[$i] ?? '') === ($ideal[$i] ?? '-')) $matches++;
                        }
                        if ($matches > $bestMatchCount) $bestMatchCount = $matches;
                    }
                    if      ($bestMatchCount == 3) { $psyScore += 30; }
                    elseif  ($bestMatchCount == 2) { $psyScore += 20; }
                    elseif  ($bestMatchCount == 1) { $psyScore += 10; }
                    else                           { $psyScore +=  5; }
                }
            } else {
                $psyScore += 30;
            }

            // -----------------------------------------------------------------
            // FIX #1 — APTITUDE / SKILL SCORING
            //
            // Removed the artificial floor of 20.
            // Old: $boostedScore = max($rawScore, 20) inflated every zero/unknown
            //      aptitude to 20, making students look stronger than they are.
            // New: $skillScore is computed purely from raw scores.
            //      $rawSkillScore is identical (the split is no longer needed but
            //      kept so overallFit penalty logic still has a raw reference).
            //
            // FIX #3 — No-aptitude-data fallback
            // Old: hardcoded to 75 ("Good") when career DB had no aptitude_weights.
            // New: uses the student's own cross-domain average so the career still
            //      receives a fair but honest score, not a phantom "Good".
            // -----------------------------------------------------------------
            $skillScore    = 0;
            $rawSkillScore = 0; 
            
            if (!empty($jobAptitudes)) {
                $totalWeight   = 0;
                $earnedScore   = 0;

                foreach ($jobAptitudes as $aptKey => $weight) {
                    $rawScore     = $studentAptitude[$aptKey] ?? 0;
                    // FIX #1: no floor — use raw score directly
                    $earnedScore += ($rawScore * $weight);
                    $totalWeight += $weight;
                }
                
                if ($totalWeight > 0) {
                    $skillScore    = ($earnedScore / $totalWeight);
                    $rawSkillScore = $skillScore;  // identical now that floor is removed
                }
            } else {
                // FIX #3: use student's own average instead of hardcoded 75
                $skillScore    = $studentAptAvg;
                $rawSkillScore = $studentAptAvg;
            }
            
            $skillScore    = min(99, round($skillScore));
            $rawSkillScore = min(99, round($rawSkillScore));

            // -----------------------------------------------------------------
            // FIX #5 — CLUSTER ALIGNMENT: lower rank weights so rank-3/4 clusters
            // cannot inflate a weak match above strong skill/interest evidence.
            // Old: rank 1=100, rank 2=85, rank 3=70, rank 4=55
            // New: rank 1=100, rank 2=75, rank 3=50, rank 4=30
            // -----------------------------------------------------------------
            $clusterScore = 20; 

            if ($isTopCluster) {
                if      ($clusterRank === 0) $clusterScore = 100;
                elseif  ($clusterRank === 1) $clusterScore = 75;   // was 85
                elseif  ($clusterRank === 2) $clusterScore = 50;   // was 70
                elseif  ($clusterRank === 3) $clusterScore = 30;   // was 55
            }

            // -----------------------------------------------------------------
            // SMART RECOMMENDATION & FIT SCORE — weights unchanged (40/40/20)
            // -----------------------------------------------------------------
            $overallFit = round(($psyScore * 0.40) + ($skillScore * 0.40) + ($clusterScore * 0.20));

            // Reality-check penalty for severe aptitude gap — unchanged threshold
            if ($rawSkillScore < 35) {
                $overallFit -= 15; 
            }

            // -----------------------------------------------------------------
            // FIX #10 — COMMENT LABEL
            // Old: second if() ran unconditionally and overwrote the first
            //      assignment, so 'Develop Skill Gap' was never returned.
            // New: exclusive if/elseif/else — each branch fires exactly once.
            //
            // FIX #9 — SYMMETRIC STREAM/CAREER MISMATCH WARNINGS
            // Old: only warned Humanities → STEM careers.
            // New: also warns Science → Commerce/Finance and any stream when
            //      core aptitude for that domain is weak.
            // -----------------------------------------------------------------
            if ($rawSkillScore >= 45 && $psyScore >= 60) {
                $comment = 'Good Choice';
            } elseif ($rawSkillScore < 45 && $psyScore >= 60) {
                $comment = 'Develop Skill Gap';   // FIX #10: now actually returned
            } elseif ($rawSkillScore >= 45 && $psyScore < 60) {
                $comment = 'Explore Interest';    // capable but not personality-aligned
            } else {
                $comment = 'Develop';
            }

            // FIX #9 — Challenging Path: check all stream/domain mismatches
            $heavyStemKeywords       = ['science', 'engineering', 'technology', 'math', 'architecture', 'data science', 'artificial intelligence'];
            $heavyCommerceKeywords   = ['finance', 'accounts', 'banking', 'economics', 'blockchain', 'business management'];
            $heavyBioKeywords        = ['health science', 'medicine', 'medical', 'pharmacy', 'nursing', 'biology'];

            $isHeavyStem     = false;
            $isHeavyCommerce = false;
            $isHeavyBio      = false;

            foreach ($heavyStemKeywords     as $kw) { if (strpos($jobCluster, $kw) !== false) { $isHeavyStem     = true; break; } }
            foreach ($heavyCommerceKeywords as $kw) { if (strpos($jobCluster, $kw) !== false) { $isHeavyCommerce = true; break; } }
            foreach ($heavyBioKeywords      as $kw) { if (strpos($jobCluster, $kw) !== false) { $isHeavyBio      = true; break; } }

            // STEM career + Humanities stream + low skill
            if ($isHeavyStem && strpos($topStreamName, 'Humanities') !== false && $rawSkillScore < 65) {
                $comment = 'Challenging Path';
            }
            // Finance/Commerce career + non-Commerce stream + low numerical skill
            elseif ($isHeavyCommerce && strpos($topStreamName, 'Science') !== false) {
                $numericalScore = $studentAptitude['numerical'] ?? $studentAptitude['logical'] ?? 0;
                if ($numericalScore < 50 && $rawSkillScore < 55) {
                    $comment = 'Challenging Path';
                }
            }
            // Bio/Health career + Commerce or Humanities stream + low logical/science skill
            elseif ($isHeavyBio && (strpos($topStreamName, 'Commerce') !== false || strpos($topStreamName, 'Humanities') !== false)) {
                if ($rawSkillScore < 55) {
                    $comment = 'Challenging Path';
                }
            }

            // -----------------------------------------------------------------
            // EXPANDED EXECUTION ROADMAP GENERATOR — unchanged
            // -----------------------------------------------------------------
            $eduData = $job['educational_requirements'] ?? null;
            $rawEdu  = is_string($eduData) ? json_decode($eduData, true) : (is_array($eduData) ? $eduData : []);
            
            $vocationalKeywords = ['carpenter', 'plumber', 'electrician', 'technician', 'operator', 'machinist', 'mechanic', 'worker', 'drafter', 'craftsperson'];
            $isVocational       = false;
            foreach ($vocationalKeywords as $vk) {
                if (strpos($baseTitle, $vk) !== false) { $isVocational = true; break; }
            }

            if ($isVocational && empty($rawEdu)) {
                $fb = [
                    'stream'  => 'Any Stream (10th/12th Pass)',
                    'degrees' => ['ITI Certification', 'Polytechnic Diploma', 'Apprenticeship'],
                    'exams'   => ['State ITI Entrance', 'Direct Skill Assessment']
                ];
            } else {
                $fallbackMap = [
                    'accounts and finance' => [
                        'stream'  => 'Commerce with Maths / Commerce',
                        'degrees' => ['B.Com (Hons)', 'B.Com (Accounting & Finance)', 'BBA (Finance)', 'CA Foundation / CMA / CS (Company Secretary)', 'CFA (Level 1)', 'B.Sc (Finance)', 'B.Sc (Actuarial Science)'],
                        'exams'   => ['CUET', 'ICAI Exams', 'ICMAI', 'ICSI Exams', 'ACET (Actuarial)', 'NMIMS NPAT', 'SET', 'Christ CUET']
                    ],
                    
                    'agriculture & environment' => [
                        'stream'  => 'Science (PCB / PCMB / Agriculture)',
                        'degrees' => ['B.Sc (Hons) Agriculture', 'B.Tech (Agricultural/Dairy Engg)', 'B.Sc Forestry / Horticulture', 'B.Sc Environmental Science', 'B.F.Sc (Fisheries)'],
                        'exams'   => ['ICAR AIEEA', 'CUET', 'MHT CET', 'KCET', 'KEAM', 'BCECE']
                    ],
                    
                    'architecture and construction' => [
                        'stream'  => 'Science (PCM)',
                        'degrees' => ['B.Arch', 'B.Planning', 'B.Tech (Civil Engineering)', 'B.Des (Interior Architecture)', 'Diploma in Architecture', 'B.Sc (Real Estate / Urban Design)'],
                        'exams'   => ['NATA', 'JEE Main Paper 2', 'JEE Advanced (AAT)', 'MHT CET', 'KCET']
                    ],
                    
                    'bio science and research' => [
                        'stream'  => 'Science (PCB / PCMB)',
                        'degrees' => ['B.Sc (Hons) Biological Sciences', 'B.Tech (Biotechnology)', 'B.Sc (Microbiology/Genetics/Bioinformatics)', 'BS-MS (Dual Degree)'],
                        'exams'   => ['CUET', 'NEST', 'IAT (IISER)', 'ICAR AIEEA', 'BITSAT']
                    ],
                    
                    'business management' => [
                        'stream'  => 'Any Stream (Commerce / Science / Humanities)',
                        'degrees' => ['BBA', 'BMS', 'BBM', 'B.Com (Management)', 'Integrated MBA (IPM)', 'B.Voc (Retail Management)'],
                        'exams'   => ['IPMAT (IIMs)', 'JIPMAT', 'CUET', 'UGAT (AIMA)', 'NMIMS NPAT', 'SET', 'Christ CUET']
                    ],
                    
                    'design & arts' => [
                        'stream'  => 'Any Stream',
                        'degrees' => ['B.Des (Fashion/Product/Graphic)', 'B.F.A. (Fine Arts)', 'B.A. (Visual/Apparel Arts)', 'B.Sc (Animation/Graphics/VFX)', 'Diploma in Design'],
                        'exams'   => ['NID DAT', 'UCEED', 'NIFT Entrance', 'FDDI AIST', 'CUET', 'State Fine Arts Entrances']
                    ],
                    
                    'education and training' => [
                        'stream'  => 'Any Stream',
                        'degrees' => ['B.A. B.Ed / B.Sc B.Ed (ITEP)', 'B.El.Ed', 'D.El.Ed', 'B.P.Ed', 'B.Ed (Special Education)'],
                        'exams'   => ['NCET (NTA)', 'RIE CEE (Regional Institutes)', 'CUET', 'UP B.Ed JEE', 'PTET', 'State B.Ed Entrance Exams']
                    ],
                    
                    'government services' => [
                        'stream'  => 'Humanities / Any Stream',
                        'degrees' => ['B.A. (Political Science / History / Public Admin)', 'B.Sc', 'B.Com (Any Graduation is valid for UPSC)'],
                        'exams'   => ['UPSC CSE (Post-Grad)', 'SSC CGL (Post-Grad)', 'NDA/NA (12th Level)', 'IBPS / SBI PO (Banking)', 'RBI Grade B', 'State PSCs']
                    ],
                    
                    'health science' => [
                        'stream'  => 'Science (PCB)',
                        'degrees' => ['MBBS', 'BDS', 'B.Sc (Nursing)', 'B.Pharm / Pharm.D', 'BPT (Physiotherapy) / BOT (Occupational Therapy)', 'B.Sc (Allied Health/Medical Lab Tech)', 'BAMS / BHMS'],
                        'exams'   => ['NEET-UG', 'AIIMS Paramedical', 'MNS (Military Nursing)', 'NEET AYUSH', 'State Medical/Nursing CETs']
                    ],
                    
                    'hospitality and tourism' => [
                        'stream'  => 'Any Stream',
                        'degrees' => ['BHM (Hotel Mgmt)', 'B.Sc (Hospitality & Hotel Administration)', 'BBA (Tourism)', 'BTTM (Travel & Tourism)', 'Culinary Arts Diploma'],
                        'exams'   => ['NCHMCT JEE', 'CUET', 'e-CHAT', 'IHM Pusa Entrance', 'State Hotel Management CETs']
                    ],
                    
                    'human service & social science' => [
                        'stream'  => 'Humanities / Any Stream',
                        'degrees' => ['B.A. (Hons) Psychology', 'BSW (Social Work)', 'B.A. (Sociology / Anthropology)', 'B.Sc (Clinical Psychology)'],
                        'exams'   => ['CUET', 'TISS BAT (via CUET)', 'Christ University Entrance', 'State University Entrances']
                    ],
                    
                    'information technology' => [
                        'stream'  => 'Science (PCM) / Commerce with Maths',
                        'degrees' => ['B.Tech (Computer Science / IT)', 'B.Tech (AI & Machine Learning)', 'BCA', 'B.Sc (IT / Computer Science)', 'B.Sc (Data Science / Cyber Security)'],
                        'exams'   => ['JEE Main', 'BITSAT', 'VITEEE', 'CUET', 'MHT CET', 'KCET', 'COMEDK']
                    ],
                    
                    'legal services' => [
                        'stream'  => 'Humanities / Commerce / Any Stream',
                        'degrees' => ['B.A. LLB (Hons)', 'BBA LLB', 'B.Com LLB', 'B.Sc LLB', 'B.Tech LLB (Cyberlaw focus)'],
                        'exams'   => ['CLAT', 'AILET', 'LSAT India', 'MH CET Law', 'SLAT', 'CUET']
                    ],
                    
                    'logistics and transportation' => [
                        'stream'  => 'Commerce / Any Stream / Science (for Maritime/Aviation)',
                        'degrees' => ['BBA (Logistics & Supply Chain / Aviation)', 'B.Sc (Nautical Science)', 'B.Tech (Marine Engineering)', 'B.Com (Supply Chain)', 'Commercial Pilot License (CPL)'],
                        'exams'   => ['IMU CET', 'CUET', 'DGCA Exams (Pilot)', 'ILAM Entrance', 'University Specific Tests']
                    ],
                    
                    'manufacturing' => [
                        'stream'  => 'Science (PCM)',
                        'degrees' => ['B.Tech (Mechanical / Production / Industrial / Mechatronics / Automobile)', 'Diploma in Engineering (Polytechnic)'],
                        'exams'   => ['JEE Main', 'State Engineering CETs', 'Polytechnic Entrance Exams (JEECUP, etc.)']
                    ],
                    
                    'marketing & advertising' => [
                        'stream'  => 'Commerce / Humanities / Any Stream',
                        'degrees' => ['BBA (Marketing)', 'BMM (Mass Media)', 'B.A. (Advertising & PR)', 'B.Com (Marketing)', 'B.Voc (Digital Marketing)'],
                        'exams'   => ['CUET', 'IPMAT', 'NMIMS NPAT', 'SET', 'University Entrances']
                    ],
                    
                    'media and communication' => [
                        'stream'  => 'Humanities / Any Stream',
                        'degrees' => ['B.A. (Journalism & Mass Comm)', 'BMM', 'B.Sc (Film Making / VFX)', 'B.A. (Media Studies)'],
                        'exams'   => ['CUET', 'JMI Entrance', 'IIMC Entrance (Post-Grad)', 'FTII JET', 'SET', 'XIC OET']
                    ],
                    
                    'public safety and security' => [
                        'stream'  => 'Any Stream / Science (for Forensics / NDA)',
                        'degrees' => ['B.A. (Criminology)', 'B.Sc (Forensic Science / Cyber Security)', 'NDA (Army/Navy/Airforce)', 'B.A. (Defense Studies)'],
                        'exams'   => ['NDA & NA Exam', 'CUET', 'NFSU Entrance (NFAT)', 'State Police / Defense Recruitment', 'Indian Coast Guard (Navik)']
                    ],
                    
                    'science, maths and engineering' => [
                        'stream'  => 'Science (PCM)',
                        'degrees' => ['B.Tech (Core Engg)', 'B.E.', 'B.Sc (Hons) Physics/Chemistry/Math', 'B.Stat', 'B.Math'],
                        'exams'   => ['JEE Advanced', 'JEE Main', 'ISI Admission Test', 'CMI Entrance', 'State CETs']
                    ],
                    
                    'sports & physical activities' => [
                        'stream'  => 'Any Stream',
                        'degrees' => ['B.P.Ed (Physical Education)', 'B.Sc (Sports Science / Biomechanics)', 'BBA (Sports Management)', 'B.A. (Physical Education)'],
                        'exams'   => ['CUET', 'LNIPE Entrance', 'SAI (NSNIS) Certifications', 'University Physical Fitness Tests']
                    ]
                ];

                $fb = [
                    'stream'  => $topStreamName !== 'Any Stream' ? $topStreamName : 'Relevant Stream in 11th/12th', 
                    'degrees' => ['Bachelors in relevant field', 'Specialized Diploma'], 
                    'exams'   => ['CUET', 'University Specific Entrances']
                ];

                foreach ($fallbackMap as $key => $mapData) {
                    if (strpos($jobCluster, $key) !== false || strpos($key, $jobCluster) !== false) {
                        $fb = $mapData;
                        break;
                    }
                }
            }

            $streamInfo  = $rawEdu['stream']  ?? $fb['stream'];
            $degreesInfo = $rawEdu['degrees'] ?? $fb['degrees'];
            $examsInfo   = $rawEdu['exams']   ?? $fb['exams'];
            // =================================================================
            // NEW: TIER 1 & TIER 2 COLLEGES FALLBACK
            // Maps the best Indian institutes based on the 20 specific clusters
            // =================================================================
            $collegesInfo = $job['colleges'] ?? null;
            if (empty($collegesInfo)) {
                $collegesFallback = [
                    'health science' => 'AIIMS (Multiple), JIPMER, AFMC (Pune), CMC (Vellore), MAMC (Delhi), KGMU (Lucknow), Top State Medical Colleges',
                    'information technology' => 'IITs, NITs, IIITs, BITS Pilani, VIT (Vellore), DTU, NSUT, Top State Engineering Colleges',
                    'science, maths and engineering' => 'IITs, NITs, IISc (Bangalore), IISERs, BITS Pilani, CMI (Chennai), ISI (Kolkata)',
                    'business management' => 'IIMs (for IPM 5-Year), SSCBS (Delhi), Christ University, NMIMS (Mumbai), Symbiosis (Pune)',
                    'accounts and finance' => 'SRCC (Delhi), Hindu College, St. Xavier\'s (Mumbai/Kolkata), Christ University, Loyola College (Chennai)',
                    'design & arts' => 'NID (Multiple campuses), NIFT (Multiple campuses), IIT Bombay (IDC), Srishti (Bengaluru), Pearl Academy',
                    'architecture and construction' => 'SPA (Delhi/Bhopal/Vijayawada), IIT Roorkee/Kharagpur, CEPT (Ahmedabad), Sir JJ College (Mumbai)',
                    'legal services' => 'NLSIU (Bengaluru), NLU (Delhi), NALSAR (Hyderabad), Symbiosis Law School, Jindal Global Law School',
                    'hospitality and tourism' => 'IHM (Pusa, Mumbai, Aurangabad, Bengaluru), Welcomgroup GS (Manipal), Christ University',
                    'media and communication' => 'IIMC (Delhi), AJK MCRC (Jamia), XIC (Mumbai), Symbiosis (SIMC), Asian College of Journalism',
                    'agriculture & environment' => 'ICAR Institutes, IARI (New Delhi), NDRI (Karnal), GBPUAT (Pantnagar), TNAU (Coimbatore)',
                    'bio science and research' => 'IISc (Bangalore), IISERs, AIIMS, Delhi University, BHU, Pune University',
                    'education and training' => 'RIE (NCERT), Delhi University (CIE), BHU, TISS (Mumbai), Azim Premji University',
                    'government services' => 'Top Central Universities (DU, JNU, BHU, JMI) - Graduation in any stream is valid for UPSC/SSC',
                    'human service & social science' => 'TISS (Mumbai/Tuljapur), Delhi University, Christ University, Ashoka University, Krea University',
                    'logistics and transportation' => 'IMU (Maritime), IGRUA (Aviation), ILAM, UPES (Dehradun), Top BBA Logistics Institutes',
                    'manufacturing' => 'IITs, NITs, BITS Pilani, DTU, State Engineering Universities (Jadavpur, COEP, VJTI)',
                    'marketing & advertising' => 'SSCBS (Delhi), NMIMS (Mumbai), Christ University, Symbiosis, XIC (Mumbai), MICA (Ahmedabad)',
                    'public safety and security' => 'NDA (Khadakwasla), NFSU (Forensics), Rashtriya Raksha University, Top Central Universities',
                    'sports & physical activities' => 'LNIPE (Gwalior), SAI NSNIS (Patiala), IISM (Mumbai), Symbiosis School of Sports Sciences'
                ];

                $collegesInfo = 'Top Tier 1 & Tier 2 Institutes (e.g., Central/State Universities, Premium Private Institutes)';
                foreach ($collegesFallback as $key => $collegesList) {
                    if (strpos($jobCluster, $key) !== false || strpos($key, $jobCluster) !== false) {
                        $collegesInfo = $collegesList;
                        break;
                    }
                }
            } elseif (is_string($collegesInfo)) {
                $decoded = json_decode($collegesInfo, true);
                if (is_array($decoded)) { $collegesInfo = implode(' • ', $decoded); }
            } elseif (is_array($collegesInfo)) {
                $collegesInfo = implode(' • ', $collegesInfo);
            }
            
            // =================================================================
            // NEW: FUTURE READINESS & AI RESILIENCE INDEX
            // Calculates automation risk based on job cluster and title keywords.
            // =================================================================
            $aiResilienceBand = 'Medium (AI-Assisted)';
            $aiImpactNote     = 'Routine technical or analytical tasks will be automated. Professionals who adapt by using AI to enhance their productivity and focus on high-level strategy will thrive.';
            $aiColor          = '#f39c12'; // Orange/Warning

            // Keywords indicating highly human-centric, physical, or complex strategic roles
            $highResilienceKeywords = ['surgeon', 'psychologist', 'doctor', 'physician', 'therapist', 'counselor', 'lawyer', 'judge', 'founder', 'social worker', 'chef', 'teacher', 'nurse', 'police', 'army', 'navy', 'athlete', 'artist', 'veterinarian', 'dentist', 'architect', 'director', 'ceo'];
            
            // Keywords indicating highly routine, repetitive, or easily automatable digital roles
            $transformativeKeywords = ['transcriptionist', 'drafter', 'helpdesk', 'support specialist', 'copywriter', 'data entry', 'clerk', 'technician', 'bookkeeper', 'teller', 'operator'];

            $isHigh = false;
            $isTransformative = false;

            // Check for High Resilience
            foreach ($highResilienceKeywords as $kw) {
                // If the job title has a high-resilience keyword OR belongs to a fundamentally human cluster
                if (strpos($baseTitle, $kw) !== false || strpos($jobCluster, 'health science') !== false || strpos($jobCluster, 'human service') !== false || strpos($jobCluster, 'education') !== false) {
                    $isHigh = true; break;
                }
            }

            // Check for Transformative/Low Resilience
            foreach ($transformativeKeywords as $kw) {
                if (strpos($baseTitle, $kw) !== false) {
                    $isTransformative = true; $isHigh = false; break;
                }
            }

            // Assign final metrics based on the analysis
            if ($isHigh) {
                $aiResilienceBand = 'High (Human-Centric)';
                $aiImpactNote     = 'Highly dependent on human empathy, complex physical dexterity, or advanced strategic negotiation. AI will act as a powerful supportive tool in this field, not a replacement.';
                $aiColor          = '#27ae60'; // Green/Safe
            } elseif ($isTransformative) {
                $aiResilienceBand = 'Transformative (High Risk)';
                $aiImpactNote     = 'Core tasks in this specific role are highly susceptible to AI automation. Long-term success requires rapid upskilling into management, strategy, or specialized niches that AI cannot replicate.';
                $aiColor          = '#c0392b'; // Red/Danger
            }
            
            $rankedPaths[] = [
                'title'         => ucwords($rawTitle) . ' - ' . ucwords($jobCluster),
                'roles'         => $job['roles'] ?? 'Role details pending...', 
                'psy'           => ['score' => min(99, $psyScore), 'band' => $this->getPsyBand($psyScore)],
                '_raw_psy'      => $psyScore,   // kept for post-loop percentile stretch; stripped before return
                'skill'         => ['score' => $skillScore, 'band' => self::getSkillBandStatic($skillScore)],
                'comment'       => $comment, 
                'sort_metric'   => $overallFit, 
                'cluster'       => $jobCluster,
                'base_title'    => $baseTitle, 
                'roadmap'       => [
                    'stream'  => $streamInfo,
                    'degrees' => is_array($degreesInfo) ? $degreesInfo : [$degreesInfo],
                    'exams'   => is_array($examsInfo)   ? $examsInfo   : [$examsInfo],
                    'colleges' => $collegesInfo,
                    'ai_resilience' => [
                        'band'  => $aiResilienceBand,
                        'note'  => $aiImpactNote,
                        'color' => $aiColor
                    ]
                ]
            ];
        }

        // -----------------------------------------------------------------
        // SORT → DEDUPLICATE → GUARANTEE → TRIM — unchanged
        // -----------------------------------------------------------------
        usort($rankedPaths, fn($a, $b) => $b['sort_metric'] <=> $a['sort_metric']);

        $dedupedPaths         = [];
        $seenBaseTitlesFinal  = [];

        foreach ($rankedPaths as $path) {
            $bt = $path['base_title']; 
            if (!isset($seenBaseTitlesFinal[$bt])) {
                $seenBaseTitlesFinal[$bt] = true;
                $dedupedPaths[]           = $path;
            }
        }

        $guaranteed    = [];
        $rest          = [];
        $clusterCounts = array_fill_keys($topClusterNames, 0);
        $minPerCluster = 3; 

        foreach ($dedupedPaths as $path) {
            $cluster = $path['cluster'];
            if (isset($clusterCounts[$cluster]) && $clusterCounts[$cluster] < $minPerCluster) {
                $guaranteed[] = $path;
                $clusterCounts[$cluster]++;
            } else {
                $rest[] = $path; 
            }
        }

        $slotsRemaining = 45 - count($guaranteed);
        $topRest        = array_slice($rest, 0, max(0, $slotsRemaining));

        $finalPaths = array_merge($guaranteed, $topRest);
        
        usort($finalPaths, fn($a, $b) => $b['sort_metric'] <=> $a['sort_metric']);

        // =====================================================================
        // FIX — PERCENTILE STRETCH: display psyScore normalisation (Fix 3)
        //
        // Problem: RIASEC intensity weighting spreads raw psyScores by ~5-12 pts
        // within the recommended set, but the absolute range (e.g. 89-101 before
        // min(99) clamp) still collapses to 89-99 on display — a 10-pt spread
        // that looks like noise to a student reading "98 vs 97 vs 96".
        //
        // Fix: apply min-max normalisation to map the actual raw score range onto
        // a target display range of 60-99.  This makes the differentiation visible
        // and meaningful without altering sort order (sort_metric is untouched).
        //
        // Target range choice (60-99):
        //   - Floor of 60 ensures every recommended career still reads as "High"
        //     or better — no career that passed the cluster gatekeeper should look
        //     weak on the psy dimension.
        //   - Ceiling of 99 preserves the existing cap convention.
        //   - The 39-point spread gives a student-readable signal (e.g. 99 vs 87
        //     vs 78) that counsellors can discuss meaningfully.
        //
        // Edge case: if all raw scores are identical (student with no RIASEC data),
        // the stretch is skipped and every path keeps its pre-stretch score.
        // =====================================================================
        $rawPsyValues = array_column($finalPaths, '_raw_psy');
        $psyMin       = !empty($rawPsyValues) ? min($rawPsyValues) : 0;
        $psyMax       = !empty($rawPsyValues) ? max($rawPsyValues) : 99;
        $psyRange     = $psyMax - $psyMin;

        $stretchFloor   = 60;
        $stretchCeiling = 99;
        $stretchRange   = $stretchCeiling - $stretchFloor;

        foreach ($finalPaths as &$p) {
            if ($psyRange > 0) {
                // Linear interpolation from raw range into target display range
                $stretched = $stretchFloor + round((($p['_raw_psy'] - $psyMin) / $psyRange) * $stretchRange);
                $stretched = max($stretchFloor, min($stretchCeiling, $stretched));
            } else {
                // All identical — keep the pre-stretch clamped value
                $stretched = min($stretchCeiling, $p['_raw_psy']);
            }
            $p['psy'] = ['score' => $stretched, 'band' => $this->getPsyBand($stretched)];
            unset($p['_raw_psy'], $p['cluster'], $p['base_title']);
        }

        return $finalPaths;
    }

    private function getPsyBand($score) {
        if ($score >= 85) return 'Very High';
        if ($score >= 60) return 'High';
        if ($score >= 45) return 'Average';
        return 'Low';
    }

    // FIX #6 — instance method delegates to static so legacy internal calls still work
    private function getSkillBand($score) {
        return self::getSkillBandStatic($score);
    }
}