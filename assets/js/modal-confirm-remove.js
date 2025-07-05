document.addEventListener('DOMContentLoaded', function() {
    document
        .querySelectorAll('[data-bs-toggle="tooltip"]')
        .forEach(el => new bootstrap.Tooltip(el));
});

function confirmRemove(triggerButton) {
    const url = triggerButton.getAttribute('data-href');
    const confirmLink = document.querySelector('[data-modal-button="confirm-link"]');
    confirmLink.setAttribute('href', url);
}
