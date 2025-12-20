// cart.js - Dynamic Cart Calculator

// Wait for DOM to be fully loaded
window.addEventListener('DOMContentLoaded', function() {
  console.log('Cart script loaded'); // Debug log
  
  // Format currency ke Rupiah
  function formatRupiah(number) {
    return 'Rp' + number.toLocaleString('id-ID');
  }

  // Calculate total & update summary
  function updateCartSummary() {
    const cartItems = document.querySelectorAll('.cart-item');
    let totalProduct = 0;
    const shippingCost = 350000;

    console.log('Updating cart, found items:', cartItems.length); // Debug log

    cartItems.forEach(item => {
      const price = parseInt(item.getAttribute('data-price'));
      const qtyInput = item.querySelector('.qty-input');
      const qty = parseInt(qtyInput.value) || 1;
      
      // Calculate subtotal per item
      const subtotal = price * qty;
      totalProduct += subtotal;

      // Update item price display
      const itemPriceDisplay = item.querySelector('.item-price');
      if (itemPriceDisplay) {
        itemPriceDisplay.textContent = formatRupiah(subtotal);
      }

      console.log(`Item: ${price} x ${qty} = ${subtotal}`); // Debug log
    });

    // Calculate grand total
    const grandTotal = totalProduct + shippingCost;

    console.log('Total Product:', totalProduct, 'Grand Total:', grandTotal); // Debug log

    // Update summary display
    const totalProductElement = document.getElementById('total-product');
    const shippingCostElement = document.getElementById('shipping-cost');
    const grandTotalElement = document.getElementById('grand-total');

    if (totalProductElement) totalProductElement.textContent = formatRupiah(totalProduct);
    if (shippingCostElement) shippingCostElement.textContent = formatRupiah(shippingCost);
    if (grandTotalElement) grandTotalElement.textContent = formatRupiah(grandTotal);
  }

  // Setup quantity controls
  function setupQuantityControls() {
    const cartItems = document.querySelectorAll('.cart-item');

    cartItems.forEach(item => {
      const qtyInput = item.querySelector('.qty-input');
      const minusBtn = item.querySelector('.qty-minus');
      const plusBtn = item.querySelector('.qty-plus');

      if (!qtyInput || !minusBtn || !plusBtn) {
        console.error('Missing quantity controls in item'); // Debug log
        return;
      }

      // Plus button
      plusBtn.addEventListener('click', function(e) {
        e.preventDefault();
        let currentQty = parseInt(qtyInput.value) || 1;
        qtyInput.value = currentQty + 1;
        updateCartSummary();
      });

      // Minus button
      minusBtn.addEventListener('click', function(e) {
        e.preventDefault();
        let currentQty = parseInt(qtyInput.value) || 1;
        if (currentQty > 1) {
          qtyInput.value = currentQty - 1;
          updateCartSummary();
        }
      });

      // Direct input change
      qtyInput.addEventListener('change', function() {
        let currentQty = parseInt(this.value);
        if (currentQty < 1 || isNaN(currentQty)) {
          this.value = 1;
        }
        updateCartSummary();
      });

      // Also update on input (real-time)
      qtyInput.addEventListener('input', function() {
        let currentQty = parseInt(this.value);
        if (currentQty >= 1 && !isNaN(currentQty)) {
          updateCartSummary();
        }
      });
    });

    console.log('Quantity controls setup complete'); // Debug log
  }

  // Initialize
  setupQuantityControls();
  updateCartSummary(); // Initial calculation

  console.log('Cart initialization complete'); // Debug log
});
