<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    if ($name) {
        if ($id) {
            $pdo->prepare("UPDATE categories SET name=? WHERE id=?")->execute([$name, $id]);
        } else {
            try {
                $pdo->prepare("INSERT INTO categories(name) VALUES(?)")->execute([$name]);
            } catch (PDOException $e) {
                flash('error', 'Category already exists.');
            }
        }
        flash('success', 'Saved.');
    }
    header('Location: categories.php');
    exit;
}
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM categories WHERE id=?")->execute([(int)$_GET['delete']]);
    flash('success', 'Deleted.');
    header('Location: categories.php');
    exit;
}

$pageTitle = 'Categories';
require_once __DIR__ . '/../includes/header.php';
$cats = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id=c.id) AS pcount
                     FROM categories c ORDER BY c.name")->fetchAll();
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}
?>
<h2 class="mb-4"><i class="bi bi-tags"></i> Categories</h2>
<div class="row g-3">
    <div class="col-md-4">
        <div class="card admin-card">
            <div class="card-body">
                <h5><?= $edit ? 'Edit' : 'Add' ?> Category</h5>
                <form method="post">
                    <input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
                    <input type="text" name="name" class="form-control mb-3" required maxlength="100"
                        value="<?= e($edit['name'] ?? '') ?>" placeholder="Category name">
                    <button class="btn btn-primary"><?= $edit ? 'Update' : 'Add' ?></button>
                    <?php if ($edit): ?><a href="categories.php" class="btn btn-secondary">Cancel</a><?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card admin-card">
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Products</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cats as $c): ?>
                            <tr>
                                <td><?= $c['id'] ?></td>
                                <td><?= e($c['name']) ?></td>
                                <td><?= $c['pcount'] ?></td>
                                <td>
                                    <a href="?edit=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                    <a href="?delete=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger confirm-delete"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>