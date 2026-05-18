<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $status = $_POST['status'];
    if (in_array($status, ['pending', 'shipped', 'delivered', 'cancelled'])) {
        $pdo->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$status, $id]);
        flash('success', 'Order updated.');
    }
    header('Location: orders.php');
    exit;
}
$pageTitle = 'Orders';
require_once __DIR__ . '/../includes/header.php';

$orders = $pdo->query("SELECT o.*, u.name AS user_name, u.email
                       FROM orders o JOIN users u ON u.id=o.user_id
                       ORDER BY o.created_at DESC")->fetchAll();
?>
<h2 class="mb-4"><i class="bi bi-receipt"></i> All Orders</h2>
<div class="card admin-card">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                    <tr>
                        <td>#<?= $o['id'] ?></td>
                        <td><?= e($o['user_name']) ?><br><small class="text-muted"><?= e($o['email']) ?></small></td>
                        <td>$<?= number_format($o['total'], 2) ?></td>
                        <td><span class="badge badge-status-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
                        <td><small><?= e($o['created_at']) ?></small></td>
                        <td>
                            <form method="post" class="d-flex gap-1">
                                <input type="hidden" name="id" value="<?= $o['id'] ?>">
                                <select name="status" class="form-select form-select-sm">
                                    <?php foreach (['pending', 'shipped', 'delivered', 'cancelled'] as $s): ?>
                                        <option <?= $s == $o['status'] ? 'selected' : '' ?>><?= $s ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-sm btn-primary"><i class="bi bi-check"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>