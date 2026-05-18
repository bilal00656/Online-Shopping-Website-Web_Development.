<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id !== current_user_id()) {
        $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
        flash('success','User deleted.');
    } else {
        flash('error','You cannot delete yourself.');
    }
    header('Location: users.php'); exit;
}
$pageTitle = 'Users';
require_once __DIR__ . '/../includes/header.php';
$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
?>
<h2 class="mb-4"><i class="bi bi-people"></i> Users</h2>
<div class="card admin-card"><div class="table-responsive">
<table class="table mb-0 align-middle">
    <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
    <tr>
        <td><?= $u['id'] ?></td>
        <td><?= e($u['name']) ?></td>
        <td><?= e($u['email']) ?></td>
        <td>
            <span class="badge bg-<?= $u['role']==='admin'?'warning text-dark':'secondary' ?>">
                <?= e($u['role']) ?>
            </span>
        </td>
        <td><small><?= e($u['created_at']) ?></small></td>
        <td>
            <?php if ($u['id'] !== current_user_id()): ?>
            <a href="?delete=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger confirm-delete">
                <i class="bi bi-trash"></i>
            </a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
