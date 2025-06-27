<?php
// Nạp các file cần thiết cho mọi action
require_once __DIR__ . '/auth-guard.php';
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/functions.php'; // Nạp cho các action khác nếu cần

// Lấy action từ URL
$action = $_GET['action'] ?? 'history';

// ===============================================================
// === KHỐI XỬ LÝ ĐẶC BIỆT: XUẤT EXCEL - PHẢI NẰM TRƯỚC MỌI HTML ===
// ===============================================================
if ($action === 'export_history') {
    try {
        // Xây dựng điều kiện WHERE dựa trên các tham số lọc
        $where_clauses = [];
        $params = [];
        $search_term = $_GET['search'] ?? '';
        $from_date = $_GET['from_date'] ?? '';
        $to_date = $_GET['to_date'] ?? '';

        if (!empty($search_term)) { $where_clauses[] = "se.export_code LIKE ?"; $params[] = "%$search_term%"; }
        if (!empty($from_date)) { $where_clauses[] = "DATE(se.created_at) >= ?"; $params[] = $from_date; }
        if (!empty($to_date)) { $where_clauses[] = "DATE(se.created_at) <= ?"; $params[] = $to_date; }
        
        $where_sql = empty($where_clauses) ? '' : 'WHERE ' . implode(' AND ', $where_clauses);
        
        // Lấy toàn bộ dữ liệu (không phân trang) để xuất file
        $stmt = $pdo->prepare("
            SELECT se.export_code, u.full_name, se.created_at, p.name, GROUP_CONCAT(av.value SEPARATOR ' - ') AS variant_attributes, pv.sku, sei.quantity, sei.price_at_export, se.note
            FROM stock_export_items sei
            JOIN stock_exports se ON sei.export_id = se.id
            JOIN users u ON se.user_id = u.id
            JOIN product_variants pv ON sei.variant_id = pv.id
            JOIN products p ON pv.product_id = p.id
            LEFT JOIN variant_values vv ON pv.id = vv.variant_id LEFT JOIN attribute_values av ON vv.attribute_value_id = av.id
            LEFT JOIN attributes a ON av.attribute_id = a.id
            $where_sql
            GROUP BY sei.id ORDER BY se.created_at DESC
        ");
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Thiết lập HTTP Headers
        $filename = "lich-su-xuat-kho_" . date('Y-m-d') . ".xls";
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        // Xuất tiêu đề
        $headers = ['Mã Phiếu', 'Người Xuất', 'Ngày Xuất', 'Tên Sản Phẩm', 'Phiên Bản', 'SKU', 'Số Lượng', 'Giá Xuất', 'Ghi Chú'];
        echo implode("\t", $headers) . "\n";
        
        // Xuất dữ liệu
        if (!empty($data)) {
            foreach ($data as $row) {
                array_walk($row, function(&$value) { $value = preg_replace("/\t/", "\\t", $value); $value = preg_replace("/\r?\n/", "\\n", $value); });
                echo implode("\t", array_values($row)) . "\n";
            }
        }
        // Dừng kịch bản ngay sau khi xuất file
        exit();

    } catch (PDOException $e) {
        die("Lỗi khi xuất dữ liệu: " . $e->getMessage());
    }
}


// --- Nếu không phải action export, thì tiếp tục hiển thị trang bình thường ---
include_once __DIR__ . '/includes/header.php'; 

// --- PHẦN LOGIC XỬ LÝ POST REQUEST (Tạo phiếu xuất) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action_post = $_POST['action'] ?? '';
    if ($action_post === 'create_export') {
        $note = trim($_POST['note'] ?? '');
        $items = $_POST['items'] ?? [];
        $user_id = $_SESSION['admin_id'];

        // Kiểm tra xem có sản phẩm nào trong phiếu không
        if (empty($items)) {
            header('Location: export.php?error=no_items');
            exit();
        }

        $pdo->beginTransaction();
        try {
            // 1. Tạo một phiếu xuất kho mới trong bảng `stock_exports`
            $export_code = 'PXK-' . date('Ymd-His'); // Tạo mã phiếu xuất duy nhất
            $stmt_export = $pdo->prepare("INSERT INTO stock_exports (export_code, user_id, note) VALUES (?, ?, ?)");
            $stmt_export->execute([$export_code, $user_id, $note]);
            $export_id = $pdo->lastInsertId();

            // 2. Lặp qua từng sản phẩm trong phiếu để xử lý
            foreach ($items as $variant_id => $item) {
                $quantity = (int)$item['quantity'];
                $price_at_export = (float)$item['price'];
                $variant_id = (int)$item['variant_id'];

                if ($quantity <= 0) continue; // Bỏ qua nếu số lượng không hợp lệ

                // 2a. Kiểm tra lại tồn kho một lần nữa để đảm bảo an toàn
                $stmt_stock = $pdo->prepare("SELECT stock_quantity FROM product_variants WHERE id = ? FOR UPDATE");
                $stmt_stock->execute([$variant_id]);
                $current_stock = $stmt_stock->fetchColumn();

                if ($current_stock < $quantity) {
                    // Nếu không đủ hàng, hủy toàn bộ giao dịch
                    throw new Exception("Không đủ tồn kho cho sản phẩm có ID phiên bản là $variant_id.");
                }
                
                // 2b. Thêm chi tiết vào bảng `stock_export_items`
                $stmt_item = $pdo->prepare("INSERT INTO stock_export_items (export_id, variant_id, quantity, price_at_export) VALUES (?, ?, ?, ?)");
                $stmt_item->execute([$export_id, $variant_id, $quantity, $price_at_export]);

                // 2c. Trừ tồn kho trong bảng `product_variants`
                $stmt_update_stock = $pdo->prepare("UPDATE product_variants SET stock_quantity = stock_quantity - ? WHERE id = ?");
                $stmt_update_stock->execute([$quantity, $variant_id]);
            }

            // Nếu mọi thứ thành công, commit transaction
            $pdo->commit();
            header('Location: export.php?success=exported');
            exit();

        } catch (Exception $e) {
            // Nếu có lỗi, rollback tất cả các thay đổi
            $pdo->rollBack();
            // Trong thực tế nên ghi log lỗi chi tiết, ở đây chúng ta hiển thị lỗi ra
            die("Có lỗi xảy ra, giao dịch đã được hủy bỏ: " . $e->getMessage());
        }
    }
}

// Hiển thị thông báo
if(isset($_GET['success'])): ?><div class="alert alert-success">Thao tác thành công!</div><?php endif; ?>
<?php if(isset($_GET['error'])): ?><div class="alert alert-danger">Có lỗi xảy ra hoặc dữ liệu không hợp lệ.</div>
<?php endif; ?>

<?php
// --- PHẦN LOGIC ĐIỀU HƯỚNG VIEW (GET REQUEST) ---
switch ($action) {
    case 'create':
        try {
            $stmt = $pdo->prepare("
                SELECT 
                    pv.id, pv.sku, pv.price, pv.stock_quantity,
                    p.name AS product_name,
                    GROUP_CONCAT(av.value ORDER BY a.id SEPARATOR ' - ') AS variant_attributes
                FROM product_variants pv
                JOIN products p ON pv.product_id = p.id
                LEFT JOIN variant_values vv ON pv.id = vv.variant_id
                LEFT JOIN attribute_values av ON vv.attribute_value_id = av.id
                LEFT JOIN attributes a ON av.attribute_id = a.id
                GROUP BY pv.id
                ORDER BY p.name, pv.id
            ");
            $stmt->execute();
            $all_variants = $stmt->fetchAll();
        } catch (PDOException $e) {
            $all_variants = [];
        }
        include __DIR__ . '/includes/export-create-view.php';
        break;
    case 'view':
        // === LOGIC MỚI CHO VIỆC XEM CHI TIẾT ===
        $export_id = (int)($_GET['id'] ?? 0);
        if ($export_id <= 0) { header('Location: export.php?action=history'); exit(); }

        // Lấy thông tin phiếu xuất chính
        $stmt_export = $pdo->prepare("SELECT se.*, u.full_name as user_name FROM stock_exports se JOIN users u ON se.user_id = u.id WHERE se.id = ?");
        $stmt_export->execute([$export_id]);
        $export = $stmt_export->fetch();

        if ($export) {
            // Lấy các sản phẩm trong phiếu xuất đó
            $stmt_items = $pdo->prepare("
                SELECT sei.quantity, sei.price_at_export, p.name as product_name, pv.sku,
                       GROUP_CONCAT(av.value ORDER BY a.id SEPARATOR ' - ') AS variant_attributes
                FROM stock_export_items sei
                JOIN product_variants pv ON sei.variant_id = pv.id
                JOIN products p ON pv.product_id = p.id
                LEFT JOIN variant_values vv ON pv.id = vv.variant_id
                LEFT JOIN attribute_values av ON vv.attribute_value_id = av.id
                LEFT JOIN attributes a ON av.attribute_id = a.id
                WHERE sei.export_id = ?
                GROUP BY sei.id, p.name, pv.sku
            ");
            $stmt_items->execute([$export_id]);
            $export_items = $stmt_items->fetchAll();
            
            // Gọi view chi tiết
            include __DIR__ . '/includes/export-detail-view.php';
        } else {
            echo '<div class="alert alert-danger">Không tìm thấy phiếu xuất kho.</div>';
        }
        break;
    default: // 'history'
        // --- LOGIC CHO VIEW LỊCH SỬ ---
        $results_per_page = 15;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $results_per_page;
        $search_term = $_GET['search'] ?? '';
        $from_date = $_GET['from_date'] ?? '';
        $to_date = $_GET['to_date'] ?? '';

        $where_clauses = [];
        $params = [];
        if (!empty($search_term)) { $where_clauses[] = "se.export_code LIKE ?"; $params[] = "%$search_term%"; }
        if (!empty($from_date)) { $where_clauses[] = "DATE(se.created_at) >= ?"; $params[] = $from_date; }
        if (!empty($to_date)) { $where_clauses[] = "DATE(se.created_at) <= ?"; $params[] = $to_date; }

        $where_sql = empty($where_clauses) ? '' : 'WHERE ' . implode(' AND ', $where_clauses);
        
        $stmt_count = $pdo->prepare("SELECT COUNT(DISTINCT se.id) FROM stock_exports se $where_sql");
        $stmt_count->execute($params);
        $total_results = $stmt_count->fetchColumn();
        $total_pages = ceil($total_results / $results_per_page);
        
        $sql = "SELECT se.*, u.full_name as user_name, COUNT(sei.id) as item_count FROM stock_exports se JOIN users u ON se.user_id = u.id LEFT JOIN stock_export_items sei ON se.id = sei.export_id $where_sql GROUP BY se.id ORDER BY se.created_at DESC LIMIT $results_per_page OFFSET $offset";
        $stmt_exports = $pdo->prepare($sql);
        $stmt_exports->execute($params);
        $exports = $stmt_exports->fetchAll();
        
        include __DIR__ . '/includes/export-history-view.php';
        break;
}

include_once __DIR__ . '/includes/footer.php'; 
?>