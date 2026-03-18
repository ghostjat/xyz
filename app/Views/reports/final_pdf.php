<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Pharos Education Career Analysis Report</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; font-size: 10pt; line-height: 1.5; margin: 0; padding: 0; }
        h1, h2, h3, h4 { margin-top: 0; color: #2c3e50; font-weight: bold; }
        .header-strip { background: #f4f6f9; border-left: 5px solid #2980b9; padding: 12px 20px; font-weight: bold; text-transform: uppercase; margin-bottom: 20px; font-size: 11pt; color: #2c3e50; }
        .editorial-box { background-color: #f8f9fa; border: 1px solid #e9ecef; padding: 15px 20px; margin-bottom: 15px; border-radius: 4px; }
        table.data-table { width: 100%; border-collapse: collapse; font-size: 9pt; margin-top: 10px; }
        table.data-table th { background: #2c3e50; color: white; padding: 10px 8px; text-align: left; text-transform: uppercase; }
        table.data-table td { border-bottom: 1px solid #ecf0f1; padding: 10px 8px; vertical-align: top; }
        .cover-page { background: #111820; color: white; padding: 0;}
        .cover-accent { height: 12px; width: 100%; background: #d35400; }
        .pagebreak { page-break-before: always; }
    </style>
</head>
<body>

<?php
// =========================================================================
// GD IMAGE GENERATORS
// =========================================================================

if (!function_exists('generateMBTISliderGD')) {
    function generateMBTISliderGD($leftPct, $rightPct, $mainHex, $lightHex, $isLeftDominant) {
        $w = 800; $h = 32; $r = 16;
        $img = imagecreatetruecolor($w, $h);
        imagesavealpha($img, true);
        imagefill($img, 0, 0, imagecolorallocatealpha($img, 0, 0, 0, 127));
        $white = imagecolorallocate($img, 255, 255, 255);
        $grayBorder = imagecolorallocate($img, 200, 200, 200);
        $mainHex = ltrim($mainHex, '#'); $lightHex = ltrim($lightHex, '#');
        $colorMain  = imagecolorallocate($img, hexdec(substr($mainHex,0,2)),  hexdec(substr($mainHex,2,2)),  hexdec(substr($mainHex,4,2)));
        $colorLight = imagecolorallocate($img, hexdec(substr($lightHex,0,2)), hexdec(substr($lightHex,2,2)), hexdec(substr($lightHex,4,2)));
        imagefilledellipse($img, $r, $r, $h, $h, $colorLight);
        imagefilledellipse($img, $w-$r, $r, $h, $h, $colorLight);
        imagefilledrectangle($img, $r, 0, $w-$r, $h, $colorLight);
        $thumbX = ($leftPct / 100) * $w;
        if ($thumbX < $r) $thumbX = $r;
        if ($thumbX > $w-$r) $thumbX = $w-$r;
        if ($isLeftDominant) {
            imagefilledellipse($img, $r, $r, $h, $h, $colorMain);
            imagefilledrectangle($img, $r, 0, $thumbX, $h, $colorMain);
        } else {
            imagefilledellipse($img, $w-$r, $r, $h, $h, $colorMain);
            imagefilledrectangle($img, $thumbX, 0, $w-$r, $h, $colorMain);
        }
        imagefilledellipse($img, $thumbX, $r, 38, 38, $grayBorder);
        imagefilledellipse($img, $thumbX, $r, 34, 34, $white);
        imagefilledellipse($img, $thumbX, $r, 18, 18, $colorMain);
        ob_start(); imagepng($img); $data = ob_get_clean(); imagedestroy($img);
        return 'data:image/png;base64,' . base64_encode($data);
    }
}

if (!function_exists('generateGDDonutChart')) {
    function generateGDDonutChart($score, $hexColor) {
        $size = 300; $center = $size / 2;
        $img = imagecreatetruecolor($size, $size);
        imagesavealpha($img, true);
        imagefill($img, 0, 0, imagecolorallocatealpha($img, 0, 0, 0, 127));
        $bgGray    = imagecolorallocate($img, 225, 225, 225);
        $hexColor  = ltrim($hexColor, '#');
        $colorMain = imagecolorallocate($img, hexdec(substr($hexColor,0,2)), hexdec(substr($hexColor,2,2)), hexdec(substr($hexColor,4,2)));
        $white     = imagecolorallocate($img, 255, 255, 255);
        $startAngle = 270;
        $endAngle   = $startAngle + (($score / 100) * 360);
        imagefilledarc($img, $center, $center, $size, $size, 0, 360, $bgGray, IMG_ARC_PIE);
        if ($score > 0) imagefilledarc($img, $center, $center, $size, $size, $startAngle, $endAngle, $colorMain, IMG_ARC_PIE);
        imagefilledarc($img, $center, $center, $size*0.65, $size*0.65, 0, 360, $white, IMG_ARC_PIE);
        ob_start(); imagepng($img); $data = ob_get_clean(); imagedestroy($img);
        return 'data:image/png;base64,' . base64_encode($data);
    }
}

// Place this helper function at the top of your file or in a helper class
if (!function_exists('generateGDProgressBar')) {
    function generateGDProgressBar($percentage, $hexColor, $width = 400, $height = 14) {
        // Create base image
        $img = imagecreatetruecolor($width, $height);
        
        // Convert Hex to RGB safely
        $hexColor = ltrim($hexColor, '#');
        $r = hexdec(substr($hexColor, 0, 2));
        $g = hexdec(substr($hexColor, 2, 2));
        $b = hexdec(substr($hexColor, 4, 2));
        
        // Allocate colors
        $bgColor = imagecolorallocate($img, 236, 240, 241); // #ecf0f1 (Light Gray Background)
        $fillColor = imagecolorallocate($img, $r, $g, $b);
        
        // Fill background
        imagefilledrectangle($img, 0, 0, $width, $height, $bgColor);
        
        // Calculate and draw the colored progress portion
        $fillWidth = ($percentage / 100) * $width;
        if ($fillWidth > 0) {
            // Ensure at least a 2px sliver shows if value is > 0 but very small
            $fillWidth = max(2, $fillWidth); 
            imagefilledrectangle($img, 0, 0, $fillWidth, $height, $fillColor);
        }
        
        // Buffer output to base64 URI
        ob_start();
        imagepng($img);
        $imgData = ob_get_clean();
        imagedestroy($img);
        
        return 'data:image/png;base64,' . base64_encode($imgData);
    }
}

if (!function_exists('generateGDInlineBar')) {
    function generateGDInlineBar($score, $hexColor) {
        $w = 1600; $h = 64; $r = $h / 2;
        $img = imagecreatetruecolor($w, $h);
        imagefill($img, 0, 0, imagecolorallocate($img, 253, 253, 253));
        $trackColor = imagecolorallocate($img, 238, 238, 238);
        $hexColor   = ltrim($hexColor, '#');
        $fillColor  = imagecolorallocate($img, hexdec(substr($hexColor,0,2)), hexdec(substr($hexColor,2,2)), hexdec(substr($hexColor,4,2)));
        imagefilledellipse($img, $r, $r, $h, $h, $trackColor);
        imagefilledellipse($img, $w-$r, $r, $h, $h, $trackColor);
        imagefilledrectangle($img, $r, 0, $w-$r, $h, $trackColor);
        $fillW = ($score / 100) * $w;
        if ($score > 0) {
            if ($fillW < $h) $fillW = $h;
            imagefilledellipse($img, $r, $r, $h, $h, $fillColor);
            imagefilledellipse($img, $fillW-$r, $r, $h, $h, $fillColor);
            imagefilledrectangle($img, $r, 0, $fillW-$r, $h, $fillColor);
        }
        ob_start(); imagepng($img); $data = ob_get_clean(); imagedestroy($img);
        return ['src' => 'data:image/png;base64,' . base64_encode($data), 'width' => '100%', 'height' => 16];
    }
}

// NEW FUNCTION: Creates the specific thumb-slider bar shown in your screenshot
if (!function_exists('generateGDProgressBarWithThumb')) {
    function generateGDProgressBarWithThumb($score, $hexColor) {
        $w = 400; $h = 24; $r = $h / 2;
        $img = imagecreatetruecolor($w, $h);
        // Fill background with white
        imagefill($img, 0, 0, imagecolorallocate($img, 255, 255, 255)); 

        $trackColor = imagecolorallocate($img, 224, 224, 224); // light gray track
        $hexColor   = ltrim($hexColor, '#');
        $fillColor  = imagecolorallocate($img, hexdec(substr($hexColor,0,2)), hexdec(substr($hexColor,2,2)), hexdec(substr($hexColor,4,2)));
        $white      = imagecolorallocate($img, 255, 255, 255);

        // Draw track
        imagefilledellipse($img, $r, $r, $h, $h, $trackColor);
        imagefilledellipse($img, $w-$r, $r, $h, $h, $trackColor);
        imagefilledrectangle($img, $r, 0, $w-$r, $h, $trackColor);

        // Draw fill
        $fillW = ($score / 100) * $w;
        if ($fillW < $h) $fillW = $h;
        if ($score > 0) {
            imagefilledellipse($img, $r, $r, $h, $h, $fillColor);
            if ($fillW > $r) {
                imagefilledrectangle($img, $r, 0, $fillW-$r, $h, $fillColor);
                imagefilledellipse($img, $fillW-$r, $r, $h, $h, $fillColor);
            }
        }

        // Calculate thumb position
        $thumbX = $fillW - $r;
        if ($thumbX < $r) $thumbX = $r;
        if ($thumbX > $w - $r) $thumbX = $w - $r;

        // Draw thumb (Colored outer ring with white inner circle)
        imagefilledellipse($img, $thumbX, $r, $h, $h, $fillColor);
        imagefilledellipse($img, $thumbX, $r, $h - 8, $h - 8, $white);

        ob_start(); imagepng($img); $data = ob_get_clean(); imagedestroy($img);
        return ['src' => 'data:image/png;base64,' . base64_encode($data), 'width' => '100%', 'height' => 14];
    }
}

if (!function_exists('renderRiasecRadarSVG')) {
    function renderRiasecRadarSVG($studentScores, $targetScores) {
        $size = 360; $center = $size / 2; $maxRadius = 120;
        $labels = ['Realistic','Investigative','Artistic','Social','Enterprising','Conventional'];
        $svg = '<svg width="320" height="320" viewBox="0 0 '.$size.' '.$size.'" xmlns="http://www.w3.org/2000/svg">';
        for ($level = 1; $level <= 5; $level++) {
            $rr = $maxRadius * ($level / 5); $pts = [];
            foreach ($labels as $i => $l) { $a = deg2rad($i*60); $pts[] = ($center+$rr*sin($a)).','.($center-$rr*cos($a)); }
            $svg .= '<polygon points="'.implode(' ',$pts).'" fill="none" stroke="#ecf0f1" stroke-width="1.5"/>';
        }
        foreach ($labels as $i => $l) {
            $a = deg2rad($i*60);
            $x = $center+$maxRadius*sin($a); $y = $center-$maxRadius*cos($a);
            $svg .= '<line x1="'.$center.'" y1="'.$center.'" x2="'.$x.'" y2="'.$y.'" stroke="#bdc3c7" stroke-width="1.5"/>';
            $lx = $center+($maxRadius+25)*sin($a); $ly = $center-($maxRadius+15)*cos($a);
            $anchor = ($i==1||$i==2)?'start':(($i==4||$i==5)?'end':'middle');
            $svg .= '<text x="'.$lx.'" y="'.$ly.'" text-anchor="'.$anchor.'" fill="#2c3e50" font-size="10" font-weight="bold">'.strtoupper($l).'</text>';
        }
        $gp = function($scores) use ($labels,$center,$maxRadius) {
            $pts=[];
            foreach ($labels as $i => $l) {
                $val = $scores[strtolower($l)] ?? $scores[$l] ?? 0;
                $rr  = $maxRadius*($val/100); $a = deg2rad($i*60);
                $pts[] = ($center+$rr*sin($a)).','.($center-$rr*cos($a));
            }
            return implode(' ',$pts);
        };
        $svg .= '<polygon points="'.$gp($targetScores).'" fill="#27ae60" fill-opacity="0.15" stroke="#27ae60" stroke-width="2.5" stroke-dasharray="5,5"/>';
        $svg .= '<polygon points="'.$gp($studentScores).'" fill="#2980b9" fill-opacity="0.6" stroke="#2980b9" stroke-width="2.5"/>';
        $svg .= '</svg>';
        return $svg;
    }
}

if (!function_exists('getBandColor')) {
    function getBandColor($band) {
        $b = strtolower(trim($band));
        // Matched exactly to your screenshot colors
        if (in_array($b, ['very high','high','good','excellent match'])) return '#009432'; // Green
        if (in_array($b, ['average','fair','good match','very good match'])) return '#f39c12'; // Orange/Yellow
        if (in_array($b, ['low','improve','requires skill building'])) return '#ff0000'; // Red
        return '#7f8c8d';
    }
}

if (!function_exists('getCommentColor')) {
    function getCommentColor($comment) {
        $c = strtolower(trim($comment));
        if (strpos($c, 'good') !== false) return '#009432'; // Green match for "Good Choice"
        if (strpos($c, 'develop') !== false) return '#341f97'; // Purple/Blue match for "Develop"
        if (strpos($c, "challenging path") !== false) return '#ff0000';
        return '#333333';
    }
}

// =========================================================================
// DATA SETUP
// =========================================================================
$advData  = $advData ?? [];
$mbtiScores = $advData['mbti_percentages'] ?? ($mbti['scores'] ?? ['E'=>50,'I'=>50,'S'=>50,'N'=>50,'T'=>50,'F'=>50,'P'=>50,'J'=>50]);
$mbtiPrimaryTrait = isset($mbti['trait']) && $mbti['trait'] !== 'Pending' ? $mbti['trait'] : '';

$pctE = $mbtiScores['E'] ?? 50; $pctI = 100 - $pctE;
$pctS = $mbtiScores['S'] ?? 50; $pctN = 100 - $pctS;
$pctT = $mbtiScores['T'] ?? 50; $pctF = 100 - $pctT;
$pctP = $mbtiScores['P'] ?? 50; $pctJ = 100 - $pctP;

if (empty($mbtiPrimaryTrait) || strlen($mbtiPrimaryTrait) !== 4) {
    $mbtiPrimaryTrait = ($pctE>=50?'E':'I').($pctS>=50?'S':'N').($pctT>=50?'T':'F').($pctP>=50?'P':'J');
}
$trait1 = substr($mbtiPrimaryTrait,0,1);
$trait2 = substr($mbtiPrimaryTrait,1,1);
$trait3 = substr($mbtiPrimaryTrait,2,1);
$trait4 = substr($mbtiPrimaryTrait,3,1);

if (!function_exists('expandMBTICode')) {
    function expandMBTICode($code) {
        $map = ['E'=>'Extrovert','I'=>'Introvert','S'=>'Sensing','N'=>'INtuitive','T'=>'Thinking','F'=>'Feeling','J'=>'Judging','P'=>'Perceiving'];
        $exp = [];
        for ($i=0; $i<strlen($code); $i++) { $exp[] = $map[strtoupper($code[$i])] ?? ''; }
        return implode(' + ', $exp);
    }
}

$riasec    = $advData['riasec_scores'] ?? [];
$clusters  = $advData['cluster_scores'] ?? [];
$paths     = $advData['career_paths'] ?? [];
$gardner = $advData['gardner_scores'] ?? [];
$learning = $advData['learning_styles'] ?? [];
$motivators = $advData['motivators'] ?? [];
$skills    = $advData['skills'] ?? [];
$academicRoadmap = $advData['academic_roadmap'] ?? [];
$mPalette  = ['#d35400','#2980b9','#27ae60','#8e44ad','#f39c12','#c0392b','#16a085','#34495e'];

$allTraits = ['E'=>$pctE,'I'=>$pctI,'S'=>$pctS,'N'=>$pctN,'T'=>$pctT,'F'=>$pctF,'P'=>$pctP,'J'=>$pctJ];
arsort($allTraits);
$topStrengthLetter = array_key_first($allTraits);

$strengthsMap = [
    'E'=>['Rational and expressive','Highly sociable and communicative','Full of life and energy','Broad network builder','Quick to adapt and take action'],
    'I'=>['Deep, focused thinker','Excellent listener and observer','Highly independent worker','Processes complex ideas internally','Calm and steady presence'],
    'S'=>['Rational and practical','Sociable and grounded','Full of life and energy','Prefer to communicate clearly with direct and factual answers','Love to experiment with new practical solutions'],
    'N'=>['Highly imaginative and innovative','Excellent at seeing the big picture','Future-oriented and strategic','Creative problem solver','Notices patterns and hidden meanings'],
    'T'=>['Highly logical and objective','Fair and consistent decision maker','Excellent critical thinker','Unbiased problem solver','Values truth and accuracy above all'],
    'F'=>['Highly empathetic and compassionate','Values harmony and cooperation','Emotionally intelligent','Supportive and encouraging to others','Makes decisions aligned with core values'],
    'P'=>['Highly adaptable and flexible','Spontaneous and open-minded','Excellent in crisis or changing situations','Resourceful and quick-thinking','Comfortable with ambiguity'],
    'J'=>['Highly organized and reliable','Structured and methodical','Decisive and clear-headed','Goal-oriented and driven','Excellent at managing time and projects'],
];
$strengthData = ['name'=>'Core Strength','color'=>'#d98565','bullets'=>$strengthsMap[$topStrengthLetter] ?? []];

$mbtiData = [
    'E'=>['name'=>'Extrovert','color'=>'#3ab2d6','bullets'=>['You are quite talkative, energized and like to spend lots of time with others.','Your primary mode of living is focused externally.','Your attention may shift quickly when there is a lot going on.','You tend to express your views in a strong and direct way.','You quickly adapt to a given situation.','You are sometimes described as an attention-seeker.']],
    'I'=>['name'=>'Introvert','color'=>'#3ab2d6','bullets'=>['You mostly get your energy from dealing with ideas, pictures, memories and reactions.','You are quiet, reserved and like to spend your time alone.','Your primary mode of living is focused internally.','You are passionate but not usually aggressive.','You are a good listener.','You are more of an inside-out person.']],
    'S'=>['name'=>'Sensing','color'=>'#f3c246','bullets'=>['You mostly collect and trust information presented in a detailed and sequential manner.','You think more about the present and learn from the past.','You like to see the practical use of things and learn best from practice.','You notice facts and remember details that are important to you.','You solve problems by working through facts until you understand the problem.','You create meaning from conscious thought and learn by observation.']],
    'N'=>['name'=>'Intuition','color'=>'#f3c246','bullets'=>['You are very imaginative, open-minded and curious.','You prefer to explore and focus on hidden meanings and future possibilities.','You are interested in doing things that are new and different.','You first like to see the biggest picture, then try to find out facts.','You are interested in new things and what might be possible.','You solve problems by leaping between different ideas and possibilities.']],
    'T'=>['name'=>'Thinking','color'=>'#5bb991','bullets'=>['You seem to make decisions based on logic rather than the circumstances.','You believe telling truth is more important than being tactful.','You seem to look for logical explanations or solutions to almost everything.','You can often be seen as very task-oriented, uncaring, or indifferent.','You are ruled by your head instead of your heart.','You are a critical thinker and oriented toward problem solving.']],
    'F'=>['name'=>'Feeling','color'=>'#5bb991','bullets'=>['You seem to make decisions based on your values or the feelings of others involved.','You seem to be ruled by your heart instead of your head.','In your relationships, you appear caring, warm, and tactful.','You look for what is important to others and express concern for others.','You tend to judge situations and others based on feelings and circumstances.','You seek to please others and want to be appreciated.']],
    'P'=>['name'=>'Perceiving','color'=>'#9a7ea7','bullets'=>['You seem to prefer a flexible and spontaneous way of life.','You prefer to adapt to the world rather than organizing it.','You like staying open to new experiences and information.','You like to approach work as play or a mix of work and play.','You appear to be casual and like to keep plans to a minimum.','You are a random thinker who prefers to keep options open.','You are spontaneous and often juggle several tasks at once.']],
    'J'=>['name'=>'Judging','color'=>'#9a7ea7','bullets'=>['You prefer a planned and organized approach to life and work.','You like to have things decided, structured, and settled.','You feel much more comfortable when decisions are made.','You like to make lists of things to do and stick to them.','You like to get your work done before playing or relaxing.','You plan work to avoid rushing just before a deadline.','You focus primarily on completing tasks and achieving goals.']],
];
?>

<htmlpageheader name="pharosHeader">
    <table width="100%" style="border-bottom: 1px solid #2c3e50; padding-bottom: 6px;">
        <tr>
            <td width="70%" style="text-align: left; font-weight: bold; font-size: 13pt; color: #001f3f; text-transform: uppercase;"><?= esc($student_name ?? 'CANDIDATE NAME') ?></td>
            <td width="30%" style="text-align: right;"><img src="<?= FCPATH . 'assets/img/pharos.jpg' ?>" style="height: 32px;" alt="Logo"></td>
        </tr>
    </table>
</htmlpageheader>

<htmlpagefooter name="pharosFooter">
    <table width="100%" style="border-top: 1px solid #000; padding-top: 4px; font-size: 9.5pt; font-weight: bold; color: #000;">
        <tr>
            <td width="70%" style="text-align: left;">Pharos Education &bull; Confidential Dossier &nbsp;&nbsp; info@pharoseducation.com</td>
            <td width="30%" style="text-align: right;">Page {PAGENO} of {nbpg}</td>
        </tr>
    </table>
</htmlpagefooter>

<sethtmlpageheader name="pharosHeader" value="on" show-this-page="1" />
<sethtmlpagefooter name="pharosFooter" value="on" show-this-page="1" />

<div class="cover-page">
    <div class="cover-accent"></div>
    <div style="padding: 100px 20px;">
        <div style="text-align: right; margin-bottom: 80px;">
            <h1 style="font-size: 30pt; margin: 0; color: #fff; letter-spacing: 2px;">PHAROS</h1>
            <p style="margin: 4px 0 0 0; color: #e67e22; font-size: 10pt; letter-spacing: 4px; text-transform: uppercase;">Education Consultancy</p>
        </div>
        <div style="border-left: 5px solid #e67e22; padding-left: 30px; margin-bottom: 60px;">
            <h2 style="font-size: 34pt; margin: 0 0 12px 0; line-height: 1.1; text-transform: uppercase; color: #fff;">Comprehensive<br>Career Analysis</h2>
            <p style="font-size: 12pt; color: #bdc3c7; margin: 0; letter-spacing: 1px;">STRATEGIC BLUEPRINT &amp; PSYCHOMETRIC EVALUATION</p>
        </div>
        <div style="background: rgba(255,255,255,0.05); padding: 25px; border-radius: 1px solid rgba(255,255,255,0.1);">
            <p style="font-size: 9pt; color: #95a5a6; text-transform: uppercase; letter-spacing: 2px; margin: 0 0 8px 0;">Prepared Exclusively For</p>
            <h3 style="font-size: 24pt; margin: 0 0 20px 0; color: #fff; text-transform: uppercase;"><?= esc($student_name) ?></h3>
            <table style="width: 100%; color: #bdc3c7; font-size: 10pt; border-collapse: collapse; border: none;">
                <tr><td style="padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.1); width: 35%;"><strong>Document ID:</strong></td><td style="padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.1); color: #fff;"><?= esc($report_id) ?></td></tr>
                <tr><td style="padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.1);"><strong>Academic Grade/Age:</strong></td><td style="padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.1); color: #fff;"><?= esc($age_grade) ?></td></tr>
                <tr><td style="padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.1);"><strong>Candidate Gender:</strong></td><td style="padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.1); color: #fff;"><?= esc($gender) ?></td></tr>
                <tr><td style="padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.1);"><strong>Registered Email:</strong></td><td style="padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.1); color: #fff;"><?= esc($user_email) ?></td></tr>
                <tr><td style="padding: 10px 0;"><strong>Evaluation Date:</strong></td><td style="padding: 10px 0; color: #fff;"><?= esc($date) ?></td></tr>
            </table>
        </div>
    </div>
    <div style="padding: 18px 10px; background: #0d1117;">
        <p style="margin: 0; font-size: 8.5pt; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px;">Confidential &bull; Document ID: <?= esc($report_id) ?></p>
    </div>
</div>

<div class="pagebreak">
    <div class="header-strip">PREFACE</div>
    <div class="editorial-box" style="font-size: 14pt; line-height: 1.55;">
        <p style="margin-top:0;">We, on behalf of Pharos Consultancy, congratulate you on completing the Career Planning Assessment. We understand that navigating educational and career choices can be a complex journey. Pharos caters to your unique needs by providing holistic, data-driven career planning—helping you maximize your potential and ensuring a structured path to a better tomorrow.</p>
        <p>Our proprietary psychometric engine analyzes your behavioral traits, cognitive aptitudes, intrinsic motivators, and vocational interests. By synthesizing this data, we construct a personalized strategy designed to align your innate strengths with real-world occupational demands.</p>
        <p style="margin-bottom:0;">This dossier is your strategic blueprint. We encourage you to review it thoroughly with your counselors and mentors.</p>
        <p style="margin-top: 20px; margin-bottom:0;">Sincerely,<br><strong style="font-size: 11pt; color: #2980b9;">Team Pharos Education Consultancy</strong></p>
    </div>
    <div style="margin-top: 25px; text-align: center;">
        <img src="<?= FCPATH . 'assets/img/preface.jpg' ?>" alt="Pharos Education" style="max-width: 100%;">
        <h4 style="color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px; margin-top: 12px;">The Assessment Lifecycle</h4>
        <div style="background: #f4f6f9; padding: 14px; border: 1px solid #ecf0f1; display: inline-block; width: 80%;">
            <p style="font-weight: bold; color: #2980b9; margin: 0;">1. Profiling Analysis &rarr; 2. Option Discovery &rarr; 3. Educational Mapping &rarr; 4. Execution Plan</p>
        </div>
    </div>
</div>

<div class="pagebreak">
    <div class="header-strip">YOUR PROFILING STAGE</div>
    <p style="margin-top:0; font-size: 13pt; line-height: 1.55;">Personal profiling is the first step in career planning. The purpose of profiling is to understand your current career planning stage. It will help decide your career objective and roadmap. The ultimate aim of the planning is to take you from the current stage of career planning to the optimized stage. Personal profiling establishes your current readiness in the career planning lifecycle. Identifying this stage allows counselors to calibrate their guidance to your immediate needs.</p>
    <div style="text-align: center; margin: 15px 0;">
        <img src="<?= FCPATH . 'assets/img/confuse.webp' ?>" alt="Pharos Education" style="max-width: 75%;">
    </div>
    <div class="editorial-box" style="border-left: 5px solid #e74c3c; font-size: 13pt; line-height: 1.55;">
        <h3 style="color: #e74c3c; margin-top: 0; text-transform: uppercase;">Current Stage: Exploration / Confused</h3>
        <p><strong>Confused:</strong> You are at the confused stage in career planning. We understand that you are having little idea of career planning, but are usually confused among various career options. At this stage, you are looking for proper guidance. Generally, your career decisions shall be influenced by friends and parents.</p>
        <p><strong>Diagnosis:</strong> You are currently at the exploratory stage in your career planning. You have broad interests but may lack definitive clarity regarding which specific occupational path aligns best with your long-term goals.</p>
        <p><strong>Associated Risks:</strong> Without intervention, there is a risk of misaligned educational investments, career dissatisfaction, or selecting a path based on external pressure rather than internal aptitude.</p>
        <p style="margin-bottom: 4px;"><strong>Strategic Action Plan:</strong></p>
        <ul style="margin: 0;">
            <li>Analyze the cognitive and behavioral data in this report.</li>
            <li>Explore the top 3 recommended Career Clusters.</li>
            <li>Align your 11th/12th-grade subject selections with these clusters.</li>
        </ul>
    </div>
</div>

<div class="pagebreak">
    <div class="header-strip">RESULT OF THE CAREER PERSONALITY</div>
    <div class="editorial-box" style="border-left: 5px solid #e74c3c; margin-bottom: 18px;">
        <h3 style="color: #2c3e50; margin-top: 0;">Your Personality Type: <span style="color: #e74c3c;"><?= esc($mbtiPrimaryTrait) ?></span></h3>
        <p style="margin-bottom:0; font-size: 14pt; line-height: 1.55;">Personality Assessment will help you understand yourself as a person. It will help you expand your career options in alignment with your personality. Self-understanding and awareness can lead you to more appropriate and rewarding career choices. The Personality Type Model identifies four dimensions of personality. The combination of your most dominant preferences is used to create your individual personality type.</p>
    </div>
    <table width="100%" cellspacing="0" cellpadding="0" style="background: #e9ecef; margin-bottom: 18px;">
        <tr>
            <td width="100%" align="center" style="padding: 12px; font-size: 13pt; font-weight: bold; color: #2c3e50; text-transform: uppercase;">
                YOUR PERSONALITY TYPE &mdash; <?= esc(expandMBTICode($mbtiPrimaryTrait)) ?>
            </td>
        </tr>
    </table>
<?php
if (!function_exists('renderPdfMBTISlider')) {
    function renderPdfMBTISlider($leftLabel, $rightLabel, $leftChar, $rightChar, $leftPct, $rightPct, $mainColor, $lightColor, $dominantChar) {
        $isLeftDominant = (strtoupper($leftChar) === strtoupper($dominantChar));
        $imgSrc   = generateMBTISliderGD($leftPct, $rightPct, $mainColor, $lightColor, $isLeftDominant);
        $domPct   = $isLeftDominant ? $leftPct : $rightPct;
        $domLabel = $isLeftDominant ? $leftLabel : $rightLabel;
        echo '<div style="margin-bottom: 18px; text-align: center;">';
        echo '<div style="font-weight: bold; font-size: 12pt; color: '.$mainColor.'; margin-bottom: 4px;">'.round($domPct).'% '.$domLabel.'</div>';
        echo '<table width="100%" cellpadding="0" cellspacing="0" style="border: none;"><tr>';
        echo '<td width="20%" align="right" style="font-weight: bold; font-size: 10pt; padding-right: 12px;">'.$leftLabel.'</td>';
        echo '<td width="60%" align="center" valign="middle"><img src="'.$imgSrc.'" style="width: 100%; height: 20px; display: block;" /></td>';
        echo '<td width="20%" align="left" style="font-weight: bold; font-size: 10pt; padding-left: 12px;">'.$rightLabel.'</td>';
        echo '</tr></table>';
        echo '</div>';
    }
}
renderPdfMBTISlider('Introvert', 'Extrovert', 'I', 'E', $pctI, $pctE, '#42b9d6', '#a5ecf3', $trait1);
renderPdfMBTISlider('Sensing',   'iNtuitive',  'S', 'N', $pctS, $pctN, '#f1c40f', '#fae588', $trait2);
renderPdfMBTISlider('Thinking',  'Feeling',    'T', 'F', $pctT, $pctF, '#27ae60', '#a3e4bd', $trait3);
renderPdfMBTISlider('Judging',   'Perceiving', 'J', 'P', $pctJ, $pctP, '#8e44ad', '#d7bde2', $trait4);
?>
</div>

<?php
if (!function_exists('renderPdfMBTIBlock')) {
    function renderPdfMBTIBlock($letter, $data, $question) {
        echo '<table width="100%" cellpadding="0" cellspacing="0" style="border: none; background: '.$data['color'].'; margin-bottom: 12px;">';
        echo '<tr>';
        echo '<td width="18%" align="center" valign="middle" style="padding: 12px 8px;">';
        echo '<div style="font-size: 50pt; font-weight: bold; line-height: 1; color: #fff;">'.$letter.'</div>';
        echo '<div style="font-size: 9.5pt; font-weight: bold; color: #fff; margin-top: 3px;">'.$data['name'].'</div>';
        echo '</td>';
        echo '<td width="82%" valign="top" style=" padding: 12px 15px;">';
        echo '<div style="font-weight: bold; font-size: 15pt; color:#fff;; margin-bottom: 6px; text-transform: uppercase;">'.$question.'</div>';
        echo '<table width="100%" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px !important; border: none;">';
        echo '<tr> <td style="padding: 15px 20px;">';
        echo '<ul style="margin: 0; padding-left: 18px; font-size: 14pt; color: #333; line-height: 1.55;">';
        foreach ($data['bullets'] as $bullet) { echo '<li style="margin-bottom: 3px;">'.$bullet.'</li>'; }
        echo '</ul>';
        echo '</td></tr></table>';
        echo '</td>';
        echo '</tr>';
        echo '</table>';
    }
}
?>
<div class="pagebreak">
    <div class="header-strip">YOUR CAREER PERSONALITY ANALYSIS</div>
    <?php
    renderPdfMBTIBlock($trait1, $mbtiData[$trait1], 'Where do you focus your energy?');
    renderPdfMBTIBlock($trait2, $mbtiData[$trait2], 'How do you process information?');
    renderPdfMBTIBlock($trait3, $mbtiData[$trait3], 'How do you make decisions?');
    renderPdfMBTIBlock($trait4, $mbtiData[$trait4], 'How do you prefer to plan your work?');
    renderPdfMBTIBlock($topStrengthLetter, $strengthData, 'Your Core Strength is');
    ?>
</div>

<div class="pagebreak">
    <div class="header-strip">YOUR CAREER INTEREST TYPES</div>
    <p style="margin-top:0; margin-bottom: 12px; font-size: 14pt; line-height: 1.55;">The Holland Code measures six broad vocational interest patterns. Understanding your top interests helps identify environments where you will be most engaged and satisfied. The Career Interest Assessment (CIA) measures six broad interest patterns that can be used to describe your career interest. Most people's interests are reflected by two or three themes, combined to form a cluster of interests.</p>
    <div style="background: #fdfdfd; border: 1px solid #ecf0f1; padding: 20px 25px;">
        <table width="100%" cellpadding="0" cellspacing="0" style="border: none;">
        <?php
        $rColors = ['Realistic'=>'#16a085','Investigative'=>'#2980b9','Artistic'=>'#e67e22','Social'=>'#e74c3c','Enterprising'=>'#f39c12','Conventional'=>'#8e44ad'];
        if (!empty($riasec)):
            arsort($riasec);
            foreach ($riasec as $rtrait => $rscore):
                $rtrait = ucfirst(strtolower($rtrait));
                $col    = $rColors[$rtrait] ?? '#34495e';
                $img    = generateGDInlineBar($rscore, $col);
        ?>
        <tr>
            <td width="22%" align="right" style="font-weight: bold; font-size: 13pt; color: <?= $col ?>; padding: 14px 14px 14px 0;"><?= esc($rtrait) ?></td>
            <td width="63%" valign="middle" style="padding: 14px 0;"><img src="<?= $img['src'] ?>" width="<?= $img['width'] ?>" height="<?= $img['height'] ?>" style="display: block;"></td>
            <td width="15%" align="left" style="font-weight: bold; font-size: 13pt; color: <?= $col ?>; padding: 14px 0 14px 14px;"><?= esc(round($rscore, 1)) ?>%</td>
        </tr>
        <?php endforeach; endif; ?>
        </table>
    </div>
</div>

<div class="pagebreak">
    <div class="header-strip">YOUR CAREER INTEREST ANALYSIS</div>
    <?php
    $riasecContent = [
        'Realistic'    =>['color'=>'#3ba776','bullets'=>['You are active and stable and enjoy hands-on or manual activities.','You prefer to work with things rather than ideas and people.','You tend to communicate in a frank, direct manner and value material things.','You may be uncomfortable or less adept with human relations.','You value practical things that you can see and touch.','You have good skills at handling tools, mechanical drawings, machines or animals.']],
        'Investigative'=>['color'=>'#3c6e91','bullets'=>['You are analytical, intellectual, observant and enjoy research.','You enjoy using logic and solving complex problems.','You are interested in occupations that require observation, learning and investigation.','You are introspective and focused on creative problem solving.','You prefer working with ideas and using technology.']],
        'Artistic'     =>['color'=>'#f47b34','bullets'=>['You are imaginative and enjoy creative activities.','You encourage originality and use of imagination in a flexible, unstructured setting.','You are generally impulsive and emotional.','You tend to communicate in a very expressive and open manner.','You seek opportunities for self-expression through artistic creation.','You like to work with ideas and things.']],
        'Social'       =>['color'=>'#e65141','bullets'=>['You are helpful, friendly, and trustworthy.','You prefer working with and assisting people.','You tend to communicate in a warm, tactful manner.','You value serving others and social justice.','You excel at teaching, counseling, nursing, or giving information.','You solve problems by discussing them with others.']],
        'Enterprising' =>['color'=>'#eab54d','bullets'=>['You are ambitious, energetic, and sociable.','You prefer to lead, persuade, and sell things or ideas.','You tend to communicate in an assertive, confident manner.','You value success, politics, leadership, and wealth.','You are skilled at public speaking, managing others, and business strategy.']],
        'Conventional' =>['color'=>'#8e44ad','bullets'=>['You are organized, detail-oriented, and reliable.','You prefer working with data, numbers, and established procedures.','You tend to communicate in a highly structured, precise manner.','You value accuracy, stability, and efficiency.','You excel at keeping records, financial tracking, and operating computer systems.']],
    ];
    
    if (!empty($riasec)):
        $top3r = [];
        foreach (array_slice(array_keys($riasec), 0, 3) as $t) { $top3r[] = ucfirst(trim($t)); }
        foreach ($top3r as $rtrait):
            $rscore  = $riasec[strtolower($rtrait)] ?? $riasec[$rtrait] ?? 0;
            $suffix  = ($rscore >= 50) ? '-HIGH' : '';
            $rdata   = $riasecContent[$rtrait] ?? $riasecContent['Realistic'];
            $rcolor  = $rdata['color'];
            $rletter = strtoupper(substr($rtrait, 0, 1));
    ?>
<table width="100%" cellpadding="0" cellspacing="0" style="background-color: <?= $rcolor ?>; border-radius: 12px; margin-bottom: 20px; border: none;">
        <tr>
            <td width="22%" align="center" valign="middle" style="padding: 20px 10px; color: #ffffff;">
                <div style="font-family: sans-serif; font-size: 55pt; font-weight: bold; line-height: 1;">
                    <?= $rletter ?>
                </div>
                <div style="font-family: sans-serif; font-size: 13pt; font-weight: bold; margin-top: 8px;">
                    <?= $rtrait ?><?= $suffix ?>
                </div>
            </td>
            
            <td width="78%" valign="middle" style="padding: 12px 12px 12px 0;">
                
                <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; border: none;">
                    <tr>
                        <td style="padding: 15px 20px;">
                            <ul style="margin: 0; padding-left: 20px; font-family: sans-serif; font-size: 14pt;  color: #222222; line-height: 1.5;">
                                <?php foreach ($rdata['bullets'] as $rbullet): ?>
                                    <li style="margin-bottom: 6px;"><?= $rbullet ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

    <?php endforeach; else: ?>
        <div class="editorial-box"><p>No Career Interest data available.</p></div>
    <?php endif; ?>
</div>

<div class="pagebreak">
    <div class="header-strip">YOUR CAREER MOTIVATOR TYPES</div>
    
    <p style="margin-bottom: 40px; color: #444; font-size: 14pt; line-height: 1.55;">
        Values are the things that are most important to us in our lives and careers. Our values are formed in a variety of ways through our life experiences, our feelings and our families. Being aware of your intrinsic motivators is critical because a career choice that aligns with your core beliefs is significantly more likely to be a lasting, positive choice.
    </p>
    
    <div class="no-break" style="background: #ffffff; padding: 20px 0;">
        <table style="width: 100%; border-collapse: collapse; border: none;">
            <?php 
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

            // Palette matching your screenshot
            $mColors = ['#c0392b', '#d35400', '#e67e22', '#f39c12', '#27ae60', '#2980b9', '#8e44ad', '#34495e'];

            if(!empty($motivators)):
                arsort($motivators); 
                $i = 0;
                
                foreach($motivators as $motivator => $score): 
                    $color = $mColors[$i % count($mColors)];
                    $i++;
                    
                    $motDesc = 'Core intrinsic value driving professional engagement and long-term career satisfaction.';
                    $lowerMot = strtolower($motivator);
                    foreach($motivatorDescriptions as $keyword => $text) {
                        if (strpos($lowerMot, $keyword) !== false) {
                            $motDesc = $text;
                            break;
                        }
                    }
                    
                    // Generate the image via GD
                    $barImageSrc =  generateGDInlineBar($score, $color);
            ?>
            <tr>
                <td style="width: 34%; text-align: right; padding-right: 20px; vertical-align: top; padding-bottom: 30px;">
                    <div style="font-weight: bold; font-size: 15pt; color: <?= $color ?>; text-transform: uppercase;">
                        <?= esc($motivator) ?>
                    </div>
                    <div style="font-size: 13pt; color: #95a5a6; margin-top: 3px; line-height: 1.3;">
                        <?= esc($motDesc) ?>
                    </div>
                </td>
                
                <td style="width: 50%; vertical-align: top; padding-top: 6px; padding-bottom: 30px;">
                    <img src="<?= $barImageSrc['src'] ?>" style="width: 100%; height: 30px; display: block; border-radius: 3px;">
                </td>
                
                <td style="width: 10%; text-align: right; font-weight: bold; font-size: 11.5pt; color: <?= $color ?>; vertical-align: top; padding-top: 0px; padding-bottom: 30px;">
                    <?= esc($score) ?>%
                </td>
            </tr>
            <?php 
                endforeach; 
            else:
            ?>
                <tr><td colspan="3"><p style="text-align: center; color: #7f8c8d; font-style: italic;">Motivator profiling data is currently pending.</p></td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<div class="pagebreak">
    <div class="header-strip">YOUR CAREER MOTIVATOR ANALYSIS</div>
    <?php
    $topMotivator = !empty($motivators) ? array_key_first($motivators) : 'Achievement';
    $topColor     = $mPalette[0];
    $motivatorAnalysis = [
        'achievement'  =>['desc'=>'You are fundamentally driven by results, mastery, and the successful completion of complex tasks. Workplaces that fail to provide challenging goals or fail to utilize your core abilities will quickly lead to disengagement and burnout.','strategies'=>['Target roles that offer clear KPIs and performance-based advancement.','Seek out environments that allow you to take projects from conception to completion.','Avoid highly bureaucratic roles where results are slowed down by excessive red tape.','Look for cultures that actively celebrate wins and tangible outputs.']],
        'independence' =>['desc'=>'Your primary psychological driver is autonomy. You thrive when given the trust to execute tasks in your own way. Micro-management and rigid, inflexible schedules will severely restrict your potential and morale.','strategies'=>['Seek roles offering flexible working hours, remote options, or entrepreneurial freedom.','Target project-based work where you are judged on the final output, not the process.','Consider freelance, consulting, or highly autonomous corporate departments.','Avoid heavily monitored, strictly hierarchical, or heavily regimented roles.']],
        'recognition'  =>['desc'=>'You are highly motivated by prestige, leadership potential, and visible career trajectories. Acknowledgment of your contributions is critical to your self-esteem and professional drive.','strategies'=>['Target large, prestigious organizations with well-defined corporate ladders.','Seek roles that put you in public-facing or high-visibility leadership positions.','Ensure the career paths you choose have distinct job titles and promotion metrics.','Avoid behind-the-scenes or entirely supportive roles with no upward mobility.']],
        'relationship' =>['desc'=>'You evaluate career success based on the quality of human connections and the positive impact you have on others. A toxic or highly competitive culture will drain your energy faster than a heavy workload.','strategies'=>['Target organizations known for their strong, supportive, and inclusive cultures.','Seek out roles focused on client success, team building, or social impact.','Prioritize the who over the what when evaluating future roles.','Avoid highly cutthroat, isolated, or purely transactional sales environments.']],
        'support'      =>['desc'=>'You thrive in environments where leadership provides clear direction, fair policies, and solid backing. You value knowing that the organization stands behind its employees and maintains high ethical standards.','strategies'=>['Seek out well-established companies with strong HR policies and training programs.','Look for roles with clear job descriptions and accessible, competent management.','Prioritize stability and organizational reputation over risky startups.','Avoid chaotic work environments with high turnover or absentee leadership.']],
        'condition'    =>['desc'=>'Your primary focus is the holistic quality of your working life. Job security, environmental comfort, fair compensation, and preserving your physical and mental health are your non-negotiables.','strategies'=>['Target industries with strong union backing or historically stable employment rates.','Prioritize comprehensive benefits packages (healthcare, retirement) over base salary.','Seek roles with predictable hours to protect your personal and family time.','Avoid hustle-culture jobs requiring constant overtime or high physical stress.']],
    ];
    $lowerTopMot  = strtolower($topMotivator);
    $analysisData = $motivatorAnalysis['achievement'];
    foreach ($motivatorAnalysis as $keyword => $adata) { if (strpos($lowerTopMot, $keyword) !== false) { $analysisData = $adata; break; } }
    ?>
    <div style="border-left: 6px solid <?= $topColor ?>; padding: 22px 28px; background: #fdfdfd;">
        <h3 style="color: <?= $topColor ?>; text-transform: uppercase; font-size: 13pt; margin-top: 0; margin-bottom: 10px;">Primary Driver: <?= esc($topMotivator) ?></h3>
        <p style="font-size: 15pt; color: #444; line-height: 1.7; margin-bottom: 18px;"><?= esc($analysisData['desc']) ?></p>
        <div style="background: #f8f9fa; padding: 18px; border: 1px solid #eee;">
            <p style="font-size: 18pt; color: #2c3e50; font-weight: bold; text-transform: uppercase; margin-top: 0; margin-bottom: 10px; border-bottom: 1px solid #ddd; padding-bottom: 6px;">Strategic Career Alignment</p>
            <ul style="color: #34495e; font-size: 14pt; line-height: 1.55; margin: 0; padding-left: 18px;">
                <?php foreach ($analysisData['strategies'] as $strat): ?>
                    <li style="margin-bottom: 7px;"><?= esc($strat) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<div class="pagebreak">
    <div class="header-strip">YOUR LEARNING STYLE TYPES</div>
    <p style="margin-bottom: 40px; font-size: 14pt; line-height: 1.55; color: #444;">
        Understanding your learning style helps you study smarter, not harder. The VARK model identifies your primary preferences for taking in and processing new academic information. Most people have a dominant style, while others are multimodal and benefit from a combination of methods.
    </p>
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
                $barImageSrc = generateGDInlineBar($score, $color);
        ?>
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 28px; border: none;">
            <tr>
                <td style="width: 32%; text-align: right; padding-right: 20px; vertical-align: middle;">
                    <div style="font-weight: bold; font-size: 20pt; color: <?= $color ?>; text-transform: uppercase;">
                        <?= esc($style) ?>
                    </div>
                    <div style="font-size: 13pt; color: #7f8c8d; margin-top: 4px; line-height: 1.4;">
                        <?= esc($desc) ?>
                    </div>
                </td>
                
                <td style="width: 58%; vertical-align: middle;">
                         <img src="<?= $barImageSrc['src'] ?>" style="width: 100%; height: 30px; display: block; border-radius: 3px;">
                </td>
                
                <td style="width: 10%; text-align: left; padding-left: 15px; font-weight: bold; font-size: 15pt; color: <?= $color ?>;">
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

<div class="pagebreak">
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
    <div style="border-left: 6px solid <?= $topColor ?>; padding: 22px 28px; background: #fdfdfd;">
        <h3 style="color: <?= $topColor ?>; text-transform: uppercase; font-size: 20pt; margin-top: 0; margin-bottom: 10px;">
            Dominant Style: <?= esc($topLearning) ?></h3>
        <p style="font-size: 15pt; color: #444; line-height: 1.7; margin-bottom: 18px;"><?= esc($analysis['desc']) ?></p>
        <div style="background: #f8f9fa; padding: 18px; border: 1px solid #eee;">
            <p style="font-size: 18pt; color: #2c3e50; font-weight: bold; text-transform: uppercase; margin-top: 0; margin-bottom: 10px; border-bottom: 1px solid #ddd; padding-bottom: 6px;">
                Strategic Execution for Academic Growth</p>
            <ul style="color: #34495e; font-size: 14pt; line-height: 1.55; margin: 0; padding-left: 18px;">
                <?php foreach ($analysis['strategies'] as $strat): ?>
                    <li style="margin-bottom: 7px;"><?= esc($strat) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<div class="pagebreak">
    <div class="header-strip">YOUR MULTIPLE INTELLIGENCES (GARDNER)</div>
    
    <p style="margin-bottom: 30px; font-size: 14pt; line-height: 1.55; color: #444; ">
        Dr. Howard Gardner's Theory of Multiple Intelligences suggests that traditional IQ testing does not fully capture human ability. We all possess different kinds of minds and learn, remember, perform, and understand in different ways. Understanding your dominant intelligences helps identify careers where your natural talents will be highly valued.
    </p>
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
                $barImageSrc = generateGDInlineBar($score, $matchedColor);
        ?>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 28px; border: none;">
            <tr>
                <td style="width: 32%; text-align: right; padding-right: 20px; vertical-align: middle;">
                    <div style="font-weight: bold; font-size: 15pt; color: <?= $matchedColor ?>; text-transform: uppercase;">
                        <?= esc($displayName) ?>
                    </div>
                    <div style="font-size: 9pt; color: #7f8c8d; margin-top: 4px; line-height: 1.4;">
                        <?= esc($matchedDesc) ?>
                    </div>
                </td>
                
                <td style="width: 58%; vertical-align: middle;">
                         <img src="<?= $barImageSrc['src'] ?>" style="width: 100%; height: 30px; display: block; border-radius: 3px;">
                </td>
                
                <td style="width: 10%; text-align: left; padding-left: 15px; font-weight: bold; font-size: 11pt; color: <?= $matchedColor ?>;">
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

<div class="pagebreak">
    <div class="header-strip">YOUR SKILLS AND ABILITIES (APTITUDE)</div>
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 16px;">
        <tr>
            <td width="70%" valign="top" style="padding-right: 15px;"><p style="margin-top:0; font-size: 14pt; line-height: 1.55;">
                <p>  Cognitive aptitudes measure your raw capacity to perform specific types of tasks. 
                    The skills & abilities scores will help us to explore and identify different ways to reshape your career direction.
                    This simple graph shows how you have scored on each of these skills and abilities. The graph on the top will
                    show the average score of your overall skills and abilities.
                </p>
            </td>
            <td width="30%" align="center" valign="middle" style="background: #fdfdfd; padding: 12px; border: 1px solid #e0e0e0;">
                <p style="margin: 0; font-size: 11pt; text-transform: uppercase; color: #7f8c8d; font-weight: bold;">Overall Score</p>
                <p style="color: #42b9d6; font-weight: bold; font-size: 15pt; margin: 4px 0 0 0;"><?= esc($advData['skills_overall']['score'] ?? 0) ?>%<br><span style="font-size: 10pt; color: #2c3e50;"><?= esc($advData['skills_overall']['band'] ?? 'Average') ?></span></p>
            </td>
        </tr>
    </table>
    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
    <?php
    $aptDict = [
        'numerical'     =>['color'=>'#2c3e9e','desc'=>'Assesses mathematical capacity. Essential for finance and engineering.Measures capacity to understand, analyze, and manipulate mathematical data. Essential for finance, engineering, and data sciences.'],
        'logical'       =>['color'=>'#f39c12','desc'=>'Assesses capability to identify patterns, evaluate arguments, and solve complex problems. Crucial for IT, law, and research fields.'],
        'verbal'        =>['color'=>'#2980b9','desc'=>'Evaluates comprehension of written information and articulation of ideas. Key for journalism, management, and communications.'],
        'spatial'       =>['color'=>'#8e44ad','desc'=>'Measures capacity to visualize and mentally manipulate 2D and 3D objects. Vital for architecture, design, and specialized medical fields.'],
        'administrative'=>['color'=>'#27ae60','desc'=>'Assesses attention to detail, organizational capacity, and ability to follow procedures efficiently. Important for operations and management.'],
        'leadership'    =>['color'=>'#27ae60','desc'=>'Evaluates natural propensity to guide, influence, and take charge of group dynamics. Essential for executive and entrepreneurial roles.'],
        'mechanical'    =>['color'=>'#e74c3c','desc'=>'Assesses understanding of basic physical principles, machinery, and structural interactions. Important for manufacturing and mechanics.'],
        'social'        =>['color' =>'#e74c3c','desc'=> 'Measures empathy, emotional intelligence, and ability to navigate human interactions. Vital for human resources, psychology, and sales.']
    ];
    if (!empty($skills)):
        $rowBg = false;
        foreach ($skills as $sname => $sdata):
            $sscore = $sdata['score'] ?? 0;
            $slower = strtolower($sname);
            $sdict  = ['color'=>'#2980b9','desc'=>'Fundamental cognitive capacity score.'];
            foreach ($aptDict as $k => $v) { if (strpos($slower, $k) !== false) { $sdict = $v; break; } }
            $donutImg = generateGDDonutChart($sscore, $sdict['color']);
            $bg = $rowBg ? '#f8f9fa' : '#ffffff';
            $rowBg = !$rowBg;
    ?>
        <tr style="background: <?= $bg ?>; border-bottom: 1px solid #ecf0f1;">
            <td width="40%" align="center" valign="middle" style="padding: 10px 6px;">
                <div style="position: relative; width: 130px; height: 130px; margin: 0 auto;">
                    <img src="<?= $donutImg ?>" style="width: 130px; height: 130px;" />
                    <div style="position: absolute; top: -28px; left: 0; width: 100%; text-align: center; font-size: 11pt; font-weight: bold; color: #000;"><?= $sscore ?>%</div>
                </div>
            </td>
            <td width="60%" valign="middle" style="padding: 10px 14px;">
                <h3 style="font-size: 20pt; margin: 0 0 4px 0; color: <?= $sdict['color'] ?>; text-transform: uppercase;"><?= esc($sname) ?></h3>
                <p style="font-size: 14pt; line-height: 1.55; color: #444; margin: 0;"><?= $sdict['desc'] ?></p>
            </td>
        </tr>
    <?php endforeach; endif; ?>
    </table>
</div>

<div class="pagebreak">
    <div class="header-strip">YOUR CAREER CLUSTERS (O*NET MAPPED)</div>
    <p style="margin-bottom: 30px;font-size: 13pt; line-height: 1.5;">
        Career Clusters are groups of similar occupations and industries that require similar skills. It provides a career
road map for pursuing further education and career opportunities. They help you connect your Education with
your Career Planning. Career Cluster helps you narrow down your occupation choices based on your
assessment responses. Results show which Career Clusters would be best to explore. A simple graph report
shows how you have scored on each of the Career Clusters.
</p>
     <table width="100%" cellpadding="2" cellspacing="2" style="background: #fdfdfd; border: 1px solid #eaeaea; padding: 25px;">
        <?php 
        $c_colors = ['#16a085', '#2980b9', '#e67e22', '#8e44ad', '#00acc1', '#c0392b'];
        $i = 0;
        if(!empty($clusters)):
            foreach($clusters as $cluster => $score): 
                $col = $c_colors[$i % count($c_colors)]; $i++;
                $img = generateGDInlineBar($score, $col);
        ?>
   
        <tr>
                <td style="width: 30%; color: <?= $col ?>; text-align:left; padding-right: 0px; vertical-align: middle; text-transform: uppercase;">   
                        <?= esc($cluster) ?>
                </td>
                
                <td style="width: 60%; vertical-align: middle;">
                         <img src="<?= $img['src'] ?>" style="width: 100%; height: 25px; display: block; border-radius: 4px;">
                </td>
                <td style="width: 10%; text-align: left; padding-left: 15px; font-weight: bold; font-size: 11pt; color: <?= $col ?>;">
                    <?= esc($score) ?>%
                </td>
            </tr>
   
     <?php endforeach; endif; ?>
             </table>
</div>



<div class="pagebreak">
    <div class="header-strip">YOUR SELECTED 4 CAREER CLUSTERS</div>
    <?php
    if (!empty($clusters)):
        $top4c = array_slice(array_keys($clusters), 0, 4);
        $clusterColors = ['#42b9d6','#e6a817','#27ae60','#e67e22'];
        $clusterDict = [
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
        foreach ($top4c as $cidx => $clusterName):
            $cColor  = $clusterColors[$cidx % count($clusterColors)];
            $rankNum = $cidx + 1;
            $cDesc   = 'Professionals in this cluster are involved in strategic execution relevant to this industry. Indicates a natural propensity for daily workflows.';
            foreach ($clusterDict as $ck => $ct) { if (strpos(strtolower($clusterName), $ck) !== false) { $cDesc = $ct; break; } }
    ?>
    <div style="background: #f8f9fa; padding: 20px; margin-bottom: 20px; border-radius: 6px; border-left: 5px solid <?= $cColor ?>;">
        <h3 style="color: <?= $cColor?>; text-transform: uppercase; margin-bottom: 5px; font-size: 18pt;">
            <?= $rankNum. '. ' . esc($clusterName) ?>
        </h3>
        <p style="font-size: 14pt; margin-top: 0; line-height: 1.5; color: #34495e;">
            <?= esc($cDesc) ?>
        </p>
    </div>
    <?php endforeach; else: ?>
        <div class="editorial-box"><p>No cluster data available.</p></div>
    <?php endif; ?>
</div>

<div class="pagebreak">
    <div class="header-strip">YOUR ACADEMIC ROADMAP (STREAM SELECTION)</div>
    <p style="margin-bottom: 30px; line-height: 1.5; color: #444; font-size: 13pt;">
        Based on a rigorous cross-analysis of your <strong> Career Interests</strong>, <strong>Cognitive Aptitude</strong>, and <strong>Multiple Intelligences</strong>, our engine has calculated your compatibility with the core academic streams. Choosing the right academic stream is the critical first step in executing your long-term career plan.
    </p>
    <div style="page-break-inside: avoid; background-color: #fdfdfd; border: 1px solid #eaeaea; border-radius: 8px; padding: 30px 30px 10px 30px;">
        
    <h3 style="color: #2c3e50; font-size: 15pt; margin-top: 0; margin-bottom: 25px; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px;">
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
    
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 20px; background-color: #ffffff; border: 1px solid #ecf0f1; border-left: 5px solid <?= $data['color'] ?>; border-radius: 6px; page-break-inside: avoid;">
        <tr>
            <td width="75%" valign="top" style="padding: 20px;">
                <div style="font-size: 14pt; font-weight: bold; color: <?= $data['color'] ?>; margin-bottom: 5px;">
                    <?= $rank ?>. <?= esc($streamName) ?>
                </div>
                <div style="font-size: 11pt; color: #7f8c8d; line-height: 1.5; padding-right: 15px;">
                    <?= esc($data['desc']) ?>
                </div>
            </td>
            
            <td width="25%" align="center" valign="middle" style="padding: 20px; border-left: 1px dashed #bdc3c7;">
                <div style="font-size: 22pt; font-weight: bold; color: <?= $data['color'] ?>; line-height: 1;">
                    <?= esc($data['match_percentage']) ?>%
                </div>
                <div style="font-size: 8pt; font-weight: bold; color: <?= $badgeColor ?>; margin-top: 5px; text-transform: uppercase;">
                    <?= $badgeText ?>
                </div>
            </td>
        </tr>
    </table>

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
</div>

<div class="pagebreak">
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
    <div class="editorial-box" style="page-break-inside: avoid; margin-top: 20px; padding: 25px; border-top: 4px solid <?= $accent ?>; background-color: #fdfdfd;">
        <h3 style="color: <?= $accent ?>; margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid #eeeeee; padding-bottom: 5px; font-size: 13pt;">
            Track <?= $trackNum ?>: <?= esc($cluster) ?> Focus
        </h3>
        
        <p style="font-size: 9pt; font-weight: bold; color: #2c3e50; margin-top: 15px; text-transform: uppercase;">1. Core Academics (Mandatory)</p>
        <p style="font-size: 8pt; color: #7f8c8d; margin-top: -5px; margin-bottom: 8px;">Foundational subjects required for university admission in this field.</p>
        
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 15px; border: none;">
            <?php foreach($subjects['core'] as $sub => $val): ?>
            <tr>
                <td width="35%" style="padding: 5px 0; font-size: 9.5pt; color: #333333; font-weight: bold;"><?= esc($sub) ?></td>
                <td width="50%" style="padding: 5px 15px; vertical-align: middle;">
                    <img src="<?= generateGDProgressBar($val, $accent) ?>" style="width: 100%; height: 12px; border: none; vertical-align: middle;" alt="Progress" />
                </td>
                <td width="15%" align="right" style="padding: 5px 0; font-size: 8.5pt; font-weight: bold; color: <?= $accent ?>;">Critical</td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <p style="font-size: 9pt; font-weight: bold; color: #2c3e50; margin-top: 20px; text-transform: uppercase;">2. Recommended Electives</p>
        <p style="font-size: 8pt; color: #7f8c8d; margin-top: -5px; margin-bottom: 8px;">Subjects that strengthen your academic profile and application.</p>
        
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 15px; border: none;">
            <?php foreach($subjects['elective'] as $sub => $val): ?>
            <tr>
                <td width="35%" style="padding: 5px 0; font-size: 9.5pt; color: #555555;"><?= esc($sub) ?></td>
                <td width="50%" style="padding: 5px 15px; vertical-align: middle;">
                    <img src="<?= generateGDProgressBar($val, '#34495e') ?>" style="width: 100%; height: 12px; border: none; vertical-align: middle;" alt="Progress" />
                </td>
                <td width="15%" align="right" style="padding: 5px 0; font-size: 8.5pt; font-weight: bold; color: #34495e;">Recommended</td>
            </tr>
            <?php endforeach; ?>
        </table>

        <p style="font-size: 9pt; font-weight: bold; color: #e67e22; margin-top: 20px; text-transform: uppercase;">3. Applied Skills & Vocational Tracks</p>
        <p style="font-size: 8pt; color: #7f8c8d; margin-top: -5px; margin-bottom: 8px;">Real-world skills that demonstrate early aptitude to admissions officers.</p>
        
        <table width="100%" cellpadding="0" cellspacing="0" style="border: none;">
            <?php foreach($subjects['skill'] as $sub => $val): ?>
            <tr>
                <td width="35%" style="padding: 5px 0; font-size: 9.5pt; color: #e67e22; font-weight: bold;"><?= esc($sub) ?></td>
                <td width="50%" style="padding: 5px 15px; vertical-align: middle;">
                    <img src="<?= generateGDProgressBar($val, '#e67e22') ?>" style="width: 100%; height: 12px; border: none; vertical-align: middle;" alt="Progress" />
                </td>
                <td width="15%" align="right" style="padding: 5px 0; font-size: 8.5pt; font-weight: bold; color: #e67e22;">High Value</td>
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
</div>

<div class="pagebreak">
    <div class="header-strip">MULTI-DIMENSIONAL FIT ANALYSIS</div>
    <p style="margin-top:0; margin-bottom: 18px; line-height: 1.6; color: #444; font-size: 14pt;">This radar chart plots your unique RIASEC psychometric signature (Blue) against the Ideal Benchmark profile (Green) required by your top recommended career path.</p>
    <?php
    $idealScores = [];
    if (!empty($riasec)) {
        $sortedKeys = array_keys($riasec);
        $idealScores[strtolower($sortedKeys[0] ?? 'realistic')]     = 95;
        $idealScores[strtolower($sortedKeys[1] ?? 'investigative')] = 80;
        $idealScores[strtolower($sortedKeys[2] ?? 'artistic')]      = 60;
        $idealScores[strtolower($sortedKeys[3] ?? 'social')]        = 35;
        $idealScores[strtolower($sortedKeys[4] ?? 'enterprising')]  = 25;
        $idealScores[strtolower($sortedKeys[5] ?? 'conventional')]  = 20;
    }
    $topPathName = isset($advData['career_paths'][0]['title']) ? explode(' - ', $advData['career_paths'][0]['title'])[0] : 'Your Top Career';
    ?>
    <table width="100%" cellpadding="0" cellspacing="0" style="background: #fdfdfd; border: 1px solid #eaeaea; padding: 25px;">
        <tr>
            <td width="50%" align="center" valign="middle">
                <?= renderRiasecRadarSVG($riasec, $idealScores) ?>
            </td>
            <td width="50%" valign="middle" style="padding-left: 22px; border-left: 1px solid #ecf0f1;">
                <div style="width: 45%; padding-left: 30px; border-left: 1px solid #ecf0f1;">
            
    <h3 style="color: #2c3e50; font-size: 13pt; margin-top: 0; margin-bottom: 20px;">Profile Alignment</h3>
    
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 15px; border: none;">
        <tr>
            <td width="20" valign="top" style="padding-right: 15px;">
                <div style="width: 20px; height: 20px; background-color: rgba(41, 128, 185, 0.6); border: 2px solid #2980b9; border-radius: 4px;"></div>
            </td>
            <td valign="top">
                <div style="font-weight: bold; color: #2980b9; font-size: 11pt; margin-bottom: 3px;">Your Profile</div>
                <div style="font-size: 9pt; color: #7f8c8d; line-height: 1.4;">Your actual assessed cognitive and interest footprint.</div>
            </td>
        </tr>
    </table>

    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 25px; border: none;">
        <tr>
            <td width="20" valign="top" style="padding-right: 15px;">
                <div style="width: 20px; height: 20px; background-color: rgba(39, 174, 96, 0.15); border: 2px dashed #27ae60; border-radius: 4px;"></div>
            </td>
            <td valign="top">
                <div style="font-weight: bold; color: #27ae60; font-size: 11pt; margin-bottom: 3px;">Benchmark: <?= esc($topPathName) ?></div>
                <div style="font-size: 9pt; color: #7f8c8d; line-height: 1.4;">The ideal psychological baseline required to thrive in this role.</div>
            </td>
        </tr>
    </table>

    <table width="100%" cellpadding="0" cellspacing="0" style="border: none;">
        <tr>
            <td style="background-color: #ebf5fb; padding: 15px; border-radius: 6px; border-left: 4px solid #2980b9;">
                <div style="font-size: 9.5pt; color: #2c3e50; font-weight: bold; margin-bottom: 5px;">Diagnostic Conclusion</div>
                <div style="font-size: 8.5pt; color: #34495e; line-height: 1.5;">
                    The geometry of your profile strongly encompasses the benchmark boundaries. Areas where the blue shape exceeds the green dashed line indicate secondary talents you can leverage for a competitive advantage in this field.
                </div>
            </td>
        </tr>
    </table>
    
</div>
            </td>
        </tr>
    </table>
</div>

<div class="pagebreak">
    <h2 style="color: #2c3e50; font-size: 16pt; margin-bottom: 20px; text-transform: uppercase;">YOUR CAREER PATHS</h2>
    <p>This matrix isolates specific job titles and evaluates your exact psychological and cognitive correlation to those roles. A "Good Choice" indicates high alignment across both your personality profile and cognitive aptitude baseline.</p>
    <table style="width: 100%; border-collapse: collapse; border: 1px solid #1a2b3c;">
        <thead>
            <tr>
                <th colspan="5" style="background: #34495e; color: white; padding: 12px 15px; text-align: left; font-size: 14pt;">Recommendations for you</th>
            </tr>
            <tr>
                <th style="background: #34495e; color: white; padding: 10px; border: 1px solid #1a2b3c; width: 5%; text-align: center; font-size: 10pt;"></th>
                <th style="background: #34495e; color: white; padding: 10px; border: 1px solid #1a2b3c; width: 40%; text-align: center; font-size: 10pt;">Career Paths</th>
                <th style="background: #34495e; color: white; padding: 10px; border: 1px solid #1a2b3c; width: 20%; text-align: center; font-size: 10pt;">Psy. Analysis</th>
                <th style="background: #34495e; color: white; padding: 10px; border: 1px solid #1a2b3c; width: 20%; text-align: center; font-size: 10pt;">Skill and Abilities</th>
                <th style="background: #34495e; color: white; padding: 10px; border: 1px solid #1a2b3c; width: 15%; text-align: center; font-size: 10pt;">Comment</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if (!empty($paths)):
            $prank = 1;
            foreach ($paths as $path):
                $psyScore = $path['psy']['score'] ?? 0;   $psyBand = $path['psy']['band'] ?? 'N/A';   $psyColor = getBandColor($psyBand);
                $aptScore = $path['skill']['score'] ?? 0; $aptBand = $path['skill']['band'] ?? 'N/A'; $aptColor = getBandColor($aptBand);
                
                // Using the new UI slider-thumb bar function matching the image
                $pImg = generateGDProgressBarWithThumb($psyScore, $psyColor);
                $aImg = generateGDProgressBarWithThumb($aptScore, $aptColor);
        ?>
            <tr>
                <td style="border: 1px solid #333; text-align: center; font-weight: bold; padding: 10px; vertical-align: middle;">
                    <?= $prank++ ?>
                </td>
                <td style="border: 1px solid #333; padding: 10px; vertical-align: middle;">
                    <div style="font-weight: bold; color: #000; font-size: 10pt; margin-bottom: 4px;"><?= esc($path['title']) ?></div>
                    <div style="font-size: 8.5pt; color: #000;">
                         <?php
                                $roleDesc = esc($path['roles']);
                                echo (strlen($roleDesc) > 150) ? substr($roleDesc, 0, 150) . '...' : $roleDesc;
                                ?>
                    </div>
                </td>
                <td style="border: 1px solid #333; text-align: center; vertical-align: middle; padding: 10px;">
                    <div style="font-weight: bold; font-size: 9.5pt; color: #000; margin-bottom: 6px;"><?= esc($psyBand) ?>:<?= esc($psyScore) ?></div>
                    <img src="<?= $pImg['src'] ?>" width="10%" height="14" style="display: block; margin: 0 auto;">
                </td>
                <td style="border: 1px solid #333; text-align: center; vertical-align: middle; padding: 10px;">
                    <div style="font-weight: bold; font-size: 9.5pt; color: #000; margin-bottom: 6px;"><?= esc($aptBand) ?>:<?= esc($aptScore) ?></div>
                    <img src="<?= $aImg['src'] ?>" width="10%" height="14" style="display: block; margin: 0 auto;">
                </td>
                <td style="border: 1px solid #333; text-align: center; vertical-align: middle; font-weight: bold; font-size: 10pt; color: <?= getCommentColor($path['comment'] ?? '') ?>;">
                    <?= esc($path['comment'] ?? 'N/A') ?>
                </td>
            </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="5" align="center" style="padding: 20px; border: 1px solid #333;">No career paths generated yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="pagebreak">
    <div class="header-strip">EXECUTION ROADMAPS (TOP 3 CAREERS)</div>
    <?php
    if (!empty($paths)):
        $top3paths = array_slice($paths, 0, 3);
        $roadmapColors = ['#2980b9','#e67e22','#27ae60'];
        foreach ($top3paths as $ridx => $rpath):
            $rcolor  = $roadmapColors[$ridx];
            $roadmap = $rpath['roadmap'] ?? ['stream'=>'Pending','degrees'=>['Pending'],'exams'=>['Pending'],'colleges'=>'Pending'];
    ?>
    <div style="border: 1px solid <?= $rcolor ?>; margin-bottom: 25px; border-radius: 6px; overflow: hidden; page-break-inside: avoid;">
        <table width="100%" cellpadding="0" cellspacing="0" style="border: none;">
            <tr>
                <td style="background: <?= $rcolor ?>; color: #fff; padding: 12px 18px; font-weight: bold; font-size: 11pt;">
                    <table width="100%" style="border: none;"><tr>
                        <td align="left" style="color: white; border: none;">Rank <?= $ridx+1 ?>: <?= esc(explode(' - ', $rpath['title'])[0]) ?></td>
                        <td align="right" style="color: white; border: none;">Match: <?= round($rpath['sort_metric']) ?>%</td>
                    </tr></table>
                </td>
            </tr>
        </table>
        
        <table width="100%" cellpadding="0" cellspacing="0" style="background: #fdfdfd; border: none;">
            <tr>
                <td width="33%" valign="top" style="padding: 16px; border-right: 1px dashed #bdc3c7; border-bottom: 1px dashed #bdc3c7;">
                    <div style="font-size: 8pt; color: #7f8c8d; text-transform: uppercase; font-weight: bold; margin-bottom: 6px;">1. High School (11/12)</div>
                    <div style="font-size: 10pt; color: #2c3e50; font-weight: bold; line-height: 1.4;"><?= esc($roadmap['stream']) ?></div>
                </td>
                <td width="33%" valign="top" style="padding: 16px; border-right: 1px dashed #bdc3c7; border-bottom: 1px dashed #bdc3c7;">
                    <div style="font-size: 8pt; color: #7f8c8d; text-transform: uppercase; font-weight: bold; margin-bottom: 6px;">2. Undergrad Degree</div>
                    <div style="font-size: 10pt; color: #2c3e50; font-weight: bold; line-height: 1.4;"><?= esc(implode(', ', $roadmap['degrees'])) ?></div>
                </td>
                <td width="34%" valign="top" style="padding: 16px; border-bottom: 1px dashed #bdc3c7;">
                    <div style="font-size: 8pt; color: #7f8c8d; text-transform: uppercase; font-weight: bold; margin-bottom: 6px;">3. Target Entrance Exams</div>
                    <div style="font-size: 10pt; color: #2c3e50; font-weight: bold; line-height: 1.4;"><?= esc(implode(' • ', $roadmap['exams'])) ?></div>
                </td>
            </tr>
            <tr>
                <td colspan="3" valign="top" style="padding: 16px; background-color: #fbfcfc; border-bottom: 1px dashed #bdc3c7;">
                    <div style="font-size: 8pt; color: #7f8c8d; text-transform: uppercase; font-weight: bold; margin-bottom: 6px;">
                        4. Target Colleges & Universities (Tier 1 & Tier 2)
                    </div>
                    <div style="font-size: 10.5pt; color: <?= $rcolor ?>; font-weight: bold; line-height: 1.5;">
                        <?= esc($roadmap['colleges']) ?>
                    </div>
                </td>
            </tr>
            
            <?php 
                // SAFE FALLBACK: Prevents crashes on older saved reports that don't have the new AI key yet.
                $aiData = $roadmap['ai_resilience'] ?? [
                    'band'  => 'Analysis Pending',
                    'note'  => 'AI impact metrics are currently being evaluated for this specific role.',
                    'color' => '#7f8c8d' // Neutral Grey
                ];
            ?>
            <tr>
                <td colspan="3" valign="top" style="padding: 16px; background-color: #f4f6f9;">
                    <table width="100%" cellpadding="0" cellspacing="0" style="border: none;">
                        <tr>
                            <td width="30%" valign="top" style="border: none;">
                                <div style="font-size: 8pt; color: #7f8c8d; text-transform: uppercase; font-weight: bold; margin-bottom: 6px;">
                                    5. AI Resilience Score
                                </div>
                                <div style="font-size: 11pt; color: <?= esc($aiData['color']) ?>; font-weight: bold;">
                                    <?= esc($aiData['band']) ?>
                                </div>
                            </td>
                            <td width="70%" valign="top" style="border: none; padding-left: 15px; border-left: 3px solid <?= esc($aiData['color']) ?>;">
                                <div style="font-size: 8pt; color: #7f8c8d; text-transform: uppercase; font-weight: bold; margin-bottom: 4px;">
                                    Future Readiness Analysis
                                </div>
                                <div style="font-size: 9.5pt; color: #34495e; line-height: 1.4;">
                                    <?= esc($aiData['note']) ?>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    <?php endforeach; else: ?>
        <div class="editorial-box"><p>No career path data available.</p></div>
    <?php endif; ?>
</div>


    <?php
    // =========================================================================
    // DYNAMIC COUNSELLOR DEBRIEF
    // Automatically flags Divergent RIASEC profiles, Motivator tensions, or 
    // highly consistent profiles to guide the student's next steps.
    // =========================================================================
    $debrief = $advData['counsellor_debrief'] ?? null;
    
    if ($debrief):
        $isAlert = $debrief['requires_review'] ?? false;
        $boxColor = $isAlert ? '#fdf2e9' : '#ebf5fb';
        $borderColor = $isAlert ? '#e67e22' : '#2980b9';
        $titleColor = $isAlert ? '#d35400' : '#2c3e50';
    ?>
  <div class="pagebreak" style="margin-top: 0px; background-color: <?= $boxColor ?>; border-left: 5px solid <?= $borderColor ?>; border-radius: 4px; padding: 25px;">
        <h3 style="color: <?= $titleColor ?>; margin-top: 0; margin-bottom: 1px; font-size: 14pt; text-transform: uppercase;">
            <img src="<?= FCPATH . 'assets/img/pharos.jpg' ?>" style="height: 18px; vertical-align: middle; margin-right: 8px;" alt="*"> 
            Counsellor's Diagnostic Debrief
        </h3>
        
        <p style="font-size: 11pt; color: #34495e; line-height: 1.6; margin-bottom: <?= !empty($debrief['reasons']) ? '20px' : '0' ?>; margin-top: 0;">
            <?= esc($debrief['summary'] ?? '') ?>
        </p>

        <?php if (!empty($debrief['reasons'])): ?>
            <h4 style="color: #c0392b; font-size: 11pt; margin-bottom: 8px; margin-top: 0; text-transform: uppercase;">Key Diagnostic Flags:</h4>
            <ul style="color: #333; font-size: 10.5pt; line-height: 1.6; margin-top: 0; margin-bottom: 20px; padding-left: 20px;">
                <?php foreach ($debrief['reasons'] as $reason): ?>
                    <li style="margin-bottom: 6px;"><?= esc($reason) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
            
            <?php if (isset($advData['academic_compatibility'])): $envData = $advData['academic_compatibility']; ?>
    <div style="margin-top: 25px; border: 1px solid <?= $envData['color'] ?>; border-radius: 8px; overflow: hidden; page-break-inside: avoid;">
        <table width="100%" cellpadding="0" cellspacing="0" style="border: none;">
            <tr>
                <td style="background: <?= $envData['color'] ?>; color: #fff; padding: 12px 20px; font-weight: bold; font-size: 11pt; text-transform: uppercase;">
                    <img src="<?= FCPATH . 'assets/img/pharos.jpg' ?>" style="height: 16px; vertical-align: middle; margin-right: 8px; filter: brightness(0) invert(1);" alt="*"> 
                    Academic Environment Compatibility Diagnosis
                </td>
            </tr>
            <tr>
                <td style="background: #fdfdfd; padding: 25px 25px 15px 25px;">
                    <h3 style="color: <?= $envData['color'] ?>; font-size: 15pt; margin-top: 0; margin-bottom: 10px;">
                        Recommended Prep Style: <?= esc($envData['band']) ?>
                    </h3>
                    <p style="font-size: 11pt; color: #2c3e50; line-height: 1.6; margin-top: 0; margin-bottom: 20px;">
                        <?= esc($envData['note']) ?>
                    </p>
                    
                    <div style="border-top: 1px solid #ecf0f1; padding-top: 12px;">
                        <p style="font-size: 8.5pt; color: #7f8c8d; line-height: 1.4; margin: 0; font-style: italic;">
                            <strong>*Professional Note:</strong> This compatibility index is derived from Person-Environment Fit (P-E Fit) theory, cross-referencing the candidate's conscientiousness, intrinsic motivators, and emotional regulation against the structural demands of high-stakes competitive examination environments. It is a predictive educational indicator, not a clinical diagnosis.
                        </p>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <?php endif; ?>
            
        <?php if (!empty($debrief['questions_to_ask'])): ?>
            <h4 style="color: #2980b9; font-size: 11pt; margin-bottom: 8px; margin-top: 0; text-transform: uppercase;">Questions to Ask Your Counsellor:</h4>
            <ul style="color: #333; font-size: 10.5pt; line-height: 1.6; margin-top: 0; margin-bottom: 0; padding-left: 20px;">
                <?php foreach ($debrief['questions_to_ask'] as $question): ?>
                    <li style="margin-bottom: 6px; font-style: italic; font-weight: bold;">"<?= esc($question) ?>"</li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <?php endif; ?>


<div class="pagebreak">
    <div class="header-strip" style="font-size: 9pt;">OUR CAREER ASSESSMENT IS BASED ON THE CONCEPT OF CORRELATION THEORY AND VARIOUS PSYCHOMETRIC AND STATISTICAL MODELS.</div>
    <p style="margin-bottom: 20px;">The data presented in this dossier is not absolute but probabilistic. It is calculated using proprietary algorithms mapped to international occupational frameworks (such as O*NET and ISCO-08).</p>
    
    <table class="data-table" width="100%" cellpadding="8" cellspacing="0" style="background-color: #fcfcfc; border-collapse: collapse; border: 1px solid #dddddd;">
        <thead>
            <tr>
                <th width="30%" align="left" style="background-color: #2c3e50; color: #ffffff; border: 1px solid #2c3e50;">Metric Engine</th>
                <th width="70%" align="left" style="background-color: #2c3e50; color: #ffffff; border: 1px solid #2c3e50;">Algorithmic Mapping Logic Applied</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="font-weight: bold; color: #2980b9; border: 1px solid #dddddd;">Career Personality</td>
                <td style="font-family: monospace; color: #555555; border: 1px solid #dddddd;">
                    Engine Result: <strong><?= esc($mbtiPrimaryTrait) ?></strong>
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; color: #2980b9; border: 1px solid #dddddd;">Career Interest</td>
                <td style="color: #555555; border: 1px solid #dddddd;">
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
                <td style="font-weight: bold; color: #2980b9; border: 1px solid #dddddd;">Career Motivator</td>
                <td style="color: #555555; border: 1px solid #dddddd;">Primary Node Identified: <?= esc(array_keys($motivators)[0] ?? 'N/A') ?></td>
            </tr>
            <tr>
                <td style="font-weight: bold; color: #2980b9; border: 1px solid #dddddd;">Learning Style</td>
                <td style="color: #555555; border: 1px solid #dddddd;">Primary Modality: <?= esc(array_keys($learning)[0] ?? 'N/A') ?></td>
            </tr>
            <tr>
                <td style="font-weight: bold; color: #2980b9; border: 1px solid #dddddd;">Skills & Abilities</td>
                <td style="font-size: 8pt; color: #555555; line-height: 1.6; border: 1px solid #dddddd;">
                    Numerical Ability [<?= esc($skills['Numerical Ability']['score'] ?? 0) ?>%] + Logical Ability [<?= esc($skills['Logical Ability']['score'] ?? 0) ?>%] + Verbal Ability [<?= esc($skills['Verbal Ability']['score'] ?? 0) ?>%] + Administrative Skills [<?= esc($skills['Administrative Skills']['score'] ?? 0) ?>%] + Spatial Ability [<?= esc($skills['Spatial Ability']['score'] ?? 0) ?>%] + Leadership Skills [<?= esc($skills['Leadership Skills']['score'] ?? 0) ?>%] + Social Skills [<?= esc($skills['Social Skills']['score'] ?? 0) ?>%] + Mechanical Abilities [<?= esc($skills['Mechanical Abilities']['score'] ?? 0) ?>%]
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; color: #2980b9; border: 1px solid #dddddd;">Selected Clusters</td>
                <td style="color: #555555; border: 1px solid #dddddd;">
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
        </tbody>
    </table>
    <div style="margin-top: 40px; border-top: 2px solid #2c3e50; padding-top: 25px; page-break-inside: avoid;">
        <table width="100%" cellpadding="0" cellspacing="0" style="border: none;">
            <tr>
                <td width="15%" valign="middle" align="left">
                    <?php if (!empty($verification_url)): ?>
                        <barcode code="<?= esc($verification_url) ?>" type="QR" size="1.2" error="M" disableborder="1" style="background-color: #ffffff;" />
                    <?php else: ?>
                        <div style="width: 80px; height: 80px; border: 1px solid #ccc; text-align: center; line-height: 80px; font-size: 8pt; color: #999;">No URL</div>
                    <?php endif; ?>
                </td>
                
                <td width="50%" valign="top" style="padding-left: 15px;">
                    <h4 style="color: #2c3e50; margin-top: 0; margin-bottom: 8px; text-transform: uppercase; font-size: 11pt;">Certification of Authenticity</h4>
                    <p style="font-size: 8.5pt; color: #7f8c8d; line-height: 1.5; margin-bottom: 10px; padding-right: 20px;">
                        This document has been computationally generated and verified by PACE (Pharos AI Career Engine). The psychometric and cognitive aptitude data contained herein is bound to the candidate profile and secured via cryptographic hashing to prevent unauthorized alteration.
                    </p>
                    <div style="font-family: monospace; font-size: 10pt; color: #2980b9; font-weight: bold;">
                        Secure Token: <?= esc($verification_token ?? 'PENDING') ?>
                    </div>
                </td>
                
                <td width="35%" align="right" valign="bottom">
                    <div style="font-family: 'Helvetica', sans-serif; font-size: 24pt; color: #2980b9; margin-bottom: 5px; font-weight: normal; letter-spacing: -1px;">
                        Pharos Education
                    </div>
                    <div style="font-size: 9pt; color: #2c3e50; font-weight: bold; text-transform: uppercase;">Chief Psychometrician</div>
                    <div style="font-size: 8pt; color: #7f8c8d;">Pharos Education Consultancy</div>
                </td>
            </tr>
        </table>
        
        <div style="text-align: center; margin-top: 40px;">
            <h4 style="color: #bdc3c7; margin-bottom: 5px; margin-top: 0; font-size: 10pt; letter-spacing: 2px;">*** END OF OFFICIAL DOSSIER ***</h4>
            <p style="font-size: 8pt; color: #95a5a6; margin-top: 0;">&copy; <?= date('Y') ?> Pharos Education. All rights reserved.</p>
        </div>
    </div>
    
    </div>
    

</body>
</html>
