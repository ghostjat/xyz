<!DOCTYPE html>
<html lang="en">
<head>
    <title><?= strtoupper($module) ?> Assessment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .question-card { display: none; } /* Hide all initially */
        .question-card.active { display: block; animation: fadeIn 0.5s; }
        @keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow-lg border-0">
        <div class="card-header bg-dark text-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0 fw-bold"><?= strtoupper($module) ?> Assessment</h4>
                <span class="badge bg-warning text-dark">Step <span id="currStep">1</span> of <?= count($questions) ?></span>
            </div>
            <div class="progress mt-3" style="height: 4px;">
                <div class="progress-bar bg-success" id="progressBar" style="width: 0%"></div>
            </div>
        </div>
        
        <div class="card-body p-5">
            <form id="testForm">
                <input type="hidden" name="module_code" value="<?= $module ?>">
                
                <?php foreach($questions as $index => $q): 
                    $options = $q['options_json'] ? json_decode($q['options_json'], true) : null;
                ?>
                
                <div class="question-card" data-index="<?= $index ?>" style="display: <?= $index === 0 ? 'block' : 'none' ?>;">
                    
                    <h3 class="fw-light text-center mb-5"><?= $q['question_text'] ?></h3>
                    
                    <div class="row justify-content-center">
                        <div class="col-md-10">
                            
                            <?php if($q['input_type'] == 'forced_choice'): ?>
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <input type="radio" class="btn-check" name="answers[<?= $q['id'] ?>]" id="q<?= $q['id'] ?>_a" value="<?= $options['a']['val'] ?>" required>
                                        <label class="btn btn-outline-primary w-100 p-4 h-100 d-flex align-items-center justify-content-center shadow-sm" for="q<?= $q['id'] ?>_a">
                                            <span class="fs-5"><?= $options['a']['text'] ?></span>
                                        </label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="radio" class="btn-check" name="answers[<?= $q['id'] ?>]" id="q<?= $q['id'] ?>_b" value="<?= $options['b']['val'] ?>" required>
                                        <label class="btn btn-outline-primary w-100 p-4 h-100 d-flex align-items-center justify-content-center shadow-sm" for="q<?= $q['id'] ?>_b">
                                            <span class="fs-5"><?= $options['b']['text'] ?></span>
                                        </label>
                                    </div>
                                </div>

                            <?php elseif($q['input_type'] == 'likert_3'): ?>
                                <div class="d-flex justify-content-center gap-3">
                                    <input type="radio" class="btn-check" name="answers[<?= $q['id'] ?>]" id="q<?= $q['id'] ?>_dislike" value="0" required>
                                    <label class="btn btn-outline-danger px-4 py-3" for="q<?= $q['id'] ?>_dislike"><i class="bi bi-hand-thumbs-down"></i> Dislike</label>

                                    <input type="radio" class="btn-check" name="answers[<?= $q['id'] ?>]" id="q<?= $q['id'] ?>_neutral" value="1" required>
                                    <label class="btn btn-outline-secondary px-4 py-3" for="q<?= $q['id'] ?>_neutral">Neutral</label>

                                    <input type="radio" class="btn-check" name="answers[<?= $q['id'] ?>]" id="q<?= $q['id'] ?>_like" value="2" required>
                                    <label class="btn btn-outline-success px-4 py-3" for="q<?= $q['id'] ?>_like"><i class="bi bi-hand-thumbs-up"></i> Like</label>
                                </div>

                            <?php else: ?>
                                <div class="d-flex justify-content-between text-muted small px-2 mb-2">
                                    <span>Strongly Disagree</span>
                                    <span>Strongly Agree</span>
                                </div>
                                <div class="d-flex justify-content-between bg-light p-3 rounded">
                                    <?php for($i=1; $i<=5; $i++): ?>
                                    <div class="form-check text-center">
                                        <input class="form-check-input" type="radio" name="answers[<?= $q['id'] ?>]" id="q<?= $q['id'] ?>_<?= $i ?>" value="<?= $i ?>" required style="transform: scale(1.3);">
                                        <label class="d-block mt-2 fw-bold" for="q<?= $q['id'] ?>_<?= $i ?>"><?= $i ?></label>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>

                    <div class="mt-5 d-flex justify-content-between">
                        <button type="button" class="btn btn-link text-muted prev-btn text-decoration-none" <?= $index==0 ? 'disabled':'' ?>>
                            &larr; Previous
                        </button>
                        <button type="button" class="btn btn-dark px-5 next-btn">
                            <?= ($index < count($questions)-1) ? 'Next Question' : 'Finish Assessment' ?>
                        </button>
                    </div>

                </div>
                <?php endforeach; ?>
            </form>
        </div>
    </div>
</div>

<script>
$(function() {
    let currentStep = 0;
    const totalSteps = <?= count($questions) ?>;
    const $questions = $('.question-card');

    // Auto-advance for Forced Choice (Better UX)
    $('input[type=radio]').change(function() {
        // If it's forced choice, wait 300ms then auto-click next
        if($(this).closest('.row').find('.btn-outline-primary').length > 0) {
            setTimeout(() => {
                if(currentStep < totalSteps - 1) $('.next-btn').eq(currentStep).click();
            }, 300);
        }
    });

    $('.next-btn').click(function() {
        let currentQ = $questions.eq(currentStep);
        if (currentQ.find('input:checked').length === 0) {
            alert('Please select an option.');
            return;
        }

        // If last question, submit
        if(currentStep === totalSteps - 1) {
            $('#testForm').submit();
            return;
        }

        currentQ.fadeOut(200, function() {
            currentStep++;
            $questions.eq(currentStep).fadeIn(200);
            updateProgress();
        });
    });

    $('.prev-btn').click(function() {
        $questions.eq(currentStep).fadeOut(200, function() {
            currentStep--;
            $questions.eq(currentStep).fadeIn(200);
            updateProgress();
        });
    });

    function updateProgress() {
        let pct = ((currentStep + 1) / totalSteps) * 100;
        $('#progressBar').css('width', pct + '%');
        $('#currStep').text(currentStep + 1);
    }

    $('#testForm').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: '<?= base_url("tests/submit") ?>',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if(res.status === 'success') window.location.href = res.redirect;
            }
        });
    });
});
</script>
</body>
</html>