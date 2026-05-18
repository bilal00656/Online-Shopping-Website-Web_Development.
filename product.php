<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p
                       JOIN categories c ON c.id=p.category_id WHERE p.id=?");
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) {
    flash('error', 'Product not found');
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}
$pageTitle = $p['name'];
require_once __DIR__ . '/includes/header.php';
?>
<div class="row g-4">
    <div class="col-md-5">
        <div class="card">
            <?php if ($p['image'] && file_exists(__DIR__ . '/assets/uploads/' . $p['image'])): ?>
                <img src="<?= BASE_URL ?>/assets/uploads/<?= e($p['image']) ?>" class="card-img-top" style="max-height:420px; object-fit:cover;">
            <?php else: ?>
                <div class="product-img-placeholder card-img-top" style="height:420px;font-size:6rem;"><i class="bi bi-image"></i></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-7">
        <small class="text-muted"><?= e($p['category_name']) ?></small>
        <h2 class="mb-2"><?= e($p['name']) ?></h2>
        <div class="price h3 mb-3">$<?= number_format($p['price'], 2) ?></div>
        <p><?= nl2br(e($p['description'] ?? '')) ?></p>
        <p>
            <?php if ($p['stock'] > 0): ?>
                <span class="badge bg-success">In stock: <?= $p['stock'] ?></span>
            <?php else: ?>
                <span class="badge bg-danger">Out of stock</span>
            <?php endif; ?>
        </p>

        <?php if ($p['stock'] > 0): ?>
            <div class="d-flex align-items-center gap-2 mb-3" style="max-width:280px;">
                <label class="form-label mb-0">Qty:</label>
                <input id="qty-input" type="number" min="1" max="<?= $p['stock'] ?>" value="1" class="form-control form-control-sm">
            </div>
            <button class="btn btn-primary add-to-cart-btn" data-id="<?= $p['id'] ?>">
                <i class="bi bi-cart-plus"></i> Add to Cart
            </button>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>