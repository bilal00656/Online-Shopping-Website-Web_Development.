<?php
$pageTitle = 'Shop';
require_once __DIR__ . '/includes/header.php';

// ----- Filters -----
$q          = trim($_GET['q'] ?? '');
$categoryId = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$page       = max(1, (int)($_GET['page'] ?? 1));
$perPage    = 8;
$offset     = ($page - 1) * $perPage;

// ----- Build WHERE -----
$where  = [];
$params = [];
if ($q !== '') {
    $where[] = "(p.name LIKE :q OR p.description LIKE :q)";
    $params[':q'] = "%$q%";
}
if ($categoryId > 0) {
    $where[] = "p.category_id = :cat";
    $params[':cat'] = $categoryId;
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// ----- Total count -----
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p $whereSql");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));

// ----- Products -----
$sql = "SELECT p.*, c.name AS category_name FROM products p
        JOIN categories c ON c.id = p.category_id
        $whereSql ORDER BY p.created_at DESC LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<div class="hero text-center">
    <h1 class="display-5 fw-bold">Welcome to Smart Shop</h1>
    <p class="lead mb-0">Quality products at unbeatable prices.</p>
</div>

<div class="row">
    <!-- Sidebar -->
    <aside class="col-lg-3 mb-4">
        <div class="sidebar-filter">
            <h5 class="mb-3"><i class="bi bi-funnel"></i> Categories</h5>
            <ul class="list-unstyled mb-3">
                <li class="mb-2">
                    <a class="<?= $categoryId === 0 ? 'fw-bold text-primary' : 'text-decoration-none text-dark' ?>"
                        href="?<?= http_build_query(array_filter(['q' => $q])) ?>">All Products</a>
                </li>
                <?php foreach ($categories as $c): ?>
                    <li class="mb-2">
                        <a class="<?= $categoryId === (int)$c['id'] ? 'fw-bold text-primary' : 'text-decoration-none text-dark' ?>"
                            href="?<?= http_build_query(array_filter(['q' => $q, 'cat' => $c['id']])) ?>">
                            <?= e($c['name']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <hr>
            <label class="form-label">Quick filter</label>
            <input type="text" id="client-filter" class="form-control form-control-sm" placeholder="Filter visible...">
        </div>
    </aside>

    <!-- Products -->
    <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">
                <?php if ($q): ?>Results for "<?= e($q) ?>" (<?= $total ?>)
                <?php else: ?>All Products (<?= $total ?>)<?php endif; ?>
            </h4>
        </div>

        <?php if (!$products): ?>
            <div class="alert alert-info">No products found.</div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($products as $p): ?>
                    <div class="col-md-6 col-xl-3 product-card-wrapper" data-name="<?= e($p['name']) ?>">
                        <div class="card product-card">
                            <?php if ($p['image'] && file_exists(__DIR__ . '/assets/uploads/' . $p['image'])): ?>
                                <img src="<?= BASE_URL ?>/assets/uploads/<?= e($p['image']) ?>" class="product-img card-img-top">
                            <?php else: ?>
                                <div class="product-img-placeholder card-img-top"><i class="bi bi-image"></i></div>
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column">
                                <small class="text-muted"><?= e($p['category_name']) ?></small>
                                <h6 class="card-title mb-2"><?= e($p['name']) ?></h6>
                                <div class="price mb-2">$<?= number_format($p['price'], 2) ?></div>
                                <div class="mt-auto d-grid gap-2">
                                    <a href="<?= BASE_URL ?>/product.php?id=<?= $p['id'] ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++):
                            $qs = http_build_query(array_filter(['q' => $q, 'cat' => $categoryId ?: null, 'page' => $i])); ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= $qs ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>