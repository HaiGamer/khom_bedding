<?php
require_once __DIR__ . '/auth-guard.php';
include_once __DIR__ . '/includes/header.php';

// --- PHẦN LOGIC CHUNG VÀ ĐIỀU HƯỚNG ---

// Hàm tiện ích format trạng thái đơn hàng (để dùng trong view chi tiết)
function format_order_status_admin($status) {
    switch ($status) {
        case 'pending': return '<span class="badge bg-warning text-dark">Chờ xử lý</span>';
        case 'processing': return '<span class="badge bg-info text-dark">Đang xử lý</span>';
        case 'shipped': return '<span class="badge bg-primary">Đang giao</span>';
        case 'completed': return '<span class="badge bg-success">Hoàn thành</span>';
        case 'cancelled': return '<span class="badge bg-danger">Đã hủy</span>';
        default: return '<span class="badge bg-secondary">Không xác định</span>';
    }
}

// Lấy action từ URL, mặc định là 'list' (hiển thị danh sách)
$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'view':
        // --- LOGIC CHO VIEW CHI TIẾT ---
        $customer_id = (int)($_GET['id'] ?? 0);
        $stmt_customer = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt_customer->execute([$customer_id]);
        $customer = $stmt_customer->fetch();

        if ($customer) {
            $stmt_orders = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
            $stmt_orders->execute([$customer_id]);
            $orders = $stmt_orders->fetchAll();
            
            // Gọi view chi tiết
            include __DIR__ . '/includes/customer-detail-view.php';
        } else {
            echo '<div class="alert alert-danger">Không tìm thấy khách hàng.</div>';
        }
        break;
    
    default: // Mặc định là action 'list'
        // --- LOGIC CHO VIEW DANH SÁCH (CÓ TÌM KIẾM & PHÂN TRANG) ---
        $results_per_page = 15;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) { $page = 1; }
        $offset = ($page - 1) * $results_per_page;

        $search_term = $_GET['search'] ?? '';
        $where_clause = '';
        $search_params = []; 
        if (!empty($search_term)) {
            $where_clause = "WHERE (full_name LIKE :search OR email LIKE :search)";
            $search_params[':search'] = "%$search_term%";
        }

        $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM users $where_clause");
        $stmt_count->execute($search_params);
        $total_results = $stmt_count->fetchColumn();
        $total_pages = ceil($total_results / $results_per_page);
        
        $sql = "SELECT id, full_name, email, role, created_at FROM users $where_clause ORDER BY id DESC LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        if (!empty($search_params)) {
            $stmt->bindValue(':search', $search_params[':search'], PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $results_per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $customers = $stmt->fetchAll();

        // Gọi view danh sách
        include __DIR__ . '/includes/customer-list-view.php';
        break;
}

include_once __DIR__ . '/includes/footer.php';