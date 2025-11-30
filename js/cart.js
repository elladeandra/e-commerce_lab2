const CartActions = (() => {
  const actionsBase = window.location.pathname.includes('/view/') ? '../actions/' : 'actions/';
  const endpoints = {
    add: `${actionsBase}add_to_cart_action.php`,
    update: `${actionsBase}update_quantity_action.php`,
    remove: `${actionsBase}remove_from_cart_action.php`,
    empty: `${actionsBase}empty_cart_action.php`,
  };

  const toastContainer = () => {
    let container = document.getElementById('cartToastContainer');
    if (!container) {
      container = document.createElement('div');
      container.id = 'cartToastContainer';
      container.className = 'toast-container';
      document.body.appendChild(container);
    }
    return container;
  };

  const showToast = (type, message) => {
    const container = toastContainer();
    const toast = document.createElement('div');
    toast.className = `toast-message ${type}`;
    toast.innerHTML = `
      <span>${message}</span>
      <button type="button" class="toast-close" aria-label="Close notification">&times;</button>
    `;
    container.appendChild(toast);

    const removeToast = () => {
      toast.classList.add('leaving');
      setTimeout(() => toast.remove(), 150);
    };

    toast.querySelector('.toast-close').addEventListener('click', removeToast);
    setTimeout(removeToast, 4000);
  };

  const fetchPost = async (url, payload) => {
    const formData = new URLSearchParams();
    Object.entries(payload).forEach(([key, value]) => formData.append(key, value));

    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: formData.toString(),
    });

    if (!response.ok) {
      const text = await response.text();
      throw new Error(text || 'Server error');
    }

    return response.json();
  };

  const updateCartSummaryUI = (cart) => {
    if (!cart) return;

    const countEl = document.getElementById('summaryItemCount') || document.getElementById('checkoutItemCount');
    const subtotalEl = document.getElementById('summarySubtotal') || document.getElementById('checkoutSubtotal');
    const layout = document.querySelector('.cart-layout');
    const emptyState = document.getElementById('cartEmptyState');

    if (countEl) {
      countEl.textContent = cart.totals.count ?? 0;
    }

    if (subtotalEl) {
      const value = Number(cart.totals.subtotal || 0).toFixed(2);
      subtotalEl.textContent = `GHS ${value}`;
    }

    const simulator = document.getElementById('simulatePaymentBtn');
    if (simulator && cart.totals) {
      simulator.dataset.totalAmount = cart.totals.subtotal || 0;
      simulator.disabled = Number(cart.totals.subtotal) <= 0;
    }

    if (layout) {
      if (!cart.items.length) {
        layout.hidden = true;
        if (emptyState) {
          emptyState.hidden = false;
        }
        if (simulator) {
          simulator.dataset.totalAmount = '0';
          simulator.disabled = true;
        }
      } else {
        layout.hidden = false;
        if (emptyState) {
          emptyState.hidden = true;
        }

        const itemsMap = new Map(cart.items.map((item) => [String(item.product_id), item]));
        document.querySelectorAll('.cart-item').forEach((itemEl) => {
          const productId = itemEl.dataset.productId;
          const data = itemsMap.get(productId);

          if (!data) {
            itemEl.remove();
            return;
          }

          const qtyInput = itemEl.querySelector('.cart-qty-input');
          if (qtyInput) {
            qtyInput.value = data.qty;
            qtyInput.dataset.lastKnown = data.qty;
          }

          const subtotal = Number(data.product_price) * Number(data.qty);
          const subtotalEl = itemEl.querySelector('.subtotal-value');
          if (subtotalEl) {
            subtotalEl.dataset.subtotal = subtotal.toFixed(2);
            subtotalEl.textContent = `GHS ${subtotal.toFixed(2)}`;
          }
        });

        // Remove any stray empty nodes if items shrink to zero
        if (!cart.items.length) {
          layout.hidden = true;
          if (emptyState) emptyState.hidden = false;
          if (simulator) {
            simulator.dataset.totalAmount = '0';
            simulator.disabled = true;
          }
        }
      }
    }
  };

  const setLoading = (element, isLoading, loadingText = 'Processing...') => {
    if (!element) return;

    if (isLoading) {
      element.dataset.originalText = element.dataset.originalText || element.textContent;
      element.classList.add('is-loading');
      element.disabled = true;
      element.textContent = loadingText;
    } else {
      element.classList.remove('is-loading');
      element.disabled = false;
      if (element.dataset.originalText) {
        element.textContent = element.dataset.originalText;
        delete element.dataset.originalText;
      }
    }
  };

  const handleAddToCart = async (event) => {
    event.preventDefault();
    const button = event.currentTarget;
    const productId = Number(button.dataset.productId);
    const quantity = Number(button.dataset.quantity || button.getAttribute('data-quantity') || 1);

    if (!productId) {
      showToast('error', 'Invalid product selected.');
      return;
    }

    setLoading(button, true, 'Adding...');

    try {
      const result = await fetchPost(endpoints.add, { product_id: productId, quantity });

      if (result.status === 'success') {
        showToast('success', result.message);
        updateCartSummaryUI(result.data?.cart);
      } else if (result.status === 'warning') {
        showToast('warning', result.message || 'Unable to add to cart.');
        updateCartSummaryUI(result.data?.cart);
      } else {
        showToast('error', result.message || 'Unable to add to cart.');
      }
    } catch (error) {
      console.error(error);
      showToast('error', 'Unable to add product to cart.');
    } finally {
      setLoading(button, false);
    }
  };

  const handleQuantityChange = async (productId, qtyInput, sourceButton = null) => {
    const quantity = Math.max(1, Number(qtyInput.value || 1));
    qtyInput.value = quantity;

    if (String(quantity) === String(qtyInput.dataset.lastKnown)) {
      return;
    }

    if (sourceButton) {
      setLoading(sourceButton, true);
    }

    try {
      const result = await fetchPost(endpoints.update, {
        product_id: productId,
        quantity,
      });

      if (result.status === 'success') {
        showToast('success', result.message);
        qtyInput.dataset.lastKnown = quantity;
        updateCartSummaryUI(result.data?.cart);
      } else if (result.status === 'warning') {
        showToast('warning', result.message || 'Could not update cart.');
        qtyInput.dataset.lastKnown = quantity;
        updateCartSummaryUI(result.data?.cart);
      } else {
        showToast('error', result.message || 'Could not update cart.');
        qtyInput.value = qtyInput.dataset.lastKnown || 1;
      }
    } catch (error) {
      console.error(error);
      showToast('error', 'Could not update cart at this time.');
      qtyInput.value = qtyInput.dataset.lastKnown || 1;
    } finally {
      if (sourceButton) {
        setLoading(sourceButton, false);
      }
    }
  };

  const handleRemoveItem = async (productId, trigger) => {
    setLoading(trigger, true, 'Removing...');

    try {
      const result = await fetchPost(endpoints.remove, { product_id: productId });

      if (result.status === 'success') {
        showToast('success', result.message);
        updateCartSummaryUI(result.data?.cart);
      } else if (result.status === 'warning') {
        showToast('warning', result.message || 'Unable to remove item.');
        updateCartSummaryUI(result.data?.cart);
      } else {
        showToast('error', result.message || 'Unable to remove item.');
      }
    } catch (error) {
      console.error(error);
      showToast('error', 'Unable to remove item from cart.');
    } finally {
      setLoading(trigger, false);
    }
  };

  const handleEmptyCart = async (trigger) => {
    setLoading(trigger, true, 'Clearing...');

    try {
      const result = await fetchPost(endpoints.empty, {});
      if (result.status === 'success') {
        showToast('success', result.message);
        updateCartSummaryUI(result.data?.cart);
      } else if (result.status === 'warning') {
        showToast('warning', result.message || 'Unable to empty cart.');
      } else {
        showToast('error', result.message || 'Unable to empty cart.');
      }
    } catch (error) {
      console.error(error);
      showToast('error', 'Unable to empty cart at this time.');
    } finally {
      setLoading(trigger, false);
    }
  };

  const bindCartPageEvents = () => {
    const cartContainer = document.querySelector('.cart-items');
    if (!cartContainer) return;

    cartContainer.addEventListener('click', (event) => {
      const itemEl = event.target.closest('.cart-item');
      if (!itemEl) return;

      const productId = Number(itemEl.dataset.productId);
      const qtyInput = itemEl.querySelector('.cart-qty-input');

      if (event.target.closest('.remove-item-btn')) {
        handleRemoveItem(productId, event.target.closest('.remove-item-btn'));
      }

      if (event.target.closest('.qty-btn')) {
        const button = event.target.closest('.qty-btn');
        if (!qtyInput) return;
        const currentQty = Number(qtyInput.value || 1);
        if (button.classList.contains('increment')) {
          qtyInput.value = currentQty + 1;
        } else if (button.classList.contains('decrement')) {
          qtyInput.value = Math.max(1, currentQty - 1);
        }
        handleQuantityChange(productId, qtyInput, button);
      }
    });

    cartContainer.addEventListener('change', (event) => {
      if (event.target.classList.contains('cart-qty-input')) {
        const itemEl = event.target.closest('.cart-item');
        if (!itemEl) return;
        const productId = Number(itemEl.dataset.productId);
        handleQuantityChange(productId, event.target);
      }
    });

    const emptyCartBtn = document.querySelector('.empty-cart-btn');
    if (emptyCartBtn) {
      emptyCartBtn.addEventListener('click', () => handleEmptyCart(emptyCartBtn));
    }
  };

  const bindAddToCartButtons = () => {
    document.querySelectorAll('.add-to-cart-btn').forEach((button) => {
      button.addEventListener('click', handleAddToCart);
    });
  };

  const bindToastEvents = () => {
    document.addEventListener('click', (event) => {
      if (event.target.classList.contains('toast-close')) {
        event.target.closest('.toast-message')?.remove();
      }
    });
  };

  return {
    init() {
      bindAddToCartButtons();
      bindCartPageEvents();
      bindToastEvents();
    },
    updateCartSummaryUI,
    showToast,
    endpoints,
  };
})();

document.addEventListener('DOMContentLoaded', () => {
  CartActions.init();
});

