<?php
require_once __DIR__ . '/auth-guard.php';
include_once __DIR__ . '/includes/header.php';

// --- XỬ LÝ POST REQUEST (CHỈ CÓ SỬA) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'edit_review') {
        $id = (int)($_POST['review_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');

        if ($id > 0 && $rating >= 1 && $rating <= 5 && !empty($comment)) {
            $stmt = $pdo->prepare("UPDATE reviews SET rating = ?, comment = ? WHERE id = ?");
            $stmt->execute([$rating, $comment, $id]);
            header('Location: reviews.php?success=edited');
            exit();
        } else {
            header('Location: reviews.php?action=edit&id='.$id.'&error=invalid_data');
            exit();
        }
    }
}

// --- ĐIỀU HƯỚNG VIEW VÀ XỬ LÝ GET REQUEST (XEM DANH SÁCH / XÓA) ---
$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'edit':
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("
            SELECT r.*, p.name as product_name, p.slug as product_slug, u.full_name as user_name 
            FROM reviews r
            JOIN products p ON r.product_id = p.id
            JOIN users u ON r.user_id = u.id
            WHERE r.id = ?
        ");
        $stmt->execute([$id]);
        $review = $stmt->fetch();
        if ($review) {
            include __DIR__ . '/includes/review-edit-view.php';
        } else {
            header('Location: reviews.php?error=not_found');
        }
        break;
    
    case 'delete':
        $id = (int)($_GET['id'] ?? 0);
        $stmt_delete = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt_delete->execute([$id]);
        header('Location: reviews.php?success=deleted');
        exit();

    default: // 'list'
        $stmt = $pdo->prepare("
            SELECT r.*, p.name as product_name, p.slug as product_slug, u.full_name as user_name 
            FROM reviews r
            JOIN products p ON r.product_id = p.id
            JOIN users u ON r.user_id = u.id
            ORDER BY r.created_at DESC
        ");
        $stmt->execute();
        $reviews = $stmt->fetchAll();
        include __DIR__ . '/includes/review-list-view.php';
        break;
}

include_once __DIR__ . '/includes/footer.php';