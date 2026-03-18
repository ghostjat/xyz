<div class="table-responsive bg-white p-3 rounded shadow-sm border border-light">
    <table id="reportsDataTable" class="table table-hover table-bordered align-middle w-100 mb-0">
        <thead class="table-dark">
            <tr>
                <th class="text-uppercase small">Student Name</th>
                <th class="text-uppercase small">Contact Email</th>
                <th class="text-uppercase small">Status</th>
                <th class="text-uppercase small text-end">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($candidates)): ?>
                <tr><td colspan="4" class="text-center py-4 text-muted">No completed student profiles available.</td></tr>
            <?php else: ?>
                <?php foreach($candidates as $c): ?>
                    <tr>
                        <td class="fw-bold text-dark"><?= esc($c['full_name']) ?></td>
                        <td><i class="fas fa-envelope text-muted me-1"></i> <?= esc($c['email']) ?></td>
                        <td><span class="badge bg-success rounded-pill px-3">Ready for Review</span></td>
                        <td class="text-end">
                            <a href="<?= base_url('cca/viewStudentReport/' . $c['id']) ?>" target="_blank" class="btn btn-sm btn-primary shadow-sm">
                                <i class="fas fa-external-link-alt me-1"></i> Open Dossier
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    // Initialize DataTables
    setTimeout(function() {
        if (typeof $.fn.DataTable !== 'undefined' && !$.fn.DataTable.isDataTable('#reportsDataTable')) {
            $('#reportsDataTable').DataTable({
                "pageLength": 25,
                "language": { "search": "<i class='fas fa-search text-muted'></i> Search:" }
            });
        }
    }, 100);
</script>