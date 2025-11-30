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
    const currency = currencySelect ? currencySelect.value : 'GHS';
    modalAmount.textContent = `${currency} ${amount.toFixed(2)}`;
    modal.dataset.currency = currency;
    modal.dataset.amount = amount.toString();
    modal.removeAttribute('hidden');
    modal.classList.add('visible');
    document.body.style.overflow = 'hidden';
  };

  const closeModal = () => {
    modal.setAttribute('hidden', '');
    modal.classList.remove('visible');
    document.body.style.overflow = '';
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
      // Get total amount
      const amount = parseFloat(modal.dataset.amount || 0);
      
      if (!amount || amount <= 0) {
        CartActions.showToast('error', 'Invalid payment amount');
        setButtonState(confirmBtn, false);
        return;
      }
      
      // Prompt for customer email
      const customerEmail = prompt('Please enter your email for payment:', '');
      
      if (!customerEmail) {
        CartActions.showToast('error', 'Email is required for payment');
        setButtonState(confirmBtn, false);
        return;
      }
      
      // Validate email format
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(customerEmail)) {
        CartActions.showToast('error', 'Please enter a valid email address');
        setButtonState(confirmBtn, false);
        return;
      }
      
      // Initialize Paystack transaction
      const initResponse = await fetch(`${actionsBase}paystack_init_transaction.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          amount: amount,
          email: customerEmail
        })
      });
      
      if (!initResponse.ok) {
        throw new Error('Failed to initialize payment');
      }
      
      const initData = await initResponse.json();
      
      if (initData.status === 'success') {
        // Close modal
        closeModal();
        
        // Show success message
        CartActions.showToast('success', 'Redirecting to secure payment...');
        
        // Redirect to Paystack payment page
        setTimeout(() => {
          window.location.href = initData.authorization_url;
        }, 1000);
      } else {
        throw new Error(initData.message || 'Failed to initialize payment');
      }
      
    } catch (error) {
      console.error('Payment error:', error);
      CartActions.showToast('error', error.message || 'Unable to initialize payment. Please try again.');
      showFeedback('error', error.message || 'Unable to initialize payment. Please try again.');
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
    if (event.key === 'Escape' && !modal.hasAttribute('hidden')) {
      closeModal();
    }
  });
});

