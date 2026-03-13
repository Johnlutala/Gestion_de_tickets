document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('ticket-filter-form');
    if (form) {
        form.querySelectorAll('select, input[type="date"]').forEach((field) => {
            field.addEventListener('change', () => {
                form.submit();
            });
        });
    }

    document.querySelectorAll('.js-submit-form-link').forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();

            const formId = link.dataset.formId;
            const confirmMessage = link.dataset.confirmMessage;
            const targetForm = formId ? document.getElementById(formId) : null;
            if (!targetForm) {
                return;
            }

            if (confirmMessage && !window.confirm(confirmMessage)) {
                return;
            }

            targetForm.submit();
        });
    });
});
