function confirmRemove(triggerButton) {
    const url = triggerButton.getAttribute('data-href');
    const confirmLink = document.querySelector('[data-modal-button="confirm-link"]');
    confirmLink.setAttribute('href', url);
}

window.confirmRemove = confirmRemove;
