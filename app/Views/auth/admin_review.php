<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Review Registration - Pharos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .review-card { max-width: 600px; margin: 60px auto; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border-radius: 12px; }
        .card-header { background-color: #2c3e50; color: white; border-radius: 12px 12px 0 0 !important; padding: 20px; }
        .data-label { color: #7f8c8d; font-size: 0.9rem; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }
        .data-val { color: #2c3e50; font-size: 1.1rem; font-weight: 600; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="container">
    <div class="card review-card">
        <div class="card-header text-center">
            <h4 class="mb-0"><i class="fas fa-user-shield me-2"></i> User Verification Required</h4>
        </div>
        <div class="card-body p-5">
            
            <?php if($user['is_active'] == 1): ?>
                <div class="alert alert-success text-center fw-bold">
                    <i class="fas fa-check-circle me-2"></i> This user is already approved.
                </div>
            <?php else: ?>
                <div class="alert alert-warning text-center fw-bold mb-4">
                    <i class="fas fa-clock me-2"></i> Pending Approval
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="data-label">Full Name</div>
                    <div class="data-val"><?= esc($user['full_name']) ?></div>
                </div>
                <div class="col-md-6">
                    <div class="data-label">Username</div>
                    <div class="data-val"><?= esc($user['username']) ?></div>
                </div>
                <div class="col-md-6">
                    <div class="data-label">Email Address</div>
                    <div class="data-val"><a href="mailto:<?= esc($user['email']) ?>"><?= esc($user['email']) ?></a></div>
                </div>
                <div class="col-md-6">
                    <div class="data-label">Phone Number</div>
                    <div class="data-val"><?= esc($user['phone']) ?></div>
                </div>
            </div>

            <?php if($user['is_active'] == 0): ?>
            <hr class="my-4">
            <form action="<?= base_url('auth/process_review') ?>" method="POST" class="d-flex justify-content-between">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= esc($user['id']) ?>">
                <input type="hidden" name="token" value="<?= esc($token) ?>">
                
                <button type="submit" name="action" value="reject" class="btn btn-outline-danger px-4 py-2 fw-bold" onclick="return confirm('Are you sure you want to completely delete this user?');">
                    <i class="fas fa-trash-alt me-2"></i> Reject & Delete
                </button>
                
                <button type="submit" name="action" value="approve" class="btn btn-success px-4 py-2 fw-bold shadow-sm">
                    <i class="fas fa-check me-2"></i> Approve User
                </button>
            </form>
            <?php endif; ?>

        </div>
    </div>
</div>

</body>
</html>