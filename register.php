<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) { header('Location: ' . BASE_URL . '/index.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pwd   = $_POST['password'] ?? '';
    $cpwd  = $_POST['confirm_password'] ?? '';

    if (!$name || !$email || !$pwd) {
        flash('error', 'All fields are required.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Invalid email address.');
    } elseif (strlen($pwd) < 6) {
        flash('error', 'Password must be at least 6 characters.');
    } elseif ($pwd !== $cpwd) {
        flash('error', 'Passwords do not match.');
    } else {
        $exists = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $exists->execute([$email]);
        if ($exists->fetch()) {
            flash('error', 'Email is already registered.');
        } else {
            $hash = password_hash($pwd, PASSWORD_DEFAULT);
            $ins = $pdo->prepare("INSERT INTO users(name,email,password,role) VALUES(?,?,?,'user')");
            $ins->execute([$name, $email, $hash]);
            flash('success', 'Account created. You can now log in.');
            header('Location: ' . BASE_URL . '/login.php'); exit;
        }
    }
}
$pageTitle = 'Register';
require_once __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card admin-card">
            <div class="card-body p-4">
                <h3 class="mb-3 text-center"><i class="bi bi-person-plus"></i> Create Account</h3>
                <form method="post" data-validate>
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required maxlength="150">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" required minlength="6">
                    </div>
                    <button class="btn btn-primary w-100">Register</button>
                </form>
                <p class="mt-3 text-center small">
                    Already have an account? <a href="<?= BASE_URL ?>/login.php">Login</a>
                </p>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
