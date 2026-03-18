<?php if (empty($careers)): ?>
    <div class="col-12 text-center py-5 text-muted">
        <i class="fas fa-folder-open fa-3x mb-3 opacity-50"></i>
        <h5>No careers found in this category</h5>
    </div>
<?php else: ?>
    <?php foreach ($careers as $c): ?>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm transition-hover career-item" style="cursor:pointer; border-left: 4px solid #3b82f6 !important;" data-id="<?= esc($c['id']) ?>" data-title="<?= esc($c['career_title']) ?>">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-1 text-dark"><?= esc($c['career_title'] ?: 'Unknown Title') ?></h6>
                        <small class="text-muted text-truncate d-inline-block" style="max-width: 250px;"><?= esc($c['short_description'] ?: 'No description available.') ?></small>
                    </div>
                    <button class="btn btn-sm btn-light text-primary rounded-circle"><i class="fas fa-arrow-right"></i></button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>