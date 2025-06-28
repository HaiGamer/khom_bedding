<?php
require_once __DIR__ . '/auth-guard.php';
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products.php');
    exit();
}

$product_id = (int)($_POST['product_id'] ?? 0);

// --- HÀNH ĐỘNG: THÊM SẢN PHẨM ---
if (isset($_POST['action']) && $_POST['action'] === 'add_product') {
    $name = trim($_POST['name'] ?? '');
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $description = trim($_POST['description'] ?? '');
    $short_description = trim($_POST['short_description'] ?? '');
    $slug = generate_slug($name);
    $variants = $_POST['variants'] ?? [];
    $default_variant_index = $_POST['default_variant_index'] ?? 0;

    if (empty($name) || empty($variants)) { die("Lỗi: Tên sản phẩm và ít nhất một phiên bản là bắt buộc."); }

    $pdo->beginTransaction();
    try {
        $sql_product = "INSERT INTO products (name, slug, category_id, description, short_description) VALUES (?, ?, ?, ?, ?)";
        $stmt_product = $pdo->prepare($sql_product);
        $stmt_product->execute([$name, $slug, $category_id, $description, $short_description]);
        $product_id = $pdo->lastInsertId();

        if (isset($_FILES['gallery_images']) && !empty(array_filter($_FILES['gallery_images']['name']))) {
            $gallery_files = $_FILES['gallery_images'];
            $is_first_image = true;
            foreach ($gallery_files['name'] as $index => $file_name) {
                if ($gallery_files['error'][$index] === UPLOAD_ERR_OK) {
                    $file_tmp_path = $gallery_files['tmp_name'][$index];
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    $new_file_name = uniqid('prod_' . $product_id . '_', true) . '.' . $file_ext;
                    $dest_path = __DIR__ . '/../uploads/products/' . $new_file_name;
                    $allowed_file_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    if (in_array($file_ext, $allowed_file_types) && move_uploaded_file($file_tmp_path, $dest_path)) {
                        $image_url_db = 'uploads/products/' . $new_file_name;
                        $stmt_img = $pdo->prepare("INSERT INTO product_images (product_id, image_url, is_featured) VALUES (?, ?, ?)");
                        $stmt_img->execute([$product_id, $image_url_db, $is_first_image]);
                        $is_first_image = false;
                    }
                }
            }
        }
        
        foreach ($variants as $index => $variant_data) {
            $cost_price = $variant_data['cost_price'] ?? 0; // Lấy giá vốn
            $sku = $variant_data['sku'] ?? '';
            $price = $variant_data['price'] ?? 0;
            $original_price = !empty($variant_data['original_price']) ? $variant_data['original_price'] : null;
            $stock = $variant_data['stock'] ?? 0;
            $attributes = $variant_data['attributes'] ?? [];
            $is_default = ($index == $default_variant_index);

            $sql_variant = "INSERT INTO product_variants (product_id, sku, price, original_price, cost_price, stock_quantity, is_default) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_variant = $pdo->prepare($sql_variant);
            $stmt_variant->execute([$product_id, $sku, $price, $original_price, $cost_price, $stock, $is_default]);
            $variant_id = $pdo->lastInsertId();

            $sql_variant_value = "INSERT INTO variant_values (variant_id, attribute_value_id) VALUES (?, ?)";
            $stmt_variant_value = $pdo->prepare($sql_variant_value);
            foreach ($attributes as $attribute_id => $attribute_value_id) {
                if (!empty($attribute_value_id)) { $stmt_variant_value->execute([$variant_id, $attribute_value_id]); }
            }
        }
        $pdo->commit();
        header('Location: products.php?success=added');
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Có lỗi xảy ra khi thêm sản phẩm: " . $e->getMessage());
    }
}
// --- HÀNH ĐỘNG: SỬA SẢN PHẨM ---
elseif (isset($_POST['action']) && $_POST['action'] === 'edit_product') {
    if ($product_id === 0) { die("Lỗi: ID sản phẩm không hợp lệ."); }
    
    $name = trim($_POST['name'] ?? '');
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $description = trim($_POST['description'] ?? '');
    $short_description = trim($_POST['short_description'] ?? '');
    $slug = generate_slug($name);
    $variants = $_POST['variants'] ?? [];
    $default_variant_index = $_POST['default_variant_index'] ?? -1;

    if (empty($name) || empty($variants)) { die("Lỗi: Thiếu thông tin sản phẩm."); }

    $pdo->beginTransaction();
    try {
        $sql_product = "UPDATE products SET name = ?, slug = ?, category_id = ?, description = ?, short_description = ? WHERE id = ?";
        $stmt_product = $pdo->prepare($sql_product);
        $stmt_product->execute([$name, $slug, $category_id, $description, $short_description, $product_id]);

        if (isset($_FILES['new_gallery_images']) && !empty(array_filter($_FILES['new_gallery_images']['name']))) {
            $gallery_files = $_FILES['new_gallery_images'];
            foreach ($gallery_files['name'] as $index => $file_name) {
                if ($gallery_files['error'][$index] === UPLOAD_ERR_OK) {
                    $file_tmp_path = $gallery_files['tmp_name'][$index];
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    $new_file_name = uniqid('prod_' . $product_id . '_', true) . '.' . $file_ext;
                    $dest_path = __DIR__ . '/../uploads/products/' . $new_file_name;
                    $allowed_file_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    if (in_array($file_ext, $allowed_file_types) && move_uploaded_file($file_tmp_path, $dest_path)) {
                        $image_url_db = 'uploads/products/' . $new_file_name;
                        $stmt_img = $pdo->prepare("INSERT INTO product_images (product_id, image_url) VALUES (?, ?)");
                        $stmt_img->execute([$product_id, $image_url_db]);
                    }
                }
            }
        }

        // CẬP NHẬT LIÊN KẾT BỘ SƯU TẬP
        // B1: Xóa hết các liên kết cũ của sản phẩm này
        $stmt_delete_coll = $pdo->prepare("DELETE FROM product_collections WHERE product_id = ?");
        $stmt_delete_coll->execute([$product_id]);

        // B2: Thêm lại các liên kết mới được chọn từ form
        if (!empty($_POST['collections']) && is_array($_POST['collections'])) {
            $sql_coll = "INSERT INTO product_collections (product_id, collection_id) VALUES (?, ?)";
            $stmt_coll = $pdo->prepare($sql_coll);
            foreach ($_POST['collections'] as $collection_id) {
                $stmt_coll->execute([$product_id, (int)$collection_id]);
            }
        }
        
        foreach ($variants as $index => $variant_data) {
            $cost_price = $variant_data['cost_price'] ?? 0; // Lấy giá vốn
            $variant_id = (int)($variant_data['id'] ?? 0);
            $sku = $variant_data['sku'] ?? '';
            $price = $variant_data['price'] ?? 0;
            $original_price = !empty($variant_data['original_price']) ? $variant_data['original_price'] : null;
            $stock = $variant_data['stock'] ?? 0;
            $attributes = $variant_data['attributes'] ?? [];
            $is_default = ($index == $default_variant_index);
            
            if ($variant_id > 0) { // Cập nhật phiên bản đã có
                $sql_variant = "UPDATE product_variants SET sku = ?, price = ?, original_price = ?, cost_price = ?, stock_quantity = ?, is_default = ? WHERE id = ? AND product_id = ?";
                $stmt_variant = $pdo->prepare($sql_variant);
                $stmt_variant->execute([$sku, $price, $original_price, $cost_price, $stock, $is_default, $variant_id, $product_id]);
            } else { // Thêm phiên bản mới
                $sql_variant = "INSERT INTO product_variants (product_id, sku, price, original_price, cost_price, stock_quantity, is_default) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt_variant = $pdo->prepare($sql_variant);
                $stmt_variant->execute([$product_id, $sku, $price, $original_price, $cost_price, $stock, $is_default]);
                $variant_id = $pdo->lastInsertId();
            }
            
            $stmt_delete_attrs = $pdo->prepare("DELETE FROM variant_values WHERE variant_id = ?");
            $stmt_delete_attrs->execute([$variant_id]);
            $sql_variant_value = "INSERT INTO variant_values (variant_id, attribute_value_id) VALUES (?, ?)";
            $stmt_variant_value = $pdo->prepare($sql_variant_value);
            foreach ($attributes as $attribute_id => $attribute_value_id) {
                if (!empty($attribute_value_id)) { $stmt_variant_value->execute([$variant_id, $attribute_value_id]); }
            }
        }
        
        $pdo->commit();
        header('Location: product-edit.php?id='.$product_id.'&success=1');
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Có lỗi xảy ra khi cập nhật sản phẩm: " . $e->getMessage());
    }
}
// --- HÀNH ĐỘNG: XÓA MỘT PHIÊN BẢN ---
elseif (isset($_POST['delete_variant'])) {
    $variant_id = (int)$_POST['delete_variant'];

    if ($variant_id > 0 && $product_id > 0) {
        $pdo->beginTransaction();
        try {
            $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM product_variants WHERE product_id = ?");
            $stmt_count->execute([$product_id]);
            if ($stmt_count->fetchColumn() <= 1) { throw new Exception('last_variant'); }
            
            $stmt_check = $pdo->prepare("SELECT 1 FROM order_items WHERE variant_id = ? LIMIT 1");
            $stmt_check->execute([$variant_id]);
            if ($stmt_check->fetch()) { throw new Exception('variant_in_order'); }
            
            $stmt_delete = $pdo->prepare("DELETE FROM product_variants WHERE id = ?");
            $stmt_delete->execute([$variant_id]);
            
            $pdo->commit();
            header('Location: product-edit.php?id='.$product_id.'&success=1');
        } catch (Exception $e) {
            $pdo->rollBack();
            header('Location: product-edit.php?id='.$product_id.'&error='.$e->getMessage());
        }
    } else { header('Location: products.php'); }
}

// --- HÀNH ĐỘNG: XÓA MỘT ẢNH GALLERY ---
elseif (isset($_POST['delete_image'])) {
    $image_id = (int)$_POST['delete_image'];

    if ($image_id > 0 && $product_id > 0) {
        $stmt_get = $pdo->prepare("SELECT image_url, is_featured FROM product_images WHERE id = ? AND product_id = ?");
        $stmt_get->execute([$image_id, $product_id]);
        $image = $stmt_get->fetch();

        if ($image && $image['is_featured']) {
            header('Location: product-edit.php?id='.$product_id.'&error=featured_delete'); exit();
        }

        if ($image) {
            $stmt_delete = $pdo->prepare("DELETE FROM product_images WHERE id = ?");
            $stmt_delete->execute([$image_id]);
            if (file_exists(__DIR__ . '/../' . $image['image_url'])) { unlink(__DIR__ . '/../' . $image['image_url']); }
        }
        header('Location: product-edit.php?id='.$product_id.'&success=1');
    } else { header('Location: products.php'); }
}

// --- HÀNH ĐỘNG: ĐẶT LÀM ẢNH ĐẠI DIỆN ---
elseif (isset($_POST['set_featured_image'])) {
    $image_id = (int)$_POST['set_featured_image'];
    
    if ($image_id > 0 && $product_id > 0) {
        $pdo->beginTransaction();
        try {
            $stmt_reset = $pdo->prepare("UPDATE product_images SET is_featured = 0 WHERE product_id = ?");
            $stmt_reset->execute([$product_id]);
            
            $stmt_set = $pdo->prepare("UPDATE product_images SET is_featured = 1 WHERE id = ? AND product_id = ?");
            $stmt_set->execute([$image_id, $product_id]);

            $pdo->commit();
            header('Location: product-edit.php?id='.$product_id.'&success=1');
        } catch (Exception $e) {
            $pdo->rollBack();
            die("Lỗi: " . $e->getMessage());
        }
    } else { header('Location: products.php'); }
}

else {
    header('Location: products.php');
}
exit();