<div class="container mt-5 d-flex justify-content-center">
    <div class="card shadow p-4" style="width: 400px;">
        <h4 class="text-center mb-3">Login</h4>
        <form action="<?= base_url('auth/authenticate') ?>" method="post" class="spa-form">
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        <div class="text-center mt-3">
            <a href="<?= base_url('register') ?>" class="spa-link">Create Account</a>
        </div>
    </div>
</div>