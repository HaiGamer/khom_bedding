<?php
// lấy thông tin địa chỉ đã lưu của người dùng
$user_id = $_SESSION['user_id'];
$addresses = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
    $stmt->execute([$user_id]);
    $addresses = $stmt->fetchAll();
} catch (PDOException $e) { die("Lỗi truy vấn: " . $e->getMessage()); }
?>

<div class="d-flex justify-content-between align-items-center mb-4">
   <h1 class="mb-0" style="font-family: var(--font-heading);">Địa chỉ đã lưu</h1>
   <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAddressModal">
      <i class="bi bi-plus-lg"></i> Thêm địa chỉ mới
   </button>
</div>

<?php if (empty($addresses)): ?>
<div class="alert alert-info">Bạn chưa có địa chỉ nào được lưu.</div>
<?php else: ?>
<div class="row g-4">
   <?php foreach ($addresses as $address): ?>
   <div class="col-md-6">
      <div class="card h-100">
         <div class="card-body">
            <div class="d-flex justify-content-between">
               <h5 class="card-title"><?php echo htmlspecialchars($address['full_name']); ?></h5>
               <?php if ($address['is_default']): ?>
               <span class="badge bg-success">Mặc định</span>
               <?php endif; ?>
            </div>
            <p class="card-text mb-1"><?php echo htmlspecialchars($address['address_line']); ?></p>
            <p class="card-text text-muted"><?php echo htmlspecialchars($address['phone_number']); ?></p>
            <div class="mt-3">
               <button type="button" class="btn btn-sm btn-outline-secondary edit-address-btn" data-bs-toggle="modal"
                  data-bs-target="#editAddressModal" data-id="<?php echo $address['id']; ?>"
                  data-name="<?php echo htmlspecialchars($address['full_name']); ?>"
                  data-phone="<?php echo htmlspecialchars($address['phone_number']); ?>"
                  data-address="<?php echo htmlspecialchars($address['address_line']); ?>"
                  data-default="<?php echo $address['is_default']; ?>">
                  Chỉnh sửa
               </button>
               <button type="button" class="btn btn-sm btn-outline-danger delete-address-btn" data-bs-toggle="modal"
                  data-bs-target="#deleteAddressModal" data-id="<?php echo $address['id']; ?>">
                  Xóa
               </button>
            </div>
         </div>
      </div>
   </div>
   <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="modal fade" id="addAddressModal" tabindex="-1" aria-labelledby="addAddressModalLabel" aria-hidden="true">
   <div class="modal-dialog">
      <div class="modal-content">
         <form action="account-handler.php" method="POST"><input type="hidden" name="action" value="add_address">
            <div class="modal-header">
               <h5 class="modal-title" id="addAddressModalLabel">Thêm địa chỉ mới</h5><button type="button"
                  class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
               <div class="mb-3"><label for="modal_add_full_name" class="form-label">Họ và tên người nhận</label><input
                     type="text" class="form-control" id="modal_add_full_name" name="full_name" required></div>
               <div class="mb-3"><label for="modal_add_phone_number" class="form-label">Số điện thoại</label><input
                     type="tel" class="form-control" id="modal_add_phone_number" name="phone_number" required></div>
               <div class="mb-3"><label for="modal_add_address_line" class="form-label">Địa chỉ chi
                     tiết</label><textarea class="form-control" id="modal_add_address_line" name="address_line" rows="3"
                     required></textarea></div>
               <div class="form-check"><input class="form-check-input" type="checkbox" name="is_default" value="1"
                     id="modal_add_is_default"><label class="form-check-label" for="modal_add_is_default">Đặt làm địa
                     chỉ mặc định</label></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary"
                  data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-primary">Lưu địa chỉ</button>
            </div>
         </form>
      </div>
   </div>
</div>

<div class="modal fade" id="editAddressModal" tabindex="-1" aria-labelledby="editAddressModalLabel" aria-hidden="true">
   <div class="modal-dialog">
      <div class="modal-content">
         <form action="account-handler.php" method="POST">
            <input type="hidden" name="action" value="edit_address">
            <input type="hidden" name="address_id" id="edit-address-id">
            <div class="modal-header">
               <h5 class="modal-title" id="editAddressModalLabel">Chỉnh sửa địa chỉ</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
               <div class="mb-3">
                  <label for="edit-full_name" class="form-label">Họ và tên người nhận</label>
                  <input type="text" class="form-control" id="edit-full_name" name="full_name" required>
               </div>
               <div class="mb-3">
                  <label for="edit-phone_number" class="form-label">Số điện thoại</label>
                  <input type="tel" class="form-control" id="edit-phone_number" name="phone_number" required>
               </div>
               <div class="mb-3">
                  <label for="edit-address_line" class="form-label">Địa chỉ chi tiết</label>
                  <textarea class="form-control" id="edit-address_line" name="address_line" rows="3"
                     required></textarea>
               </div>
               <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="is_default" value="1" id="edit-is_default">
                  <label class="form-check-label" for="edit-is_default">
                     Đặt làm địa chỉ mặc định
                  </label>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
               <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
            </div>
         </form>
      </div>
   </div>
</div>

<div class="modal fade" id="deleteAddressModal" tabindex="-1" aria-labelledby="deleteAddressModalLabel"
   aria-hidden="true">
   <div class="modal-dialog">
      <div class="modal-content">
         <form action="account-handler.php" method="POST">
            <input type="hidden" name="action" value="delete_address">
            <input type="hidden" name="address_id" id="delete-address-id">
            <div class="modal-header">
               <h5 class="modal-title" id="deleteAddressModalLabel">Xác nhận xóa</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
               Bạn có chắc chắn muốn xóa địa chỉ này không? Hành động này không thể hoàn tác.
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
               <button type="submit" class="btn btn-danger">Xác nhận Xóa</button>
            </div>
         </form>
      </div>
   </div>
</div>