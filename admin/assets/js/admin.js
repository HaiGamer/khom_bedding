document.addEventListener('DOMContentLoaded', function() {
    // Logic chung để thêm/xóa phiên bản
    const variantsContainer = document.getElementById('variants-container');
    if (variantsContainer) {
        const addVariantBtn = document.getElementById('add-variant-btn');
        const variantTemplate = document.getElementById('variant-template');
        
        // Đếm số phiên bản đã có sẵn trên trang sửa để tính index cho phiên bản mới
        let variantIndex = variantsContainer.querySelectorAll('.variant-row').length;

        function addVariantRow() {
            if (!variantTemplate) return;
            const templateContent = variantTemplate.content.cloneNode(true);
            const newRow = templateContent.querySelector('.variant-row');
            
            // Cập nhật lại các thuộc tính 'name' với index mới
            let newHtml = newRow.innerHTML.replace(/__INDEX__/g, variantIndex);
            newRow.innerHTML = newHtml;

            // Cập nhật số thứ tự hiển thị
            newRow.querySelector('.variant-index').textContent = variantIndex + 1;
            
            // Nếu là phiên bản đầu tiên (trên trang Thêm mới), check nó làm mặc định
            if (document.querySelectorAll('.variant-row').length === 0 && variantIndex === 0) {
                const defaultRadio = newRow.querySelector('input[type="radio"][name="default_variant_index"]');
                if (defaultRadio) {
                    defaultRadio.checked = true;
                }
            }
            
            variantsContainer.appendChild(templateContent);
            variantIndex++;
        }

        if (addVariantBtn) {
            addVariantBtn.addEventListener('click', addVariantRow);
        }
        

        // Tạm thời chưa có chức năng xóa động bằng JS, vì ta dùng submit form
        // Do đó, không cần listener cho remove-variant-btn ở đây
        // Việc xóa sẽ được xử lý bằng cách submit form riêng cho nút xóa
        
        // Nếu là trang Thêm sản phẩm (chưa có phiên bản nào), tự động thêm dòng đầu tiên
        if (document.getElementById('add-variant-btn') && document.querySelectorAll('.variant-row').length === 0) {
            addVariantRow();
        }
    }
    // =======================================================
    // --- LOGIC CHO NÚT COPY TRÊN TRANG CHI TIẾT ĐƠN HÀNG ---
    // =======================================================
    const copyButtons = document.querySelectorAll('.copy-btn');
    if (copyButtons.length > 0) {
        copyButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetSelector = this.getAttribute('data-target');
                const targetElement = document.querySelector(targetSelector);

                if (targetElement) {
                    // Dùng Clipboard API hiện đại để copy
                    navigator.clipboard.writeText(targetElement.value || targetElement.textContent)
                        .then(() => {
                            // Cung cấp phản hồi cho người dùng
                            const originalIcon = this.innerHTML;
                            this.innerHTML = '<i class="bi bi-check-lg text-success"></i>'; // Đổi icon thành dấu check
                            setTimeout(() => {
                                this.innerHTML = originalIcon; // Trả lại icon cũ sau 1.5 giây
                            }, 1500);
                        })
                        .catch(err => {
                            console.error('Không thể copy: ', err);
                            alert('Copy thất bại!');
                        });
                }
            });
        });
    }
});