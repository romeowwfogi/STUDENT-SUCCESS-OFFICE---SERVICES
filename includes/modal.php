<style>
    :root {
        --bg: #f6f8fb;
        --card: #ffffff;
        --muted: #6b7280;
        --accent: #2E7D32;
        --cancel: #e5e7eb;
        --text: #111827;
    }

    .message-modalv1-modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.4);
        justify-content: center;
        align-items: center;
        /* Ensure message modal renders above edit/view/preview overlays */
        z-index: 1300;
    }

    .message-modalv1-modal.active {
        display: flex;
    }

    .message-modalv1-content {
        background: var(--card);
        max-width: 380px;
        width: 100%;
        max-height: 90vh;
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        padding: 24px 20px;
        margin: auto;
        display: flex;
        flex-direction: column;
        gap: 20px;
        overflow: hidden;
    }

    .message-modalv1-body {
        display: grid;
        align-items: flex-start;
        gap: 16px;
        text-align: left;
        overflow-y: auto;
        flex: 1;
        min-height: 0;
    }

    .message-modalv1-icon-circle {
        flex-shrink: 0;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: #eef2ff;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 26px;
    }

    .message-modalv1-text {
        flex: 1;
    }

    .message-modalv1-title {
        font-weight: 600;
        font-size: 18px;
        margin: 0 0 4px 0;
        color: var(--text);
    }

    .message-modalv1-message {
        color: var(--muted);
        font-size: 14px;
        margin: 0;
        line-height: 1.4;
    }

    .message-modalv1-footer {
        display: flex;
        gap: 10px;
        margin-top: 5px;
        flex-shrink: 0;
    }

    .message-modalv1-btn-cancel,
    .message-modalv1-btn-action {
        flex: 1;
        padding: 10px 16px;
        border: none;
        border-radius: 8px;
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        cursor: pointer;
    }

    .message-modalv1-btn-cancel {
        background: var(--cancel);
        color: var(--text);
    }

    .message-modalv1-btn-action {
        color: #fff;
    }
</style>

<div class="message-modalv1-modal" id="message-modalv1-confirm-modal">
    <div class="message-modalv1-content">
        <div class="message-modalv1-body">
            <div class="message-modalv1-icon-circle" id="message-modalv1-icon"></div>
            <div class="message-modalv1-text">
                <div class="message-modalv1-title" id="message-modalv1-title"></div>
                <div class="message-modalv1-message" id="message-modalv1-message"></div>
            </div>
        </div>
        <div class="message-modalv1-footer">
            <button class="message-modalv1-btn-cancel" id="message-modalv1-cancel-btn"></button>
            <button class="message-modalv1-btn-action" id="message-modalv1-action-btn"></button>
        </div>
    </div>
</div>

<script>
    let messageModalV1CurrentConfirmAction = null;

    function messageModalV1Dismiss() {
        document.getElementById('message-modalv1-confirm-modal').classList.remove('active');
    }

    function messageModalV1Show({
        icon,
        iconBg,
        actionBtnBg,
        showCancelBtn = true,
        title,
        message,
        cancelText,
        actionText,
        onConfirm,
        onCancel,
        dismissOnConfirm = true
    }) {
        const iconEl = document.getElementById('message-modalv1-icon');
        const actionBtn = document.getElementById('message-modalv1-action-btn');
        const cancelBtn = document.getElementById('message-modalv1-cancel-btn');
        const iconCircle = document.querySelector('.message-modalv1-icon-circle');
        const modalEl = document.getElementById('message-modalv1-confirm-modal');

        // Cancel button visibility
        cancelBtn.style.display = showCancelBtn ? 'block' : 'none';

        // Icon setup
        iconEl.innerHTML = icon;
        iconEl.style.background = iconBg;
        iconCircle.style.color = actionBtnBg;

        // Button colors
        actionBtn.style.background = actionBtnBg;

        // Text
        document.getElementById('message-modalv1-title').textContent = title;
        document.getElementById('message-modalv1-message').innerHTML = message;
        cancelBtn.textContent = cancelText;
        actionBtn.textContent = actionText;

        messageModalV1CurrentConfirmAction = onConfirm;
        window.messageModalV1CurrentCancelAction = onCancel;
        // Store dismiss behavior on confirm
        modalEl.dataset.dismissOnConfirm = dismissOnConfirm ? '1' : '0';
        // Store cancel handler and behavior
        modalEl.dataset.hasOnCancel = typeof onCancel === 'function' ? '1' : '0';
        modalEl.classList.add('active');
    }

    // Close modal on cancel
    document.getElementById('message-modalv1-cancel-btn').addEventListener('click', () => {
        const modalEl = document.getElementById('message-modalv1-confirm-modal');
        const hasCancel = modalEl.dataset.hasOnCancel === '1';
        const cancelHandler = window.messageModalV1CurrentCancelAction;
        if (hasCancel && typeof cancelHandler === 'function') {
            cancelHandler();
        } else {
            modalEl.classList.remove('active');
        }
    });

    // Confirm action
    document.getElementById('message-modalv1-action-btn').addEventListener('click', () => {
        if (typeof messageModalV1CurrentConfirmAction === 'function') {
            messageModalV1CurrentConfirmAction();
        }
        const modalEl = document.getElementById('message-modalv1-confirm-modal');
        const shouldDismiss = modalEl.dataset.dismissOnConfirm !== '0';
        if (shouldDismiss) {
            modalEl.classList.remove('active');
        }
    });

    // Expose cancel handler assignment
    window.messageModalV1CurrentCancelAction = null;
</script>

<!-- <button onclick="messageModalV1Show({
  icon: `<svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='1.5' stroke='currentColor' width='28' height='28'><path stroke-linecap='round' stroke-linejoin='round' d='M6 18L18 6M6 6l12 12' /></svg>`,
  iconBg: '#fee2e2',
  actionBtnBg: '#2E7D32',
  title: 'Delete Record?',
  message: 'Are you sure you want to delete this record permanently?',
  cancelText: 'Cancel',
  actionText: 'Delete',
  onConfirm: () => alert('Deleted ✅')
})">
    Show Modal
</button> -->
