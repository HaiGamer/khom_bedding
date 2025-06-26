<?php
require_once __DIR__ . '/auth-guard.php';
include_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../core/functions.php';

// --- XỬ LÝ POST REQUEST (THÊM / SỬA) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_category') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        if (!empty($name)) {
            $slug = generate_slug($name);
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
            $stmt->execute([$name, $slug, $description]);
            header('Location: categories.php?success=added');
            exit();
        }
    }
    
    elseif ($action === 'edit_category') {
        $id = (int)($_POST['category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        if ($id > 0 && !empty($name)) {
            $slug = generate_slug($name);
            $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $slug, $description, $id]);
            header('Location: categories.php?success=edited');
            exit();
        }
    }
}

// --- ĐIỀU HƯỚNG VIEW VÀ XỬ LÝ GET REQUEST (XEM DANH SÁCH / XÓA) ---
$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'add':
        $is_editing = false;
        include __DIR__ . '/includes/category-form-view.php';
        break;

    case 'edit':
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $category = $stmt->fetch();
        if ($category) {
            $is_editing = true;
            include __DIR__ . '/includes/category-form-view.php';
        } else {
            header('Location: categories.php?error=not_found');
        }
        break;

    case 'delete':
        $id = (int)($_GET['id'] ?? 0);
        // Kiểm tra an toàn: danh mục có sản phẩm nào không?
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $stmt_check->execute([$id]);
        if ($stmt_check->fetchColumn() > 0) {
            header('Location: categories.php?error=in_use');
        } else {
            $stmt_delete = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt_delete->execute([$id]);
            header('Location: categories.php?success=deleted');
        }
        exit();

    default: // 'list'
        $stmt = $pdo->prepare("
            SELECT c.*, COUNT(p.id) as product_count 
            FROM categories c 
            LEFT JOIN products p ON c.id = p.category_id 
            GROUP BY c.id ORDER BY c.name ASC
        ");
        $stmt->execute();
        $categories = $stmt->fetchAll();
        include __DIR__ . '/includes/category-list-view.php';
        break;
}

include_once __DIR__ . '/includes/footer.php';