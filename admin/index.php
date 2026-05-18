<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';

$stats = [
    'products'   => (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'orders'     => (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'users'      => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn(),
    'revenue'    => (float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status<>'cancelled'")->fetchColumn(),
];
?>
<h2 class="mb-4"><i class="bi bi-speedometer2"></i> Admin Dashboard</h2>

<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['Products', $stats['products'], 'bi-box-seam', 'primary', 'products.php'],
        ['Orders', $stats['orders'], 'bi-receipt', 'success', 'orders.php'],
        ['Users', $stats['users'], 'bi-people', 'info', 'users.php'],
        ['Revenue', '$' . number_format($stats['revenue'], 2), 'bi-cash-stack', 'warning', 'orders.php'],
    ];
    foreach ($cards as $c): ?>
        <div class="col-md-6 col-lg-3">
            <a href="<?= BASE_URL ?>/admin/<?= $c[4] ?>" class="text-decoration-none">
                <div class="card admin-card text-bg-<?= $c[3] ?>">
                    <div class="card-body d-flex align-items-center">
                        <i class="bi <?= $c[2] ?>" style="font-size:2.5rem;"></i>
                        <div class="ms-3">
                            <div class="small text-uppercase"><?= $c[0] ?></div>
                            <div class="h4 mb-0"><?= $c[1] ?></div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<div class="card admin-card">
    <div class="card-body">
        <h5>Quick Actions</h5>
        <a href="<?= BASE_URL ?>/admin/products.php?action=new" class="btn btn-primary"><i class="bi bi-plus"></i> Add Product</a>
        <a href="<?= BASE_URL ?>/admin/categories.php" class="btn btn-outline-secondary">Manage Categories</a>
        <a href="<?= BASE_URL ?>/admin/orders.php" class="btn btn-outline-secondary">View Orders</a>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>