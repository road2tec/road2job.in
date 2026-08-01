(function () {
    var stored = localStorage.getItem('r2j-theme');
    var theme = stored || 'light';
    document.documentElement.setAttribute('data-bs-theme', theme);

    document.addEventListener('DOMContentLoaded', function () {
        var toggle = document.getElementById('themeToggle');
        if (!toggle) return;

        toggle.checked = theme === 'dark';

        toggle.addEventListener('change', function () {
            var next = toggle.checked ? 'dark' : 'light';
            document.documentElement.setAttribute('data-bs-theme', next);
            localStorage.setItem('r2j-theme', next);
        });
    });
})();
