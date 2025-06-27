<?php 
require_once __DIR__ . '/auth-guard.php';
include_once __DIR__ . '/includes/header.php'; 

// --- PHẦN LOGIC PHP ---
try {
    // Lấy tất cả dữ liệu phiên bản, bao gồm cả giá vốn
    $stmt = $pdo->prepare("
        SELECT 
            pv.id, pv.sku, pv.stock_quantity, pv.cost_price,
            p.id AS product_id, p.name AS product_name,
            GROUP_CONCAT(av.value ORDER BY a.id SEPARATOR ' - ') AS variant_attributes,
            COALESCE(SUM(oi.quantity), 0) AS total_sold
        FROM product_variants pv
        JOIN products p ON pv.product_id = p.id
        LEFT JOIN variant_values vv ON pv.id = vv.variant_id
        LEFT JOIN attribute_values av ON vv.attribute_value_id = av.id
        LEFT JOIN attributes a ON av.attribute_id = a.id
        LEFT JOIN order_items oi ON pv.id = oi.variant_id
        GROUP BY pv.id, p.id, p.name
        ORDER BY p.name, pv.id
    ");
    $stmt->execute();
    $variants_flat_list = $stmt->fetchAll();

    $products_with_variants = [];
    $grand_total_cost = 0;
    foreach ($variants_flat_list as $variant) {
        if (!isset($products_with_variants[$variant['product_id']])) {
            $products_with_variants[$variant['product_id']] = [
                'details' => ['product_name' => $variant['product_name'], 'product_id' => $variant['product_id']],
                'variants' => []
            ];
        }
        $products_with_variants[$variant['product_id']]['variants'][] = $variant;
        $grand_total_cost += $variant['cost_price'] * $variant['stock_quantity'];
    }

} catch (PDOException $e) {
    die("Lỗi truy vấn: " . $e->getMessage());
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
   <h1 class="mb-0">Báo cáo Tồn kho & Bán hàng</h1>
   <a href="export-inventory.php" class="btn btn-success"><i class="bi bi-file-earmark-excel-fill"></i> Xuất Excel</a>
</div>

<div class="card">
   <div class="card-body">
      <div class="table-responsive">
         <table class="table table-hover align-middle">
            <thead>
               <tr>
                  <th style="width: 30%;">Phiên bản</th>
                  <th>SKU</th>
                  <th class="text-center">Đã bán</th>
                  <th class="text-center">Tồn kho</th>
                  <th class="text-end">Giá vốn</th>
                  <th class="text-end">Tổng vốn tồn</th>
               </tr>
            </thead>
            <tbody id="inventory-table-body"> <?php if (empty($products_with_variants)): ?>
               <tr>
                  <td colspan="6" class="text-center">Chưa có sản phẩm nào.</td>
               </tr>
               <?php else: ?>
               <?php foreach ($products_with_variants as $product_data): ?>
               <tr class="table-light">
                  <td colspan="6" class="fw-bold"><a
                        href="product-edit.php?id=<?php echo $product_data['details']['product_id']; ?>"
                        class="text-dark text-decoration-none"><?php echo htmlspecialchars($product_data['details']['product_name']); ?></a>
                  </td>
               </tr>
               <?php foreach ($product_data['variants'] as $variant): ?>
               <tr class="inventory-item-row" data-cost-price="<?php echo $variant['cost_price']; ?>">
                  <td class="ps-4"><?php echo htmlspecialchars($variant['variant_attributes'] ?? 'Phiên bản gốc'); ?>
                  </td>
                  <td><?php echo htmlspecialchars($variant['sku']); ?></td>
                  <td class="text-center fw-bold"><?php echo $variant['total_sold']; ?></td>
                  <td class="text-center" style="width: 120px;"><input type="number"
                        class="form-control form-control-sm text-center stock-update-input"
                        value="<?php echo $variant['stock_quantity']; ?>"
                        data-variant-id="<?php echo $variant['id']; ?>" min="0"></td>

                  <td class="text-end"><?php echo number_format($variant['cost_price'], 0, ',', '.'); ?>đ</td>
                  <td class="text-end fw-bold item-total-cost">
                     <?php echo number_format($variant['cost_price'] * $variant['stock_quantity'], 0, ',', '.'); ?>đ
                  </td>
               </tr>
               <?php endforeach; ?>
               <?php endforeach; ?>
               <?php endif; ?>
            </tbody>
         </table>
      </div>
   </div>
   <div class="card-footer text-end">
      <h5 class="mb-0">Tổng giá trị vốn tồn kho: <span class="text-danger fw-bold"
            id="inventory-grand-total"><?php echo number_format($grand_total_cost, 0, ',', '.'); ?>đ</span></h5>
   </div>
</div>

<?php 
include_once __DIR__ . '/includes/footer.php'; 
?>