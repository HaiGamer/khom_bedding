document.addEventListener('DOMContentLoaded', function () {
    
    // Khai báo biến BASE_URL nếu nó được định nghĩa trong header.php
    const BASE_URL = window.BASE_URL || '';
    
    // === KHAI BÁO CÁC BIẾN TOÀN CỤC CHO HEADER ===
    const cartCountEl = document.getElementById('cart-item-count');
    const cartCountMobile = document.getElementById('cart-item-count-mobile');
    
    // =======================================================
    // --- LOGIC CHO FORM ĐĂNG KÝ NHẬN TIN Ở FOOTER ---
    // =======================================================
    const subscriptionForm = document.getElementById('subscription-form');
    if (subscriptionForm) {
        const messageDiv = document.getElementById('subscription-message');
        const emailInput = subscriptionForm.querySelector('input[name="email"]');
        const submitButton = subscriptionForm.querySelector('button[type="submit"]');

        subscriptionForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const originalButtonHTML = submitButton.innerHTML;
            
            submitButton.disabled = true;
            submitButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>`;
            messageDiv.textContent = '';
            
            const formData = new FormData(this);

            fetch(`${BASE_URL}subscribe-handler.php`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                messageDiv.textContent = data.message;
                if(data.success) {
                    messageDiv.className = 'small mt-2 text-success';
                    emailInput.value = ''; // Xóa email sau khi thành công
                } else {
                    messageDiv.className = 'small mt-2 text-danger';
                }
            })
            .catch(error => {
                console.error('Lỗi đăng ký nhận tin:', error);
                messageDiv.textContent = 'Có lỗi xảy ra, vui lòng thử lại.';
                messageDiv.className = 'small mt-2 text-danger';
            })
            .finally(() => {
                // Trả lại trạng thái ban đầu cho nút bấm
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonHTML;
            });
        });
    }


    // =======================================================
    // #1 LOGIC CHO LIVE SEARCH Ở HEADER (Chạy trên tất cả các trang)
    // =======================================================
    const desktopSearchInput = document.getElementById('live-search-input');
    const desktopResultsContainer = document.getElementById('search-results-container');
    const mobileSearchInput = document.getElementById('mobile-search-input');
    const mobileResultsContainer = document.getElementById('mobile-search-results');
    let searchTimeout;

    function performLiveSearch(searchTerm, resultsContainer) {
        if (searchTerm.length < 2) {
            resultsContainer.innerHTML = '';
            resultsContainer.style.display = 'none';
            return;
        }
        const url = `${BASE_URL}ajax-search.php?term=${encodeURIComponent(searchTerm)}`;
        
        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok.');
                return response.json();
            })
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
            .catch(error => {
                console.error('Lỗi tìm kiếm:', error);
                resultsContainer.style.display = 'none';
            });
    }

    if (desktopSearchInput && desktopResultsContainer) {
        desktopSearchInput.addEventListener('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performLiveSearch(this.value, desktopResultsContainer);
            }, 300);
        });
    }

    if (mobileSearchInput && mobileResultsContainer) {
        mobileSearchInput.addEventListener('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performLiveSearch(this.value, mobileResultsContainer);
            }, 300);
        });
    }
    
    document.addEventListener('click', function(event) {
        const searchContainer = document.querySelector('.search-container');
        if (searchContainer && !searchContainer.contains(event.target)) {
            if(desktopResultsContainer) desktopResultsContainer.style.display = 'none';
        }
    });

    // ===================================================================
    // #2 LOGIC CHO TRANG CHI TIẾT SẢN PHẨM (product-detail.php)
    // ===================================================================
    const productDetailContainer = document.getElementById('product-detail-page-container');
    if (productDetailContainer && typeof allVariantsData !== 'undefined' && allVariantsData.length > 0) {
        const priceEl = document.getElementById('product-price');
        const skuEl = document.getElementById('product-sku');
        const skuContainer = document.getElementById('product-sku-container');
        const stockStatusEl = document.getElementById('stock-status');
        const addToCartBtn = document.getElementById('add-to-cart-btn');
        const quantityInput = productDetailContainer.querySelector('input[type="number"]');
        const optionContainer = document.getElementById('product-options');
        const optionGroups = Array.from(optionContainer.querySelectorAll('.mb-3'));
        let selectedVariant = null;

        function updateProductDisplay(variant) {
            selectedVariant = variant;
            if (variant) {
                let priceHTML = '';
                const salePrice = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(variant.price);
                if (variant.original_price && parseFloat(variant.original_price) > parseFloat(variant.price)) {
                    const originalPrice = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(variant.original_price);
                    priceHTML = `<span class="me-2">${salePrice}</span><del class="price-original">${originalPrice}</del>`;
                } else {
                    priceHTML = `<span>${salePrice}</span>`;
                }
                priceEl.innerHTML = priceHTML;
                
                skuEl.textContent = variant.sku;
                skuContainer.style.display = 'inline-block';

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
                skuContainer.style.display = 'none';
                stockStatusEl.textContent = '';
                addToCartBtn.disabled = true;
                addToCartBtn.textContent = 'Vui lòng chọn tùy chọn';
            }
        }

        function updateAllOptions() {
            const currentSelections = {};
            const selectedRadios = optionContainer.querySelectorAll('input[type="radio"]:checked');
            selectedRadios.forEach(radio => {
                const attributeName = radio.closest('.mb-3').querySelector('.form-label').textContent.replace(':', '');
                currentSelections[attributeName] = radio.value;
            });

            optionGroups.forEach(group => {
                const groupLabel = group.querySelector('.form-label').textContent.replace(':', '');
                if (groupLabel.includes('Kích thước')) {
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
                    if (radio.checked && !isAvailable) {
                        radio.checked = false;
                    }
                });
            });

            const finalSelectedRadios = optionContainer.querySelectorAll('input[type="radio"]:checked');
            if (finalSelectedRadios.length === optionGroups.length) {
                const finalSelections = {};
                finalSelectedRadios.forEach(radio => {
                    const attributeName = radio.closest('.mb-3').querySelector('.form-label').textContent.replace(':', '');
                    finalSelections[attributeName] = radio.value;
                });
                const foundVariant = allVariantsData.find(variant => Object.keys(finalSelections).every(key => variant.attributes[key] === finalSelections[key]));
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
                    if(heightRadioToCheck) { heightRadioToCheck.checked = true; }
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
                const response = await fetch(`${BASE_URL}cart-add.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ variant_id: selectedVariant.id, quantity: quantity })
                });
                const result = await response.json();
                if (result.success) {
                    addToCartBtn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Đã thêm vào giỏ hàng!';
                    const toastElement = document.getElementById('add-to-cart-toast');
                    if (toastElement) {
                        const toast = new bootstrap.Toast(toastElement);
                        toast.show();
                    }
                    const cartCountMobile = document.getElementById('cart-item-count-mobile');
                    if(cartCountEl) { cartCountEl.textContent = result.cart_count; }
                    if(cartCountMobile) { cartCountMobile.textContent = result.cart_count; }
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

        // === HÀM KHỞI TẠO ĐÃ ĐƯỢC NÂNG CẤP ===
        function initializeProductDetailPage() {
            // Gắn các sự kiện cần thiết trước
            const allRadios = optionContainer.querySelectorAll('input[type="radio"]');
            allRadios.forEach(radio => radio.addEventListener('change', handleOptionChange));
            addToCartBtn.addEventListener('click', handleAddToCart);

            // TÌM VÀ CHỌN PHIÊN BẢN MẶC ĐỊNH
            const defaultVariant = allVariantsData.find(v => v.is_default);

            if (defaultVariant) {
                // Lặp qua các thuộc tính của phiên bản mặc định
                Object.entries(defaultVariant.attributes).forEach(([name, value]) => {
                    // Tìm đúng radio button và check vào nó
                    const normalizedName = name.toLowerCase().replace(/ /g, '_');
                    const radioToSelect = document.querySelector(`input[name="option_${normalizedName}"][value="${value}"]`);
                    if (radioToSelect) {
                        radioToSelect.checked = true;
                    }
                });
            }

            // Gọi hàm cập nhật tổng thể để hiển thị đúng giá, SKU,
            // và trạng thái bật/tắt của các nút dựa trên lựa chọn mặc định
            updateAllOptions();
        }


        initializeProductDetailPage();

        const scrollToReviewsLink = document.getElementById('scroll-to-reviews');
        const reviewsTabButton = document.getElementById('reviews-tab-button');
        const productInfoTab = document.getElementById('productInfoTab');
        if (scrollToReviewsLink && reviewsTabButton && productInfoTab) {
            scrollToReviewsLink.addEventListener('click', function(event) {
                event.preventDefault();
                const tab = new bootstrap.Tab(reviewsTabButton);
                tab.show();
                productInfoTab.scrollIntoView({ behavior: 'smooth' });
            });
        }
    }


    // ===================================================================
    // #3 LOGIC CHO TRANG DANH SÁCH SẢN PHẨM (products.php)
    // ===================================================================
    const productGrid = document.getElementById('product-grid');
    if (productGrid) {
        const sortBySelect = document.getElementById('sort-by');
        const filterInputs = document.querySelectorAll('.filter-input');
        const paginationContainer = document.getElementById('pagination-container');
        const productListContainer = productGrid.closest('.col-lg-9');

        function fetchProducts(page = 1) {
            productGrid.innerHTML = '<div class="col-12 text-center my-5"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
            const params = new URLSearchParams();
            params.append('sort', sortBySelect.value);
            params.append('page', page);
            const categorySlugInput = document.getElementById('current-category-slug');
            if (categorySlugInput && categorySlugInput.value) { params.append('category', categorySlugInput.value); }
            const collectionSlugInput = document.getElementById('current-collection-slug');
            if (collectionSlugInput && collectionSlugInput.value) { params.append('collection', collectionSlugInput.value); }
            const priceRangeInput = document.querySelector('input[name="price_range"]:checked');
            if (priceRangeInput) { params.append('price_range', priceRangeInput.value); }
            const url = `${BASE_URL}ajax-filter-products.php?${params.toString()}`;
            fetch(url)
                .then(response => {
                    if (!response.ok) { throw new Error('Network response was not ok'); }
                    return response.json();
                })
                .then(data => {
                    if(data.error) { throw new Error(data.error); }
                    productGrid.innerHTML = data.products_html;
                    if (paginationContainer) { paginationContainer.innerHTML = data.pagination_html; }
                })
                .catch(error => {
                    console.error('Lỗi khi tải sản phẩm:', error);
                    productGrid.innerHTML = '<p class="text-center col-12 text-danger">Đã có lỗi xảy ra. Vui lòng thử lại.</p>';
                });
        }
        const allFilterElements = document.querySelectorAll('#sort-by, .filter-input');
        allFilterElements.forEach(element => {
            element.addEventListener('change', () => fetchProducts(1));
        });
        if (paginationContainer) {
            paginationContainer.addEventListener('click', function(event){
                if (event.target.matches('a.page-link')) {
                    event.preventDefault();
                    const page = event.target.getAttribute('data-page');
                    const isDisabled = event.target.closest('.page-item').classList.contains('disabled');
                    if (page && !isDisabled) {
                        fetchProducts(page);
                        if (productListContainer) { productListContainer.scrollIntoView({ behavior: 'smooth' }); }
                    }
                }
            });
        }
    }

    // ===================================================================
    // #4 LOGIC CHO TRANG GIỎ HÀNG (cart.php)
    // ===================================================================
    const cartPage = document.querySelector('.cart-page-container');
    if (cartPage) {
        const cartSubtotalEl = document.getElementById('cart-subtotal');
        const cartGrandTotalEl = document.getElementById('cart-grand-total');

        function updateCartTotals() {
            let grandTotal = 0;
            const itemRows = document.querySelectorAll('.cart-item-row');
            if (itemRows.length === 0) { window.location.reload(); }
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
            fetch(`${BASE_URL}cart-update.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ variant_id: variantId, quantity: quantity })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if(cartCountEl) { cartCountEl.textContent = data.cart_count; }
                    if(cartCountMobile) { cartCountMobile.textContent = data.cart_count; }
                }
            })
            .catch(console.error);
        }

        cartPage.addEventListener('change', function(event) {
            if (event.target.classList.contains('quantity-input')) {
                const quantity = parseInt(event.target.value, 10);
                const row = event.target.closest('.cart-item-row');
                const variantId = row.getAttribute('data-variant-id');
                if (quantity <= 0) { row.remove(); }
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
    
    // ===================================================================
    // #5 LOGIC CHO TRANG XÁC THỰC (auth.php)
    // ===================================================================
    const authFormContainer = document.getElementById('authTabContent');
    if (authFormContainer) {
        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');
        const messageDiv = document.getElementById('auth-message');

        const handleAuthSubmit = async (form, event) => {
            event.preventDefault();
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...';
            messageDiv.innerHTML = '';
            try {
                const response = await fetch(`${BASE_URL}auth-handler.php`, {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    if(result.redirect) {
                        window.location.href = result.redirect;
                    } else {
                        messageDiv.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
                        form.reset();
                    }
                } else {
                    messageDiv.innerHTML = `<div class="alert alert-danger">${result.message}</div>`;
                }
            } catch (error) {
                messageDiv.innerHTML = `<div class="alert alert-danger">Có lỗi xảy ra, vui lòng thử lại.</div>`;
                console.error('Auth Error:', error);
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            }
        };
        if(loginForm) loginForm.addEventListener('submit', (e) => handleAuthSubmit(loginForm, e));
        if(registerForm) registerForm.addEventListener('submit', (e) => handleAuthSubmit(registerForm, e));
    }
});