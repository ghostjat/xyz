<?php
// Intelligent Icon Mapper
$iconMap = [
    'agriculture' => 'fas fa-leaf',
    'environment' => 'fas fa-seedling',
    'architecture' => 'fas fa-building',
    'construction' => 'fas fa-hard-hat',
    'arts' => 'fas fa-palette',
    'design' => 'fas fa-paint-brush',
    'business' => 'fas fa-briefcase',
    'education' => 'fas fa-chalkboard-teacher',
    'finance' => 'fas fa-chart-line',
    'accounts' => 'fas fa-coins',
    'government' => 'fas fa-landmark',
    'public' => 'fas fa-university',
    'health' => 'fas fa-heartbeat',
    'hospitality' => 'fas fa-concierge-bell',
    'tourism' => 'fas fa-plane-departure',
    'human service' => 'fas fa-hands-helping',
    'social' => 'fas fa-users',
    'information technology' => 'fas fa-laptop-code',
    'law' => 'fas fa-balance-scale',
    'legal' => 'fas fa-gavel',
    'manufacturing' => 'fas fa-industry',
    'marketing' => 'fas fa-bullhorn',
    'advertising' => 'fas fa-ad',
    'science' => 'fas fa-flask',
    'bio' => 'fas fa-dna',
    'engineering' => 'fas fa-cogs',
    'sports' => 'fas fa-running',
    'transportation' => 'fas fa-truck',
    'logistics' => 'fas fa-shipping-fast',
    'security' => 'fas fa-shield-alt'
];

function getClusterIcon($categoryName, $iconMap) {
    $nameLower = strtolower($categoryName);
    foreach ($iconMap as $keyword => $iconClass) {
        if (strpos($nameLower, $keyword) !== false) {
            return $iconClass;
        }
    }
    return 'fas fa-layer-group'; // Fallback icon
}
?>

<?php if (empty($clusters)): ?>
    <div class="col-12 text-center py-5">
        <h5 class="text-muted">No categories found in the database.</h5>
    </div>
<?php else: ?>

    <style>
        .cluster-card { 
            cursor: pointer; 
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out; 
        }
        .cluster-card:hover { 
            transform: translateY(-4px); 
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important; 
        }
        .icon-wrapper { 
            width: 60px; 
            height: 60px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
        }
    </style>

    <div class="row g-4">
        <?php foreach ($clusters as $c): ?>
            <?php 
                // Context-aware XSS escaping
                $catName = esc($c['career_category'] ?: 'Uncategorized');
                $safeNameAttr = esc($c['career_category'] ?: 'Uncategorized', 'attr');
                $safeIdAttr = esc($c['id'] ?? '', 'attr'); 
                
                $icon = getClusterIcon($c['career_category'], $iconMap);
            ?>
            <div class="col-md-4 col-lg-3">
                <div class="card h-100 border-0 shadow-sm cluster-card text-center py-4" 
                     data-id="<?= $safeIdAttr ?>" 
                     data-category="<?= $safeNameAttr ?>" 
                     role="button" 
                     tabindex="0">
                    
                    <div class="card-body">
                        <div class="icon-wrapper bg-primary-subtle text-primary mx-auto mb-3 rounded-circle fs-3">
                            <i class="<?= esc($icon, 'attr') ?>"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1"><?= $catName ?></h6>
                        <span class="badge bg-secondary rounded-pill">
                            <?= esc($c['total_careers']) ?> Careers
                        </span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>