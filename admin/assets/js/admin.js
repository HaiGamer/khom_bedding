document.addEventListener('DOMContentLoaded', function() {
    // =======================================================
    // --- LOGIC CHO TRANG THÊM/SỬA SẢN PHẨM ---
    // =======================================================
    const variantsContainer = document.getElementById('variants-container');
    if (variantsContainer) {
        const addVariantBtn = document.getElementById('add-variant-btn');
        const variantTemplate = document.getElementById('variant-template');
        let variantIndex = variantsContainer.querySelectorAll('.variant-row').length;

        function addVariantRow() {
            if (!variantTemplate) return;
            const templateContent = variantTemplate.content.cloneNode(true);
            const newRow = templateContent.querySelector('.variant-row');
            
            newRow.innerHTML = newRow.innerHTML.replace(/__INDEX__/g, variantIndex);
            newRow.querySelector('.variant-index').textContent = variantIndex + 1;
            
            if (document.querySelectorAll('.variant-row').length === 0 && variantIndex === 0) {
                const defaultRadio = newRow.querySelector('input[type="radio"][name="default_variant_index"]');
                if (defaultRadio) { defaultRadio.checked = true; }
            }
            
            variantsContainer.appendChild(templateContent);
            variantIndex++;
        }

        if (addVariantBtn) {
            addVariantBtn.addEventListener('click', addVariantRow);
        }
        
        // Tự động thêm phiên bản đầu tiên trên trang Thêm mới
        if (document.getElementById('add-variant-btn') && document.querySelectorAll('.variant-row').length === 0) {
            addVariantRow();
        }
    }

    // =======================================================
    // --- LOGIC CHO BÁO CÁO TỒN KHO ---
    // =======================================================
    const stockInputs = document.querySelectorAll('.stock-update-input');
    if (stockInputs.length > 0) {
        let stockUpdateTimeout;
        stockInputs.forEach(input => {
            input.addEventListener('change', function() {
                clearTimeout(stockUpdateTimeout);
                const variantId = this.dataset.variantId;
                const newStock = this.value;
                const originalBackgroundColor = this.style.backgroundColor;
                this.style.backgroundColor = '#fff3cd';

                stockUpdateTimeout = setTimeout(() => {
                    fetch('ajax-update-stock.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({
                            variant_id: variantId,
                            stock_quantity: newStock
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) { this.style.backgroundColor = '#d1e7dd'; } 
                        else {
                            this.style.backgroundColor = '#f8d7da';
                            alert(data.message || 'Có lỗi xảy ra.');
                        }
                    })
                    .catch(error => { this.style.backgroundColor = '#f8d7da'; console.error('Update stock error:', error); })
                    .finally(() => {
                        setTimeout(() => { this.style.backgroundColor = originalBackgroundColor; }, 1500);
                    });
                }, 800);
            });
        });
    }

    // =======================================================
    // --- LOGIC CHO TRANG XUẤT KHO ---
    // =======================================================
    const exportPageContainer = document.getElementById('product-search-input');
    if (exportPageContainer) {
        const searchInput = document.getElementById('product-search-input');
        const searchResultsContainer = document.getElementById('product-search-results');
        const exportItemsTable = document.getElementById('export-items-table');
        const grandTotalEl = document.getElementById('export-grand-total');
        const productListContainer = document.querySelector('.product-list-container');
        let searchTimeout;

        function calculateGrandTotal() {
            let total = 0;
            const itemRows = exportItemsTable.querySelectorAll('tr[data-variant-id]');
            itemRows.forEach(row => {
                const price = parseFloat(row.dataset.price);
                const quantity = parseInt(row.querySelector('.export-quantity-input').value) || 0;
                const subtotal = price * quantity;
                row.querySelector('.item-subtotal').textContent = new Intl.NumberFormat('vi-VN').format(subtotal) + 'đ';
                total += subtotal;
            });
            grandTotalEl.textContent = new Intl.NumberFormat('vi-VN').format(total) + 'đ';
        }

        function addItemToExportSlip(targetButton) {
            const variantId = targetButton.dataset.id;
            if (document.querySelector(`tr[data-variant-id="${variantId}"]`)) { alert('Sản phẩm này đã có trong phiếu.'); return; }
            if (document.getElementById('no-items-row')) { document.getElementById('no-items-row').remove(); }
            const newRow = document.createElement('tr');
            newRow.dataset.variantId = variantId;
            newRow.dataset.price = targetButton.dataset.price;
            newRow.innerHTML = `
                <td><input type="hidden" name="items[${variantId}][variant_id]" value="${variantId}"><strong>${targetButton.dataset.name}</strong><br><small class="text-muted">${targetButton.dataset.attributes}</small></td>
                <td><input type="number" name="items[${variantId}][quantity]" class="form-control form-control-sm export-quantity-input" value="1" min="1" max="${targetButton.dataset.stock}" required></td>
                <td class="text-end"><input type="number" name="items[${variantId}][price]" class="form-control form-control-sm text-end" value="${targetButton.dataset.price}" required></td>
                <td class="text-end fw-bold"><span class="item-subtotal">${new Intl.NumberFormat('vi-VN').format(targetButton.dataset.price)}đ</span></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-export-item-btn"><i class="bi bi-x-lg"></i></button></td>
            `;
            exportItemsTable.appendChild(newRow);
            calculateGrandTotal();
        }

        searchInput.addEventListener('keyup', function() {
            clearTimeout(searchTimeout);
            const searchTerm = this.value.trim();
            if (searchTerm.length < 2) { searchResultsContainer.style.display = 'none'; return; }
            searchTimeout = setTimeout(() => {
                fetch(`ajax-product-search.php?term=${encodeURIComponent(searchTerm)}`)
                    .then(response => response.json())
                    .then(data => {
                        let html = '<div class="list-group list-group-flush">';
                        if (data.length > 0) {
                            data.forEach(item => {
                                html += `<a href="#" class="list-group-item list-group-item-action add-item-btn" data-id="${item.id}" data-name="${item.product_name}" data-attributes="${item.variant_attributes || ''}" data-sku="${item.sku}" data-price="${item.price}" data-stock="${item.stock_quantity}"><strong>${item.product_name}</strong> - ${item.variant_attributes || 'Phiên bản gốc'}<br><small class="text-muted">SKU: ${item.sku} | Tồn kho: ${item.stock_quantity}</small></a>`;
                            });
                        } else {
                            html += '<span class="list-group-item text-muted">Không tìm thấy...</span>';
                        }
                        html += '</div>';
                        searchResultsContainer.innerHTML = html;
                        searchResultsContainer.style.display = 'block';
                    });
            }, 300);
        });

        searchResultsContainer.addEventListener('click', function(event) {
            event.preventDefault();
            const target = event.target.closest('.add-item-btn');
            if (!target) return;
            addItemToExportSlip(target);
            searchResultsContainer.style.display = 'none';
            searchInput.value = '';
        });
        
        if (productListContainer) {
            productListContainer.addEventListener('click', function(event){
                const target = event.target.closest('.add-item-btn');
                if (target) { addItemToExportSlip(target); }
            });
        }
        
        exportItemsTable.addEventListener('click', function(event){
            const target = event.target.closest('.remove-export-item-btn');
            if(target){
                target.closest('tr').remove();
                calculateGrandTotal();
                if(exportItemsTable.children.length === 0){
                    exportItemsTable.innerHTML = '<tr id="no-items-row"><td colspan="5" class="text-center text-muted">Chưa có sản phẩm nào.</td></tr>';
                }
            }
        });

        exportItemsTable.addEventListener('input', function(event) {
            if (event.target.classList.contains('export-quantity-input') || event.target.classList.contains('text-end')) {
                const row = event.target.closest('tr');
                if (row) {
                    const priceInput = row.querySelector('input[name*="[price]"]');
                    row.dataset.price = priceInput.value;
                }
                calculateGrandTotal();
            }
        });
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