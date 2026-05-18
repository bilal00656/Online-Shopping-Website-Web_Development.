<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

// ---------- AJAX endpoints ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    if (!is_logged_in()) {
        echo json_encode(['success'=>false,'message'=>'Please log in','redirect'=>BASE_URL.'/login.php']); exit;
    }
    $uid = current_user_id();
    $action = $_POST['action'];

    if ($action === 'add') {
        $pid = (int)$_POST['product_id'];
        $qty = max(1, (int)$_POST['quantity']);
        $prod = $pdo->prepare("SELECT stock FROM products WHERE id=?");
        $prod->execute([$pid]);
        $stock = (int)$prod->fetchColumn();
        if ($stock < $qty) { echo json_encode(['success'=>false,'message'=>'Not enough stock']); exit; }

        $exists = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id=? AND product_id=?");
        $exists->execute([$uid, $pid]);
        $row = $exists->fetch();
        if ($row) {
            $newQty = min($stock, $row['quantity'] + $qty);
            $pdo->prepare("UPDATE cart SET quantity=? WHERE id=?")->execute([$newQty, $row['id']]);
        } else {
            $pdo->prepare("INSERT INTO cart(user_id,product_id,quantity) VALUES(?,?,?)")
                ->execute([$uid, $pid, $qty]);
        }
    } elseif ($action === 'update') {
        $cid = (int)$_POST['cart_id'];
        $qty = max(1, (int)$_POST['quantity']);
        $pdo->prepare("UPDATE cart SET quantity=? WHERE id=? AND user_id=?")->execute([$qty, $cid, $uid]);
    } elseif ($action === 'remove') {
        $cid = (int)$_POST['cart_id'];
        $pdo->prepare("DELETE FROM cart WHERE id=? AND user_id=?")->execute([$cid, $uid]);
    }

    // Recompute totals
    $stmt = $pdo->prepare("SELECT c.id, c.quantity, p.price FROM cart c JOIN products p ON p.id=c.product_id WHERE c.user_id=?");
    $stmt->execute([$uid]);
    $items = $stmt->fetchAll();
    $subtotal = 0; $count = 0; $lineTotal = 0;
    foreach ($items as $i) {
        $subtotal += $i['quantity'] * $i['price'];
        $count    += $i['quantity'];
        if (isset($_POST['cart_id']) && $i['id'] == $_POST['cart_id']) {
            $lineTotal = $i['quantity'] * $i['price'];
        }
    }
    echo json_encode([
        'success'=>true,
        'subtotal'=>number_format($subtotal,2),
        'count'=>$count,
        'line_total'=>number_format($lineTotal,2),
    ]);
    exit;
}

// ---------- Page render ----------
require_login();
$pageTitle = 'Cart';
require_once __DIR__ . '/includes/header.php';

$stmt = $pdo->prepare("SELECT c.id AS cart_id, c.quantity, p.id AS pid, p.name, p.price, p.image, p.stock
                       FROM cart c JOIN products p ON p.id=c.product_id
                       WHERE c.user_id=? ORDER BY c.id DESC");
$stmt->execute([current_user_id()]);
$items = $stmt->fetchAll();
$subtotal = array_sum(array_map(fn($i)=>$i['price']*$i['quantity'], $items));
?>
<h2 class="mb-4"><i class="bi bi-cart3"></i> Shopping Cart</h2>

<?php if (!$items): ?>
    <div class="alert alert-info">Your cart is empty. <a href="<?= BASE_URL ?>/index.php">Continue shopping</a>.</div>
<?php else: ?>
<div class="card admin-card">
    <div class="table-responsive">
    <table class="table mb-0 align-middle">
        <thead><tr>
            <th>Product</th><th>Price</th><th style="width:120px;">Qty</th>
            <th>Total</th><th></th>
        </tr></thead>
        <tbody>
        <?php foreach ($items as $i): ?>
        <tr data-cart-id="<?= $i['cart_id'] ?>">
            <td>
                <a href="<?= BASE_URL ?>/product.php?id=<?= $i['pid'] ?>" class="text-decoration-none text-dark">
                    <?= e($i['name']) ?>
                </a>
            </td>
            <td>$<?= number_format($i['price'],2) ?></td>
            <td>
                <input type="number" class="form-control form-control-sm cart-qty"
                       min="1" max="<?= $i['stock'] ?>" value="<?= $i['quantity'] ?>">
            </td>
            <td class="line-total">$<?= number_format($i['price']*$i['quantity'],2) ?></td>
            <td>
                <button class="btn btn-sm btn-outline-danger remove-cart-btn">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
        <tr>
            <th colspan="3" class="text-end">Subtotal:</th>
            <th>$<span id="cart-subtotal"><?= number_format($subtotal,2) ?></span></th>
            <th></th>
        </tr>
        </tfoot>
    </table>
    </div>
</div>
<div class="text-end mt-3">
    <a href="<?= BASE_URL ?>/index.php" class="btn btn-outline-secondary">Continue Shopping</a>
    <a href="<?= BASE_URL ?>/checkout.php" class="btn btn-success">
        <i class="bi bi-bag-check"></i> Checkout
    </a>
</div>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
