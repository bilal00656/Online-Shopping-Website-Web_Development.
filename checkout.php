<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$uid = current_user_id();
$stmt = $pdo->prepare("SELECT c.quantity, p.id AS pid, p.name, p.price, p.stock
                       FROM cart c JOIN products p ON p.id=c.product_id WHERE c.user_id=?");
$stmt->execute([$uid]);
$items = $stmt->fetchAll();

if (!$items) {
    flash('error', 'Your cart is empty.');
    header('Location: ' . BASE_URL . '/cart.php'); exit;
}
$subtotal = array_sum(array_map(fn($i)=>$i['price']*$i['quantity'], $items));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['address'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    if (!$address || !$phone) {
        flash('error', 'Address and phone are required.');
    } else {
        try {
            $pdo->beginTransaction();
            // Stock re-check
            foreach ($items as $i) {
                if ($i['stock'] < $i['quantity']) {
                    throw new Exception("Not enough stock for {$i['name']}");
                }
            }
            $pdo->prepare("INSERT INTO orders(user_id,total,address,phone) VALUES(?,?,?,?)")
                ->execute([$uid, $subtotal, $address, $phone]);
            $orderId = $pdo->lastInsertId();
            $insItem = $pdo->prepare("INSERT INTO order_items(order_id,product_id,quantity,price) VALUES(?,?,?,?)");
            $decStock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            foreach ($items as $i) {
                $insItem->execute([$orderId, $i['pid'], $i['quantity'], $i['price']]);
                $decStock->execute([$i['quantity'], $i['pid']]);
            }
            $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$uid]);
            $pdo->commit();
            flash('success', "Order #$orderId placed successfully!");
            header('Location: ' . BASE_URL . '/orders.php'); exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            flash('error', 'Order failed: ' . $e->getMessage());
        }
    }
}

$pageTitle = 'Checkout';
require_once __DIR__ . '/includes/header.php';
?>
<h2 class="mb-4"><i class="bi bi-bag-check"></i> Checkout</h2>
<div class="row g-4">
    <div class="col-md-7">
        <div class="card admin-card">
            <div class="card-body">
                <h5 class="mb-3">Shipping Details</h5>
                <form method="post" data-validate>
                    <div class="mb-3">
                        <label class="form-label">Shipping Address</label>
                        <textarea name="address" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" required maxlength="30">
                    </div>
                    <button class="btn btn-success w-100"><i class="bi bi-check2-circle"></i> Place Order</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card admin-card">
            <div class="card-body">
                <h5 class="mb-3">Order Summary</h5>
                <ul class="list-group list-group-flush mb-3">
                <?php foreach ($items as $i): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><?= e($i['name']) ?> &times; <?= $i['quantity'] ?></span>
                        <strong>$<?= number_format($i['price']*$i['quantity'],2) ?></strong>
                    </li>
                <?php endforeach; ?>
                </ul>
                <h5 class="d-flex justify-content-between">
                    <span>Total:</span><span class="text-success">$<?= number_format($subtotal,2) ?></span>
                </h5>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
