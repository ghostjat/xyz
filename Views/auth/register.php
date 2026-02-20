<div class="container mt-5 d-flex justify-content-center" id="register" style="display: none;">
    <div class="card shadow p-4" style="width: 500px;">
        <h4 class="text-center mb-3">Create Account</h4>
        <form action="<?= base_url('auth/store') ?>" method="post" class="spa-form">
            <div class="mb-3">
                <label>I am a:</label>
                <select name="role" class="form-control" required>
                    <option value="student">Student</option>
                    <option value="counselor">Counselor</option>
                    <option value="school">School</option>
                    <option value="college">College</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Full Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Register</button>
        </form>
        <div class="text-center mt-3">
            <a href="<?= base_url('login') ?>" class="spa-link">Already have an account? Login</a>
        </div>
    </div>
</div>