// Initialize Bootstrap popovers
const popoverTriggerList = Array.from(document.querySelectorAll('[data-bs-toggle="popover"]'));
popoverTriggerList.forEach((popoverTriggerEl) => new bootstrap.Popover(popoverTriggerEl, { html: true }));
