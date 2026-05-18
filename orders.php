<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_login();
$pageTitle = 'My Orders';
require_once __DIR__ . '/includes/header.php';

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([current_user_id()]);
$orders = $stmt->fetchAll();
?>
<h2 class="mb-4"><i class="bi bi-receipt"></i> My Orders</h2>

<?php if (!$orders): ?>
    <div class="alert alert-info">You haven't placed any orders yet.</div>
<?php else: ?>
    <?php foreach ($orders as $o):
        $items = $pdo->prepare("SELECT oi.*, p.name FROM order_items oi
                                JOIN products p ON p.id=oi.product_id WHERE oi.order_id=?");
        $items->execute([$o['id']]);
        $items = $items->fetchAll();
    ?>
    <div class="card admin-card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between flex-wrap mb-2">
                <h5 class="mb-0">Order #<?= $o['id'] ?></h5>
                <span class="badge badge-status-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span>
            </div>
            <small class="text-muted">Placed on <?= e($o['created_at']) ?></small>
            <hr>
            <ul class="list-unstyled mb-2">
            <?php foreach ($items as $it): ?>
                <li><?= e($it['name']) ?> &times; <?= $it['quantity'] ?> — $<?= number_format($it['price']*$it['quantity'],2) ?></li>
            <?php endforeach; ?>
            </ul>
            <div class="d-flex justify-content-between">
                <small class="text-muted">Ship to: <?= e($o['address']) ?> (<?= e($o['phone']) ?>)</small>
                <strong>Total: $<?= number_format($o['total'],2) ?></strong>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
