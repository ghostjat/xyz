<?php if (empty($results)): ?>
    <div class="alert alert-info text-center py-4 border-0">
        <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
        This candidate has not completed any assessments yet.
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-sm mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Module</th>
                    <th>Primary Trait</th>
                    <th>Completed On</th>
                    <th>Raw Output Data (JSON extract)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($results as $r): ?>
                    <tr>
                        <td class="fw-bold text-uppercase align-middle"><?= esc($r['module_code']) ?></td>
                        <td class="align-middle"><span class="badge bg-primary px-2 py-1"><?= esc($r['primary_trait'] ?? 'N/A') ?></span></td>
                        <td class="align-middle text-muted small"><?= date('d M Y, h:i A', strtotime($r['completed_at'])) ?></td>
                        <td>
                            <div style="max-height: 150px; overflow-y: auto; font-size: 0.75rem; background: #212529; color: #10b981; padding: 10px; border-radius: 4px; font-family: monospace;">
                                <?php 
                                    // Make the JSON pretty
                                    $decoded = json_decode($r['result_json']);
                                    echo esc(json_encode($decoded, JSON_PRETTY_PRINT)); 
                                ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>