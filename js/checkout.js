document.addEventListener('DOMContentLoaded', () => {
  const simulateBtn = document.getElementById('simulatePaymentBtn');
  const modal = document.getElementById('paymentModal');
  const modalAmount = document.getElementById('modalAmount');
  const cancelBtn = document.getElementById('cancelPaymentBtn');
  const confirmBtn = document.getElementById('confirmPaymentBtn');
  const closeBtn = modal?.querySelector('.modal-close');
  const feedback = document.getElementById('checkoutFeedback');
  const actionsBase = window.location.pathname.includes('/view/') ? '../actions/' : 'actions/';
  const checkoutEndpoint = `${actionsBase}process_checkout_action.php`;

  if (!simulateBtn || !modal || !modalAmount || !confirmBtn) return;

  const showFeedback = (type, message) => {
    if (!feedback) {
      CartActions.showToast(type, message);
      return;
    }
    feedback.classList.remove('success', 'error', 'warning');
    feedback.classList.add(type);
    feedback.textContent = message;
    feedback.hidden = false;
  };

  const openModal = () => {
    const amount = Number(simulateBtn.dataset.totalAmount || 0);
    const currencySelect = document.getElementById('checkoutCurrency');
    const currency = currencySelect ? currencySelect.value : 'USD';
    modalAmount.textContent = `${currency} ${amount.toFixed(2)}`;
    modal.dataset.currency = currency;
    modal.dataset.amount = amount.toString();
    modal.hidden = false;
    modal.classList.add('visible');
  };

  const closeModal = () => {
    modal.hidden = true;
    modal.classList.remove('visible');
  };

  const setButtonState = (button, loading) => {
    if (!button) return;
    if (loading) {
      button.dataset.originalText = button.dataset.originalText || button.textContent;
      button.disabled = true;
      button.textContent = 'Processing...';
    } else {
      button.disabled = false;
      if (button.dataset.originalText) {
        button.textContent = button.dataset.originalText;
        delete button.dataset.originalText;
      }
    }
  };

  const processCheckout = async () => {
    setButtonState(confirmBtn, true);
    try {
      const currency = modal.dataset.currency || 'USD';
      const response = await fetch(checkoutEndpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({ currency }).toString(),
      });

      if (!response.ok) {
        const errorText = await response.text();
        throw new Error(errorText || 'Checkout failed.');
      }

      const result = await response.json();
      if (result.status === 'success') {
        closeModal();
        CartActions.showToast('success', result.message);
        CartActions.updateCartSummaryUI(result.data?.cart);

        const template = document.getElementById('checkoutResultTemplate');
        const container = document.querySelector('.checkout-main .container');
        if (template && container) {
          container.innerHTML = '';
          const clone = template.content.cloneNode(true);
          const refEl = clone.querySelector('[data-ref]');
          if (refEl) {
            refEl.textContent = result.data?.order_reference || result.data?.invoice_no || 'N/A';
          }
          container.appendChild(clone);
        }

        showFeedback('success', `Order ${result.data?.order_reference || ''} confirmed.`);
      } else {
        CartActions.showToast('error', result.message || 'Checkout failed.');
        showFeedback('error', result.message || 'Checkout failed.');
      }
    } catch (error) {
      console.error(error);
      CartActions.showToast('error', 'Unable to complete checkout.');
      showFeedback('error', 'Unable to complete checkout. Please try again.');
    } finally {
      setButtonState(confirmBtn, false);
    }
  };

  simulateBtn.addEventListener('click', () => {
    const amount = Number(simulateBtn.dataset.totalAmount || 0);
    if (!amount || amount <= 0) {
      CartActions.showToast('warning', 'Your cart is empty. Add items before checking out.');
      return;
    }
    openModal();
  });

  cancelBtn?.addEventListener('click', () => {
    closeModal();
    CartActions.showToast('warning', 'Payment cancelled.');
  });

  closeBtn?.addEventListener('click', () => {
    closeModal();
  });

  confirmBtn.addEventListener('click', processCheckout);

  modal.addEventListener('click', (event) => {
    if (event.target === modal) {
      closeModal();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !modal.hidden) {
      closeModal();
    }
  });
});

