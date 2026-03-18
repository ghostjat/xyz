<div class="table-responsive bg-white p-3 rounded shadow-sm border border-light">
    <table id="appointmentsDataTable" class="table table-hover table-bordered align-middle w-100 mb-0">
        <thead class="table-light">
            <tr>
                <th class="text-secondary small text-uppercase">Date & Time</th>
                <th class="text-secondary small text-uppercase">Student</th>
                <th class="text-secondary small text-uppercase">Topic</th>
                <th class="text-secondary small text-uppercase text-center">Status</th>
                <th class="text-secondary small text-uppercase text-end">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($appointments)): ?>
                <tr><td colspan="5" class="text-center py-4 text-muted">No appointments found.</td></tr>
            <?php else: ?>
                <?php foreach($appointments as $apt): ?>
                    <tr>
                        <td class="fw-bold text-dark">
                            <?= date('M d, Y', strtotime($apt['preferred_datetime'])) ?>
                            <div class="text-muted small"><i class="fas fa-clock me-1"></i><?= date('h:i A', strtotime($apt['preferred_datetime'])) ?></div>
                        </td>
                        <td>
                            <div class="fw-bold"><?= esc($apt['student_name']) ?></div>
                            <small class="text-muted"><i class="fas fa-envelope me-1"></i><?= esc($apt['student_email']) ?></small>
                        </td>
                        <td><span class="badge bg-light text-dark border"><?= esc($apt['topic'] ?? 'General Counseling') ?></span></td>
                        <td class="text-center">
                            <?php if($apt['status'] === 'pending'): ?>
                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">Pending</span>
                            <?php elseif($apt['status'] === 'approved'): ?>
                                <span class="badge bg-success px-3 py-2 rounded-pill">Approved</span>
                            <?php else: ?>
                                <span class="badge bg-danger px-3 py-2 rounded-pill">Cancelled</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?php if($apt['status'] === 'pending'): ?>
                                <button class="btn btn-sm btn-success update-apt shadow-sm" data-id="<?= $apt['id'] ?>" data-status="approved" title="Approve"><i class="fas fa-check"></i></button>
                                <button class="btn btn-sm btn-danger update-apt shadow-sm ms-1" data-id="<?= $apt['id'] ?>" data-status="cancelled" title="Reject"><i class="fas fa-times"></i></button>
                            <?php else: ?>
                                <button class="btn btn-sm btn-light text-muted" disabled>Locked</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    // Initialize DataTable safely
    setTimeout(function() {
        if (typeof $.fn.DataTable !== 'undefined' && !$.fn.DataTable.isDataTable('#appointmentsDataTable')) {
            $('#appointmentsDataTable').DataTable({
                "order": [[ 0, "desc" ]],
                "pageLength": 10,
                "language": { "search": "<i class='fas fa-search text-muted'></i> Search Appointments:" }
            });
        }
    }, 100);
</script>