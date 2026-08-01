document.addEventListener('DOMContentLoaded', function () {
    // Password visibility toggle - any button with data-toggle-password="#fieldId"
    document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.querySelector(btn.getAttribute('data-toggle-password'));
            if (!input) return;

            var showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';

            var icon = btn.querySelector('i');
            if (icon) {
                icon.classList.toggle('bi-eye', showing);
                icon.classList.toggle('bi-eye-slash', !showing);
            }
            btn.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
        });
    });

    // Submit-once guard - disables the button and shows a spinner once a
    // form actually submits (native required/type/minlength validation
    // still runs first, this only fires on a genuinely valid submit),
    // preventing accidental duplicate submissions.
    document.querySelectorAll('form[data-guard-submit]').forEach(function (form) {
        form.addEventListener('submit', function () {
            var btn = form.querySelector('button[type="submit"]');
            if (!btn || btn.disabled) return;

            var label = btn.getAttribute('data-loading-text') || 'Please wait...';
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' + label;
        });
    });

    // Live password-match indicator - purely a UX hint, the server's
    // "confirmed" validation rule is still the real check either way.
    document.querySelectorAll('[data-match-target]').forEach(function (confirmField) {
        var source = document.querySelector(confirmField.getAttribute('data-match-target'));
        var feedback = document.querySelector(confirmField.getAttribute('data-match-feedback'));
        if (!source || !feedback) return;

        function check() {
            if (confirmField.value === '') {
                feedback.textContent = '';
                feedback.className = 'form-text';
                return;
            }
            var matches = confirmField.value === source.value;
            feedback.textContent = matches ? 'Passwords match' : 'Passwords do not match';
            feedback.className = matches ? 'form-text text-success' : 'form-text text-danger';
        }

        confirmField.addEventListener('input', check);
        source.addEventListener('input', check);
    });
});
