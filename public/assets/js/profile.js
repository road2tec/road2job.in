function resetMultiSelectRoots(modalEl) {
    modalEl.querySelectorAll('[data-multi-select]').forEach(function (root) {
        if (root.msReset) root.msReset();
    });
}

// Chip-mode fields (technologies_used/skills_used) store a comma-joined
// string in the DB, edited as a distinct field named via data-ms-name -
// the visible <input> has no name of its own, so openResourceModal's
// generic name-matched population never reaches these. Handled here.
function populateMultiSelectRoots(modalEl, data) {
    modalEl.querySelectorAll('[data-multi-select="chips"]').forEach(function (root) {
        var name = root.getAttribute('data-ms-name');
        if (!name || !data || !(name in data)) return;

        var raw = data[name];
        if (raw === null || raw === undefined || raw === '') return;

        var values = String(raw).split(',').map(function (v) { return v.trim(); }).filter(Boolean);
        if (root.msPopulate) root.msPopulate(values);
    });
}

function resetResourceModal(modalId, action) {
    var modalEl = document.getElementById(modalId);
    var form = modalEl.querySelector('form');
    form.reset();
    if (action) form.action = action;
    var title = modalEl.querySelector('.modal-title');
    if (title) title.dataset.mode = 'add';
    resetMultiSelectRoots(modalEl);
    clearDirty(form);
}

function openResourceModal(modalId, action, data) {
    var modalEl = document.getElementById(modalId);
    var form = modalEl.querySelector('form');
    form.reset();
    form.action = action;
    resetMultiSelectRoots(modalEl);

    Object.keys(data || {}).forEach(function (key) {
        var field = form.querySelector('[name="' + key + '"]');
        if (!field) return;

        if (field.type === 'checkbox') {
            field.checked = !!(data[key] === 1 || data[key] === '1' || data[key] === true);
        } else {
            field.value = data[key] === null || data[key] === undefined ? '' : data[key];
        }
    });

    populateMultiSelectRoots(modalEl, data);
    clearDirty(form);
}

// --- Unsaved-changes protection --------------------------------------
// Forms opted in via data-track-unsaved get a dirty flag once any field
// (including chip add/remove) changes, and a confirm() before the modal
// closes with unsaved changes. Deliberately not applied to the bulk
// Skills-add modal or single-skill edit modal - both are quick, low-risk
// single-purpose forms where a confirm dialog would be more friction
// than protection.
(function () {
    var dirtyForms = new WeakSet();

    function markDirty(e) {
        var form = e.target.closest('form[data-track-unsaved]');
        if (form) dirtyForms.add(form);
    }

    document.addEventListener('input', markDirty);
    document.addEventListener('change', markDirty);
    document.addEventListener('ms:change', markDirty);

    window.clearDirty = function (form) {
        dirtyForms.delete(form);
    };

    document.addEventListener('submit', function (e) {
        dirtyForms.delete(e.target);
    });

    document.addEventListener('hide.bs.modal', function (e) {
        var form = e.target.querySelector('form[data-track-unsaved]');
        if (!form || !dirtyForms.has(form)) return;

        if (!window.confirm('You have unsaved changes in this form. Close without saving?')) {
            e.preventDefault();
        } else {
            dirtyForms.delete(form);
        }
    });
})();
