document.addEventListener('DOMContentLoaded', function () {
    
    // =======================================================
    // --- LOGIC CHO LIVE SEARCH Ở HEADER (ĐÃ NÂNG CẤP) ---
    // =======================================================
    
    // Hàm xử lý tìm kiếm có thể tái sử dụng
    function performLiveSearch(searchTerm, resultsContainer) {
        if (searchTerm.length < 2) {
            resultsContainer.innerHTML = '';
            resultsContainer.style.display = 'none';
            return;
        }

        const url = `${BASE_URL}ajax-search.php?term=${encodeURIComponent(searchTerm)}`;
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                let html = '';
                if (data.length > 0) {
                    data.forEach(item => {
                        const price = new Intl.NumberFormat('vi-VN').format(item.price) + 'đ';
                        const imageUrl = `${BASE_URL}${item.image_url || 'assets/images/placeholder.png'}`;
                        const itemUrl = `${BASE_URL}product-detail.php?slug=${item.slug}`;
                        html += `
                            <a href="${itemUrl}" class="result-item">
                                <img src="${imageUrl}" alt="${item.name}">
                                <div class="result-info">
                                    <span class="result-name">${item.name}</span>
                                    <span class="result-price">${price}</span>
                                </div>
                            </a>`;
                    });
                } else {
                    html = '<div class="p-3 text-muted">Không tìm thấy sản phẩm nào.</div>';
                }
                resultsContainer.innerHTML = html;
                resultsContainer.style.display = 'block';
            })
            .catch(error => console.error('Lỗi tìm kiếm:', error));
    }

    // Xử lý cho ô tìm kiếm trên Desktop
    const desktopSearchInput = document.getElementById('live-search-input');
    const desktopResultsContainer = document.getElementById('search-results-container');
    let desktopSearchTimeout;

    if (desktopSearchInput && desktopResultsContainer) {
        desktopSearchInput.addEventListener('keyup', function() {
            clearTimeout(desktopSearchTimeout);
            desktopSearchTimeout = setTimeout(() => {
                performLiveSearch(this.value, desktopResultsContainer);
            }, 300);
        });
    }

    // Xử lý cho ô tìm kiếm trên Mobile (trong Modal)
    const mobileSearchInput = document.getElementById('mobile-search-input');
    const mobileResultsContainer = document.getElementById('mobile-search-results');
    let mobileSearchTimeout;

    if (mobileSearchInput && mobileResultsContainer) {
        mobileSearchInput.addEventListener('keyup', function() {
            clearTimeout(mobileSearchTimeout);
            mobileSearchTimeout = setTimeout(() => {
                performLiveSearch(this.value, mobileResultsContainer);
            }, 300);
        });
    }
    
    // Ẩn kết quả khi click ra ngoài (chỉ cho desktop)
    document.addEventListener('click', function(event) {
        const searchContainer = document.querySelector('.search-container');
        if (searchContainer && !searchContainer.contains(event.target)) {
            desktopResultsContainer.style.display = 'none';
        }
    });


    // ===================================================================
    // --- LOGIC CHO TRANG CHI TIẾT SẢN PHẨM (product-detail.php) ---
    // ===================================================================
    const productDetailContainer = document.getElementById('product-options');
    if (productDetailContainer && typeof allVariantsData !== 'undefined' && allVariantsData.length > 0) {
        
        const priceEl = document.getElementById('product-price');
        const skuEl = document.getElementById('product-sku');
        const stockStatusEl = document.getElementById('stock-status');
        const addToCartBtn = document.getElementById('add-to-cart-btn');
        const quantityInput = document.querySelector('input[type="number"]');
        const cartCountEl = document.getElementById('cart-item-count');
        const optionGroups = Array.from(productDetailContainer.querySelectorAll('.mb-3'));

        let selectedVariant = null;

        function updateProductDisplay(variant) {
            selectedVariant = variant;
            if (variant) {
                // === LOGIC MỚI CHO HIỂN THỊ GIÁ ===
        let priceHTML = '';
        const salePrice = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(variant.price);
        
        if (variant.original_price && parseFloat(variant.original_price) > parseFloat(variant.price)) {
            const originalPrice = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(variant.original_price);
            priceHTML = `<span class="me-2">${salePrice}</span><del class="text-muted small price-original" style="font-size: 0.8em;">${originalPrice}</del>`;
        } else {
            priceHTML = `<span>${salePrice}</span>`;
        }
        priceEl.innerHTML = priceHTML;
        // === KẾT THÚC LOGIC MỚI ===
                //priceEl.textContent = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(variant.price);
                skuEl.textContent = `Mã: ${variant.sku}`;
                if (variant.stock > 0) {
                    stockStatusEl.textContent = `Còn lại: ${variant.stock} sản phẩm`;
                    stockStatusEl.className = 'mt-2 text-success';
                    addToCartBtn.disabled = false;
                    addToCartBtn.textContent = 'Thêm vào giỏ hàng';
                } else {
                    stockStatusEl.textContent = 'Hết hàng';
                    stockStatusEl.className = 'mt-2 text-danger fw-bold';
                    addToCartBtn.disabled = true;
                    addToCartBtn.textContent = 'Đã hết hàng';
                }
            } else {
                priceEl.textContent = '...đ';
                skuEl.textContent = '';
                stockStatusEl.textContent = '';
                addToCartBtn.disabled = true;
                addToCartBtn.textContent = 'Vui lòng chọn tùy chọn';
            }
        }

        function updateAllOptions() {
            const currentSelections = {};
            const selectedRadios = productDetailContainer.querySelectorAll('input[type="radio"]:checked');
            selectedRadios.forEach(radio => {
                const attributeName = radio.closest('.mb-3').querySelector('.form-label').textContent.replace(':', '');
                currentSelections[attributeName] = radio.value;
            });

            optionGroups.forEach(group => {
                const groupLabel = group.querySelector('.form-label').textContent.replace(':', '');
                if (groupLabel.includes('Kích thước')) {
                    // Kích thước luôn được bật
                    group.querySelectorAll('input[type="radio"]').forEach(r => {
                        r.disabled = false;
                        r.nextElementSibling.classList.remove('disabled');
                    });
                    return;
                }

                const radioButtonsInGroup = group.querySelectorAll('input[type="radio"]');
                radioButtonsInGroup.forEach(radio => {
                    const radioValue = radio.value;
                    const testSelections = { ...currentSelections, [groupLabel]: radioValue };

                    const isAvailable = allVariantsData.some(variant => 
                        variant.attributes['Kích thước'] === testSelections['Kích thước'] && variant.attributes[groupLabel] === radioValue
                    );
                    
                    radio.disabled = !isAvailable;
                    radio.nextElementSibling.classList.toggle('disabled', !isAvailable);
                });
            });

            const finalSelectedRadios = productDetailContainer.querySelectorAll('input[type="radio"]:checked');
            if (finalSelectedRadios.length === optionGroups.length) {
                const finalSelections = {};
                finalSelectedRadios.forEach(radio => {
                    const attributeName = radio.closest('.mb-3').querySelector('.form-label').textContent.replace(':', '');
                    finalSelections[attributeName] = radio.value;
                });
                const foundVariant = allVariantsData.find(variant => 
                    Object.keys(finalSelections).every(key => variant.attributes[key] === finalSelections[key])
                );
                updateProductDisplay(foundVariant);
            } else {
                updateProductDisplay(null);
            }
        }

        function handleOptionChange(event) {
            const changedGroupName = event.target.closest('.mb-3').querySelector('.form-label').textContent.replace(':', '');

            if (changedGroupName === 'Kích thước') {
                const newSize = event.target.value;
                const variantsForSize = allVariantsData.filter(v => v.attributes['Kích thước'] === newSize);
                let targetVariant = variantsForSize.find(v => v.stock > 0) || variantsForSize[0];

                if (targetVariant) {
                    const targetHeight = targetVariant.attributes['Độ cao'];
                    const heightRadioToCheck = document.querySelector(`input[value="${targetHeight}"][name*="do_cao"]`);
                    if(heightRadioToCheck) {
                        heightRadioToCheck.checked = true;
                    }
                }
            }
            updateAllOptions();
        }
        
        async function handleAddToCart(event) {
            event.preventDefault();
            if (!selectedVariant) { alert('Vui lòng chọn đầy đủ các tùy chọn sản phẩm.'); return; }
            const quantity = parseInt(quantityInput.value, 10);
            const originalBtnText = addToCartBtn.textContent;
            addToCartBtn.disabled = true;
            addToCartBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...';
            try {
                const response = await fetch('cart-add.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ variant_id: selectedVariant.id, quantity: quantity })
                });
                const result = await response.json();
                if (result.success) {
                    addToCartBtn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Đã thêm!';
                    if(cartCountEl) { cartCountEl.textContent = result.cart_count; }
                } else { throw new Error(result.message); }
            } catch (error) {
                alert(`Lỗi: ${error.message}`);
                addToCartBtn.innerHTML = originalBtnText;
            } finally {
                setTimeout(() => {
                    addToCartBtn.disabled = false;
                    addToCartBtn.innerHTML = originalBtnText;
                }, 2000);
            }
        }

        function initializeProductDetailPage() {
            const defaultVariant = allVariantsData.find(v => v.is_default);
            if (defaultVariant) {
                Object.entries(defaultVariant.attributes).forEach(([name, value]) => {
                    const radioToSelect = Array.from(document.querySelectorAll('input[type="radio"]')).find(r => r.value === value && r.closest('.mb-3').querySelector('.form-label').textContent.includes(name));
                    if (radioToSelect) { radioToSelect.checked = true; }
                });
            }
            
            const allRadios = productDetailContainer.querySelectorAll('input[type="radio"]');
            allRadios.forEach(radio => radio.addEventListener('change', handleOptionChange));
            
            updateAllOptions();
            addToCartBtn.addEventListener('click', handleAddToCart);
        }

        initializeProductDetailPage();
    }


    // ===================================================================
    // --- LOGIC CHO TRANG DANH SÁCH SẢN PHẨM (products.php) ---
    // ===================================================================
    // --- LOGIC CHO TRANG DANH SÁCH SẢN PHẨM (PHIÊN BẢN HOÀN CHỈNH) ---
const productGrid = document.getElementById('product-grid');
if (productGrid) {
    const sortBySelect = document.getElementById('sort-by');
    const filterInputs = document.querySelectorAll('.filter-input'); // Lấy tất cả các input lọc

    function fetchProducts() {
        productGrid.innerHTML = '<div class="col-12 text-center my-5"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        
        // Sử dụng URLSearchParams để xây dựng URL một cách an toàn và dễ dàng
        const params = new URLSearchParams();
        
        // 1. Lấy giá trị sắp xếp
        params.append('sort', sortBySelect.value);

        // === LOGIC MỚI: LẤY VÀ GỬI ĐI CATEGORY SLUG ===
        const categorySlugInput = document.getElementById('current-category-slug');
        if (categorySlugInput && categorySlugInput.value) {
        params.append('category', categorySlugInput.value);
        }
        
        // 2. Lấy giá trị lọc giá
        const priceRangeInput = document.querySelector('input[name="price_range"]:checked');
        if (priceRangeInput) {
            params.append('price_range', priceRangeInput.value);
        }

        // 3. Lấy giá trị lọc kích thước (có thể có nhiều)
        const sizeInputs = document.querySelectorAll('input[name="size[]"]:checked');
        sizeInputs.forEach(input => {
            params.append('size[]', input.value);
        });

        // 4. Lấy giá trị lọc chất liệu (có thể có nhiều)
        const materialInputs = document.querySelectorAll('input[name="material[]"]:checked');
        materialInputs.forEach(input => {
            params.append('material[]', input.value);
        });

        // Tạo URL cuối cùng
        const url = `ajax-filter-products.php?${params.toString()}`;

        // Gọi AJAX
        fetch(url)
            .then(response => {
                if (!response.ok) { throw new Error('Network response was not ok'); }
                return response.text();
            })
            .then(html => {
                productGrid.innerHTML = html;
            })
            .catch(error => {
                console.error('Lỗi khi tải sản phẩm:', error);
                productGrid.innerHTML = '<p class="text-center col-12 text-danger">Đã có lỗi xảy ra khi tải sản phẩm. Vui lòng thử lại.</p>';
            });
    }

    // Gắn sự kiện 'change' cho tất cả các input lọc và dropdown sắp xếp
    const allFilterElements = document.querySelectorAll('#sort-by, .filter-input');
    allFilterElements.forEach(element => {
        element.addEventListener('change', fetchProducts);
    });
    }

    // ===================================================================
    // ===================================================================
    // --- LOGIC CHO TRANG XÁC THỰC (auth.php) ---
    // ===================================================================
    const authFormContainer = document.getElementById('authTabContent');
    if (authFormContainer) {
        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');
        const messageDiv = document.getElementById('auth-message');

        const handleAuthSubmit = async (form, event) => {
            event.preventDefault(); // Ngăn form gửi đi
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            
            // Hiển thị trạng thái loading
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...';
            messageDiv.innerHTML = ''; // Xóa thông báo cũ

            try {
                const response = await fetch('auth-handler.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    if(result.redirect) {
                        // Nếu đăng nhập thành công, chuyển hướng
                        window.location.href = result.redirect;
                    } else {
                        // Nếu đăng ký thành công, hiển thị thông báo
                        messageDiv.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
                        form.reset(); // Xóa các trường trong form
                    }
                } else {
                    // Nếu có lỗi, hiển thị thông báo lỗi
                    messageDiv.innerHTML = `<div class="alert alert-danger">${result.message}</div>`;
                }

            } catch (error) {
                messageDiv.innerHTML = `<div class="alert alert-danger">Có lỗi xảy ra, vui lòng thử lại.</div>`;
                console.error('Auth Error:', error);
            } finally {
                // Trả lại trạng thái ban đầu cho nút bấm
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            }
        };

        loginForm.addEventListener('submit', (e) => handleAuthSubmit(loginForm, e));
        registerForm.addEventListener('submit', (e) => handleAuthSubmit(registerForm, e));
    }

    // ===================================================================
    // --- LOGIC CHO TRANG TÀI KHOẢN (account.php) ---
    // ===================================================================
    const editAddressModal = document.getElementById('editAddressModal');
    if (editAddressModal) {
        // Xử lý khi modal Sửa được mở
        editAddressModal.addEventListener('show.bs.modal', function (event) {
            // Nút đã kích hoạt modal
            const button = event.relatedTarget;

            // Lấy dữ liệu từ các data-* attribute của nút
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const phone = button.getAttribute('data-phone');
            const address = button.getAttribute('data-address');
            const isDefault = button.getAttribute('data-default');

            // Cập nhật các trường trong form của modal
            const modal = this;
            modal.querySelector('#edit-address-id').value = id;
            modal.querySelector('#edit-full_name').value = name;
            modal.querySelector('#edit-phone_number').value = phone;
            modal.querySelector('#edit-address_line').value = address;
            modal.querySelector('#edit-is_default').checked = (isDefault == '1');
        });
    }

    const deleteAddressModal = document.getElementById('deleteAddressModal');
    if (deleteAddressModal) {
        // Xử lý khi modal Xóa được mở
        deleteAddressModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const modal = this;
            // Cập nhật ID địa chỉ vào form xóa
            modal.querySelector('#delete-address-id').value = id;
        });
    }


    // ===================================================================
    // --- LOGIC CHO TRANG THANH TOÁN (checkout.php) ---
    // ===================================================================
    const savedAddressSelect = document.getElementById('saved_address_select');
    if (savedAddressSelect) {
        // Lấy các ô input cần được điền
        const nameInput = document.getElementById('full_name');
        const phoneInput = document.getElementById('phone_number');
        const addressInput = document.getElementById('address');

        // Gắn sự kiện 'change' cho dropdown
        savedAddressSelect.addEventListener('change', function() {
            // Lấy ra thẻ <option> đang được chọn
            const selectedOption = this.options[this.selectedIndex];
            
            // Nếu người dùng chọn "Nhập địa chỉ mới", không làm gì cả
            if(selectedOption.value === "") {
                return;
            }
            
            // Lấy dữ liệu từ các data-* attribute của option
            const name = selectedOption.getAttribute('data-name');
            const phone = selectedOption.getAttribute('data-phone');
            const address = selectedOption.getAttribute('data-address');

            // Cập nhật giá trị cho các ô input
            nameInput.value = name;
            phoneInput.value = phone;
            addressInput.value = address;
        });
    }

    // ===================================================================
    // --- LOGIC CHO TRANG GIỎ HÀNG (cart.php) ---
    // ===================================================================
    const cartPage = document.querySelector('.cart-page-container');
    if (cartPage) {
        const cartSubtotalEl = document.getElementById('cart-subtotal');
        const cartGrandTotalEl = document.getElementById('cart-grand-total');
        const cartCountEl = document.getElementById('cart-item-count');

        function updateCartTotals() {
            let grandTotal = 0;
            const itemRows = document.querySelectorAll('.cart-item-row');
            if (itemRows.length === 0) {
                // Nếu không còn sản phẩm, reload trang để hiển thị thông báo giỏ hàng trống
                window.location.reload();
            }
            itemRows.forEach(row => {
                const price = parseFloat(row.getAttribute('data-price'));
                const quantity = parseInt(row.querySelector('.quantity-input').value, 10);
                const subtotal = price * quantity;
                row.querySelector('.item-subtotal').textContent = new Intl.NumberFormat('vi-VN').format(subtotal);
                grandTotal += subtotal;
            });
            
            const formattedGrandTotal = new Intl.NumberFormat('vi-VN').format(grandTotal) + 'đ';
            cartSubtotalEl.textContent = formattedGrandTotal;
            cartGrandTotalEl.textContent = formattedGrandTotal;
        }

        function updateCartOnServer(variantId, quantity) {
            fetch('cart-update.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ variant_id: variantId, quantity: quantity })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && cartCountEl) {
                    cartCountEl.textContent = data.cart_count;
                }
            })
            .catch(console.error);
        }

        cartPage.addEventListener('change', function(event) {
            if (event.target.classList.contains('quantity-input')) {
                const quantity = parseInt(event.target.value, 10);
                const row = event.target.closest('.cart-item-row');
                const variantId = row.getAttribute('data-variant-id');
                if (quantity <= 0) {
                    row.remove();
                }
                updateCartTotals();
                updateCartOnServer(variantId, quantity);
            }
        });

        cartPage.addEventListener('click', function(event) {
            if (event.target.closest('.remove-item-btn')) {
                const button = event.target.closest('.remove-item-btn');
                const variantId = button.getAttribute('data-variant-id');
                const row = button.closest('.cart-item-row');
                
                row.remove();
                updateCartTotals();
                updateCartOnServer(variantId, 0);
            }
        });
    }

     // =======================================================
        // === KHỐI MÃ MỚI: XỬ LÝ NHẤN VÀO LINK ĐÁNH GIÁ ===
        // =======================================================
        const scrollToReviewsLink = document.getElementById('scroll-to-reviews');
        const reviewsTabButton = document.getElementById('reviews-tab-button');
        const productInfoTab = document.getElementById('productInfoTab');

        if (scrollToReviewsLink && reviewsTabButton && productInfoTab) {
            scrollToReviewsLink.addEventListener('click', function(event) {
                event.preventDefault(); // Ngăn hành vi nhảy trang mặc định của link

                // Dùng API của Bootstrap để tạo một instance của Tab và kích hoạt nó
                const tab = new bootstrap.Tab(reviewsTabButton);
                tab.show();

                // Cuộn trang mượt mà đến khu vực tab
                productInfoTab.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        }
        // === KẾT THÚC KHỐI MÃ MỚI ===






});