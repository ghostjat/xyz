<tbody>
            <?php if(empty($candidates)): ?>
                <tr><td colspan="4" class="text-center py-4 text-muted">No completed student profiles available.</td></tr>
            <?php else: ?>
                <?php 
                    $db = \Config\Database::connect();
                    foreach($candidates as $c): 
                        // Fetch Status (Ideally this should be joined in the controller for performance, but placed here for brevity)
                        $meta = $db->table('student_reports')->where('student_id', $c['id'])->get()->getRowArray();
                        $status = $meta ? $meta['status'] : 'generated';
                        
                        $badgeColor = [
                            'generated' => 'bg-secondary',
                            'reviewed' => 'bg-warning text-dark',
                            'sent' => 'bg-success',
                            'discussed' => 'bg-primary'
                        ][$status];
                ?>
                    <tr>
                        <td>
                            <div class="fw-bold text-dark"><?= esc($c['full_name']) ?></div>
                            <small class="text-muted">ID: #<?= esc($c['id']) ?></small>
                        </td>
                        <td><i class="fas fa-envelope text-muted me-1"></i> <?= esc($c['email']) ?></td>
                        <td>
                            <span class="badge <?= $badgeColor ?> rounded-pill px-3 py-2 text-uppercase" style="font-size: 0.7rem;">
                                <?= esc(ucfirst($status)) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-dark shadow-sm open-remarks-btn" data-id="<?= $c['id'] ?>" data-name="<?= esc($c['full_name']) ?>" title="Add Remarks & Status">
                                <i class="fas fa-edit"></i> Remarks
                            </button>
                            <button class="btn btn-sm btn-outline-primary shadow-sm email-report-btn ms-1" data-id="<?= $c['id'] ?>" title="Email PDF to Student">
                                <i class="fas fa-paper-plane"></i> Email
                            </button>
                            <a href="<?= base_url('cca/viewStudentReport/' . $c['id']) ?>" target="_blank" class="btn btn-sm btn-danger shadow-sm ms-1" title="Open Dossier">
                                <i class="fas fa-file-pdf"></i> View
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>