/**
 * Main JavaScript
 * Toko Islami - Online Shop & Artikel
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initMobileMenu();
    initQuantitySelectors();
    initAddToCart();
    initAlertDismiss();
});

/**
 * Mobile Menu Toggle
 */
function initMobileMenu() {
    const menuBtn = document.querySelector('.mobile-menu-btn');
    const navMenu = document.querySelector('.nav-menu');
    
    if (menuBtn && navMenu) {
        menuBtn.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            this.innerHTML = navMenu.classList.contains('active') ? '✕' : '☰';
        });
    }
}

/**
 * Quantity Selectors
 */
function initQuantitySelectors() {
    const qtyContainers = document.querySelectorAll('.cart-item-quantity, .quantity-selector');
    
    qtyContainers.forEach(container => {
        const minusBtn = container.querySelector('.qty-minus');
        const plusBtn = container.querySelector('.qty-plus');
        const input = container.querySelector('.qty-input');
        
        if (minusBtn && plusBtn && input) {
            minusBtn.addEventListener('click', () => {
                let value = parseInt(input.value) || 1;
                if (value > 1) {
                    input.value = value - 1;
                    updateCartQuantity(input);
                }
            });
            
            plusBtn.addEventListener('click', () => {
                let value = parseInt(input.value) || 1;
                const max = parseInt(input.getAttribute('max')) || 999;
                if (value < max) {
                    input.value = value + 1;
                    updateCartQuantity(input);
                }
            });
            
            input.addEventListener('change', () => {
                updateCartQuantity(input);
            });
        }
    });
}

/**
 * Update Cart Quantity via AJAX
 */
function updateCartQuantity(input) {
    const productId = input.getAttribute('data-product-id');
    const quantity = parseInt(input.value);
    
    if (productId && quantity > 0) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'cart.php';
        form.innerHTML = `
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="product_id" value="${productId}">
            <input type="hidden" name="quantity" value="${quantity}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

/**
 * Add to Cart with Animation
 */
function initAddToCart() {
    const addButtons = document.querySelectorAll('.add-to-cart-btn');
    
    addButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            // Add animation
            this.classList.add('adding');
            this.innerHTML = '<span>✓</span> Ditambahkan';
            
            setTimeout(() => {
                this.classList.remove('adding');
                this.innerHTML = '<span>🛒</span> Tambah';
            }, 2000);
        });
    });
}

/**
 * Alert Auto-dismiss
 */
function initAlertDismiss() {
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
}

/**
 * Format number to Rupiah
 */
function formatRupiah(number) {
    return 'Rp ' + number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

/**
 * Confirm Delete
 */
function confirmDelete(message = 'Apakah Anda yakin ingin menghapus?') {
    return confirm(message);
}

/**
 * Image Preview on Upload
 */
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * Smooth Scroll
 */
function scrollToElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
}
