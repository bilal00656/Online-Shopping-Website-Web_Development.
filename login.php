<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) { header('Location: ' . BASE_URL . '/index.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pwd   = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $u = $stmt->fetch();
    if ($u && password_verify($pwd, $u['password'])) {
        $_SESSION['user_id']   = $u['id'];
        $_SESSION['user_name'] = $u['name'];
        $_SESSION['role']      = $u['role'];
        flash('success', 'Welcome back, ' . $u['name'] . '!');
        header('Location: ' . BASE_URL . ($u['role']==='admin' ? '/admin/index.php' : '/index.php'));
        exit;
    }
    flash('error', 'Invalid email or password.');
}
$pageTitle = 'Login';
require_once __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card admin-card">
            <div class="card-body p-4">
                <h3 class="mb-3 text-center"><i class="bi bi-box-arrow-in-right"></i> Login</h3>
                <form method="post" data-validate>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button class="btn btn-primary w-100">Login</button>
                </form>
                <p class="mt-3 text-center small">
                    No account? <a href="<?= BASE_URL ?>/register.php">Register</a>
                </p>
                <hr>
                <small class="text-muted d-block text-center">
                    Demo: <code>admin@shop.com / admin123</code><br>
                    <code>user@shop.com / user123</code>
                </small>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
