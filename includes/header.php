<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

// Cart count for navbar
$cartCount = 0;
if (is_logged_in()) {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity),0) FROM cart WHERE user_id = ?");
    $stmt->execute([current_user_id()]);
    $cartCount = (int)$stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' - ' : '' ?>Smart E-Commerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/index.php">
            <i class="bi bi-bag-heart-fill text-warning"></i> Smart Shop
        </a>
        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <form class="d-flex me-auto ms-3" method="get" action="<?= BASE_URL ?>/index.php">
                <input class="form-control me-2" type="search" name="q" placeholder="Search products..."
                       value="<?= e($_GET['q'] ?? '') ?>">
                <button class="btn btn-outline-light" type="submit"><i class="bi bi-search"></i></button>
            </form>
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/index.php">Shop</a>
                </li>
                <?php if (is_logged_in()): ?>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="<?= BASE_URL ?>/cart.php">
                            <i class="bi bi-cart3"></i> Cart
                            <span id="cart-count" class="badge bg-warning text-dark rounded-pill">
                                <?= $cartCount ?>
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/orders.php">My Orders</a>
                    </li>
                    <?php if (is_admin()): ?>
                        <li class="nav-item">
                            <a class="nav-link text-warning" href="<?= BASE_URL ?>/admin/index.php">
                                <i class="bi bi-speedometer2"></i> Admin
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= e(current_user_name()) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/orders.php">My Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container my-4">
<?php if ($msg = flash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= e($msg) ?>
        <button class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if ($msg = flash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= e($msg) ?>
        <button class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
