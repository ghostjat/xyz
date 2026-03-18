<div class="table-responsive bg-white p-3 rounded shadow-sm border border-light">
    <table id="candidatesDataTable" class="table table-hover table-bordered align-middle w-100 mb-0">
        <thead class="table-light">
            <tr>
                <th class="text-secondary small text-uppercase">Student Info</th>
                <th class="text-secondary small text-uppercase">Contact</th>
                <th class="text-secondary small text-uppercase">Edu Level</th>
                <th class="text-secondary small text-uppercase text-center">Tests Taken</th>
                <th class="text-secondary small text-uppercase">Joined</th>
                <th class="text-secondary small text-uppercase text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($candidates as $c): ?>
                <tr>
                    <td>
                        <div class="fw-bold text-dark"><?= esc($c['full_name']) ?></div>
                        <small class="text-muted">ID: #<?= esc($c['id']) ?></small>
                    </td>
                    <td>
                        <div><i class="fas fa-envelope text-muted me-1"></i><?= esc($c['email']) ?></div>
                        <?php if (!empty($c['phone'])): ?>
                            <small><i class="fas fa-phone text-muted me-1"></i><?= esc($c['phone']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge bg-light text-dark border"><?= esc($c['educational_level'] ?? 'N/A') ?></span></td>
                    <td class="text-center">
                        <?php $tc = $testCounts[$c['id']] ?? 0; ?>
                        <span class="badge <?= $tc >= 6 ? 'bg-success' : 'bg-warning text-dark' ?> rounded-pill px-3">
                            <?= $tc ?> / 6
                        </span>
                    </td>
                    <td><small><?= date('M d, Y', strtotime($c['created_at'])) ?></small></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-primary text-white open-crm-btn shadow-sm me-1" data-id="<?= $c['id'] ?>" data-name="<?= esc($c['full_name']) ?>" title="Open CRM Workspace">
                            <i class="fas fa-address-book me-1"></i> CRM
                        </button>
                        <button class="btn btn-sm btn-info text-white view-scores-btn shadow-sm" data-id="<?= $c['id'] ?>" data-name="<?= esc($c['full_name']) ?>" title="View Raw Scores">
                            <i class="fas fa-chart-bar me-1"></i> Raw Scores
                        </button>
                        <?php if ($tc > 0): ?>
                            <a href="<?= base_url('report/viewReport/' . $c['id']) ?>" target="_blank" class="btn btn-sm btn-danger shadow-sm ms-1" title="View Full Dossier">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    // Initialize DataTable on this newly injected HTML
    $(document).ready(function () {
        if (!$.fn.DataTable.isDataTable('#candidatesDataTable')) {
            $('#candidatesDataTable').DataTable({
                "order": [[4, "desc"]], // Sort by Joined Date originally
                "pageLength": 25,
                "language": {"search": "<i class='fas fa-search text-muted'></i> Search:"}
            });
        }
    });
</script>