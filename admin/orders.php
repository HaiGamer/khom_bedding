<?php
require_once __DIR__ . '/auth-guard.php';
include_once __DIR__ . '/includes/header.php';

// --- PHẦN LOGIC XỬ LÝ POST REQUEST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'update_status') {
        $order_id = (int)($_POST['order_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $allowed_statuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
        
        if ($order_id > 0 && in_array($status, $allowed_statuses)) {
            try {
                $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $stmt->execute([$status, $order_id]);
                header('Location: orders.php?action=view&id=' . $order_id . '&success=1');
                exit();
            } catch (PDOException $e) {
                die("Lỗi cập nhật: " . $e->getMessage());
            }
        }
    }
    elseif ($action === 'update_shipping') {
        $order_id = (int)($_POST['order_id'] ?? 0);
        $customer_name = trim($_POST['customer_name'] ?? '');
        $customer_email = trim($_POST['customer_email'] ?? '');
        $customer_phone = trim($_POST['customer_phone'] ?? '');
        $customer_address = trim($_POST['customer_address'] ?? '');
        $note = trim($_POST['note'] ?? '');
        $order_total = trim($_POST['order_total'] ?? '0');

        if ($order_id > 0 && !empty($customer_name) && !empty($customer_phone) && !empty($customer_address)) {
             try {
                $sql = "UPDATE orders SET customer_name = ?, customer_email = ?, customer_phone = ?, customer_address = ?, note = ?, order_total = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$customer_name, $customer_email, $customer_phone, $customer_address, $note, $order_total, $order_id]);
                
                header('Location: orders.php?action=view&id=' . $order_id . '&success=1');
                exit();
            } catch (PDOException $e) {
                die("Lỗi cập nhật: " . $e->getMessage());
            }
        } else {
            header('Location: orders.php?action=view&id=' . $order_id . '&error=missing_fields');
            exit();
        }
    }
}

// --- PHẦN LOGIC LẤY DỮ LIỆU VÀ ĐIỀU HƯỚNG VIEW ---
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

$action = $_GET['action'] ?? 'list';

if(isset($_GET['success'])): ?>
<div class="alert alert-success">Thao tác thành công!</div>
<?php elseif(isset($_GET['error'])): ?>
<div class="alert alert-danger">Thao tác thất bại, vui lòng kiểm tra lại thông tin.</div>
<?php endif;

switch ($action) {
    case 'view':
        $order_id = (int)($_GET['id'] ?? 0);
        $stmt_order = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt_order->execute([$order_id]);
        $order = $stmt_order->fetch();

        if ($order) {
            $stmt_items = $pdo->prepare("
                SELECT oi.quantity, oi.price, p.name AS product_name, p.slug AS product_slug, pv.image_url,
                       GROUP_CONCAT(CONCAT(a.name, ': ', av.value) ORDER BY a.id SEPARATOR ' | ') AS attributes_string
                FROM order_items oi
                JOIN product_variants pv ON oi.variant_id = pv.id JOIN products p ON pv.product_id = p.id
                JOIN variant_values vv ON pv.id = vv.variant_id JOIN attribute_values av ON vv.attribute_value_id = av.id
                JOIN attributes a ON av.attribute_id = a.id WHERE oi.order_id = ?
                GROUP BY oi.id, p.name, p.slug, pv.image_url
            ");
            $stmt_items->execute([$order_id]);
            $order_items = $stmt_items->fetchAll();
            include __DIR__ . '/includes/order-detail-view.php';
        } else {
            echo '<div class="alert alert-danger">Không tìm thấy đơn hàng.</div>';
        }
        break;
    
    default:
        $stmt = $pdo->prepare("SELECT * FROM orders ORDER BY created_at DESC");
        $stmt->execute();
        $orders = $stmt->fetchAll();
        include __DIR__ . '/includes/order-list-view.php';
        break;
}

include_once __DIR__ . '/includes/footer.php';
// Nạp file JS của admin
echo '<script src="'.BASE_URL.'admin/assets/js/admin.js"></script>';