<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);
$uploadDir = __DIR__ . '/../assets/uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// ---------- Delete ----------
if ($action === 'delete' && $id) {
    $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
    flash('success','Product deleted.');
    header('Location: products.php'); exit;
}

// ---------- Save (create or update) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $desc  = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $cat   = (int)($_POST['category_id'] ?? 0);
    $pid   = (int)($_POST['id'] ?? 0);
    $imageName = $_POST['existing_image'] ?? null;

    if (!$name || $price <= 0 || $cat <= 0) {
        flash('error', 'Name, price and category are required.');
    } else {
        // handle image upload
        if (!empty($_FILES['image']['name'])) {
            $allowed = ['jpg','jpeg','png','gif','webp'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                flash('error', 'Invalid image format.');
                header('Location: products.php'); exit;
            }
            if ($_FILES['image']['size'] > 3 * 1024 * 1024) {
                flash('error', 'Image too large (max 3 MB).');
                header('Location: products.php'); exit;
            }
            $imageName = uniqid('p_', true) . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
        }

        if ($pid > 0) {
            $pdo->prepare("UPDATE products SET name=?,description=?,price=?,stock=?,category_id=?,image=? WHERE id=?")
                ->execute([$name,$desc,$price,$stock,$cat,$imageName,$pid]);
            flash('success','Product updated.');
        } else {
            $pdo->prepare("INSERT INTO products(name,description,price,stock,category_id,image) VALUES(?,?,?,?,?,?)")
                ->execute([$name,$desc,$price,$stock,$cat,$imageName]);
            flash('success','Product created.');
        }
        header('Location: products.php'); exit;
    }
}

$pageTitle = 'Manage Products';
require_once __DIR__ . '/../includes/header.php';

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// ---------- Form (new/edit) ----------
if ($action === 'new' || $action === 'edit') {
    $p = ['id'=>0,'name'=>'','description'=>'','price'=>'','stock'=>0,'category_id'=>0,'image'=>null];
    if ($action === 'edit' && $id) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
        $stmt->execute([$id]);
        $p = $stmt->fetch() ?: $p;
    }
    ?>
    <h2 class="mb-4"><?= $action==='edit' ? 'Edit' : 'New' ?> Product</h2>
    <div class="card admin-card"><div class="card-body">
    <form method="post" enctype="multipart/form-data" data-validate>
        <input type="hidden" name="id" value="<?= $p['id'] ?>">
        <input type="hidden" name="existing_image" value="<?= e($p['image'] ?? '') ?>">
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" required value="<?= e($p['name']) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select" required>
                    <option value="">-- select --</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $c['id']==$p['category_id']?'selected':'' ?>>
                            <?= e($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4"><?= e($p['description'] ?? '') ?></textarea>
            </div>
            <div class="col-md-3">
                <label class="form-label">Price ($)</label>
                <input type="number" step="0.01" name="price" class="form-control" required value="<?= e((string)$p['price']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Stock</label>
                <input type="number" name="stock" class="form-control" required value="<?= (int)$p['stock'] ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Image (jpg/png/webp, max 3MB)</label>
                <input type="file" name="image" class="form-control" accept="image/*">
                <?php if (!empty($p['image'])): ?>
                    <small class="d-block mt-1">Current: <?= e($p['image']) ?></small>
                <?php endif; ?>
            </div>
        </div>
        <div class="mt-4">
            <button class="btn btn-primary"><i class="bi bi-check2"></i> Save</button>
            <a href="products.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
    </div></div>
    <?php
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// ---------- List ----------
$products = $pdo->query("SELECT p.*, c.name AS cat FROM products p
                         JOIN categories c ON c.id=p.category_id
                         ORDER BY p.id DESC")->fetchAll();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0"><i class="bi bi-box-seam"></i> Products</h2>
    <a href="products.php?action=new" class="btn btn-primary"><i class="bi bi-plus"></i> Add Product</a>
</div>
<div class="card admin-card"><div class="table-responsive">
<table class="table mb-0 align-middle">
    <thead><tr><th>#</th><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($products as $p): ?>
    <tr>
        <td><?= $p['id'] ?></td>
        <td>
            <?php if ($p['image'] && file_exists(__DIR__.'/../assets/uploads/'.$p['image'])): ?>
                <img src="<?= BASE_URL ?>/assets/uploads/<?= e($p['image']) ?>" style="width:50px;height:50px;object-fit:cover;border-radius:4px;">
            <?php else: ?>
                <span class="text-muted"><i class="bi bi-image"></i></span>
            <?php endif; ?>
        </td>
        <td><?= e($p['name']) ?></td>
        <td><?= e($p['cat']) ?></td>
        <td>$<?= number_format($p['price'],2) ?></td>
        <td><?= $p['stock'] ?></td>
        <td>
            <a href="products.php?action=edit&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
            <a href="products.php?action=delete&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger confirm-delete"><i class="bi bi-trash"></i></a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
