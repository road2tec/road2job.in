/**
 * Reusable autocomplete / multi-select chip component - vanilla JS, no
 * build step, no dependency (same convention as auth-forms.js). One
 * component, two modes, driven entirely by data attributes so it can be
 * dropped into any form without writing new JS per field.
 *
 * Chip mode markup:
 *   <div class="ms-root" data-multi-select="chips" data-ms-name="skills"
 *        data-ms-catalog="#skillsCatalog" data-ms-placeholder="Type a skill..."
 *        data-ms-secondary-name="proficiencies" data-ms-secondary-default="#defaultProficiency">
 *     <div data-ms-chips class="ms-chips"></div>
 *     <input type="text" data-ms-input class="form-control">
 *     <div data-ms-dropdown class="ms-dropdown"></div>
 *   </div>
 *   <script type="application/json" id="skillsCatalog">["Java","Python",...]</script>
 *
 * Renders selected values as hidden inputs (name="skills[]") inside the
 * same <form> the root sits in - the form still submits as one normal
 * POST, nothing here talks to the network.
 *
 * Single mode markup: same root, data-multi-select="single", a single
 * visible text <input data-ms-input name="degree"> IS the real field -
 * selecting a suggestion or typing custom text both just set its value.
 */
(function () {
    'use strict';

    function readCatalog(selector) {
        if (!selector) return [];
        var el = document.querySelector(selector);
        if (!el) return [];
        try {
            var data = JSON.parse(el.textContent);
            return Array.isArray(data) ? data : [];
        } catch (e) {
            return [];
        }
    }

    function readSuggestionMap(selector) {
        if (!selector) return {};
        var el = document.querySelector(selector);
        if (!el) return {};
        try {
            var data = JSON.parse(el.textContent);
            return (data && typeof data === 'object') ? data : {};
        } catch (e) {
            return {};
        }
    }

    function normalize(value) {
        return String(value || '').trim().toLowerCase();
    }

    function initRoot(root) {
        var mode = root.getAttribute('data-multi-select');
        var input = root.querySelector('[data-ms-input]');
        var dropdown = root.querySelector('[data-ms-dropdown]');
        if (!input || !dropdown) return;

        var catalog = readCatalog(root.getAttribute('data-ms-catalog'));
        var suggestionMap = readSuggestionMap(root.getAttribute('data-ms-suggestions'));
        var placeholder = root.getAttribute('data-ms-placeholder');
        if (placeholder) input.placeholder = placeholder;

        var activeIndex = -1;
        var currentMatches = [];

        function closeDropdown() {
            dropdown.innerHTML = '';
            dropdown.classList.remove('ms-dropdown-open');
            activeIndex = -1;
            currentMatches = [];
        }

        function renderDropdown(matches, query) {
            currentMatches = matches;
            activeIndex = matches.length > 0 ? 0 : -1;

            if (matches.length === 0) {
                if (query.trim() === '') {
                    dropdown.innerHTML = '';
                    dropdown.classList.remove('ms-dropdown-open');
                    return;
                }
                dropdown.innerHTML = '<div class="ms-dropdown-empty">No matches - press Enter to add "' + escapeHtml(query.trim()) + '"</div>';
                dropdown.classList.add('ms-dropdown-open');
                return;
            }

            var html = matches.map(function (option, i) {
                return '<div class="ms-dropdown-item' + (i === 0 ? ' ms-dropdown-item-active' : '') + '" data-ms-option="' + escapeHtml(option) + '" data-index="' + i + '">' + escapeHtml(option) + '</div>';
            }).join('');
            dropdown.innerHTML = html;
            dropdown.classList.add('ms-dropdown-open');
        }

        function escapeHtml(str) {
            var div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        function selectedChipValues() {
            if (mode !== 'chips') return [];
            var values = [];
            root.querySelectorAll('[data-ms-chip-value]').forEach(function (el) {
                values.push(el.getAttribute('data-ms-chip-value'));
            });
            return values;
        }

        function matchOptions(query) {
            var q = normalize(query);
            var taken = mode === 'chips' ? selectedChipValues().map(normalize) : [];
            var pool = catalog.filter(function (option) {
                return taken.indexOf(normalize(option)) === -1;
            });

            if (q === '') return pool.slice(0, 8);

            return pool.filter(function (option) {
                return normalize(option).indexOf(q) !== -1;
            }).slice(0, 8);
        }

        // Not debounced: catalogs here are small (a couple hundred entries
        // at most) and filtering is effectively instant, so debouncing just
        // opened a real race - pressing Enter within the debounce window
        // (trivially reproducible by typing fast, and by any programmatic
        // fill()+press('Enter')) acted on a stale dropdown from the
        // previous keystroke instead of the just-typed value.
        input.addEventListener('input', function () {
            renderDropdown(matchOptions(input.value), input.value);
        });
        input.addEventListener('focus', function () {
            renderDropdown(matchOptions(input.value), input.value);
        });

        // === Chip mode ===
        var chipsContainer = mode === 'chips' ? root.querySelector('[data-ms-chips]') : null;
        var fieldName = root.getAttribute('data-ms-name');
        var secondaryName = root.getAttribute('data-ms-secondary-name');
        var secondaryDefaultSelector = root.getAttribute('data-ms-secondary-default');
        var secondaryOptions = (root.getAttribute('data-ms-secondary-options') || '').split(',').map(function (s) { return s.trim(); }).filter(Boolean);
        var suggestionsBox = root.querySelector('[data-ms-related]');
        var maxChips = parseInt(root.getAttribute('data-ms-max') || '0', 10);

        function currentDefaultSecondary() {
            if (!secondaryDefaultSelector) return secondaryOptions[0] || '';
            var el = document.querySelector(secondaryDefaultSelector);
            return el ? el.value : (secondaryOptions[0] || '');
        }

        function showRelatedSuggestions() {
            if (!suggestionsBox) return;
            var taken = selectedChipValues().map(normalize);
            var related = [];
            selectedChipValues().forEach(function (val) {
                var hits = suggestionMap[normalize(val)];
                if (Array.isArray(hits)) {
                    hits.forEach(function (h) {
                        if (taken.indexOf(normalize(h)) === -1 && related.indexOf(h) === -1) related.push(h);
                    });
                }
            });
            related = related.slice(0, 6);

            if (related.length === 0) {
                suggestionsBox.innerHTML = '';
                return;
            }
            suggestionsBox.innerHTML = '<span class="ms-related-label">Related: </span>' + related.map(function (r) {
                return '<button type="button" class="ms-related-chip" data-ms-add="' + escapeHtml(r) + '">+ ' + escapeHtml(r) + '</button>';
            }).join('');
        }

        function addChip(value) {
            value = String(value || '').trim();
            if (value === '') return;
            if (mode !== 'chips') return;
            if (maxChips > 0 && selectedChipValues().length >= maxChips) return;
            if (selectedChipValues().map(normalize).indexOf(normalize(value)) !== -1) {
                input.value = '';
                closeDropdown();
                return;
            }

            var chip = document.createElement('span');
            chip.className = 'ms-chip';

            var label = document.createElement('span');
            label.className = 'ms-chip-label';
            label.textContent = value;
            chip.appendChild(label);

            var hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = fieldName + '[]';
            hidden.value = value;
            hidden.setAttribute('data-ms-chip-value', value);
            chip.appendChild(hidden);

            if (secondaryName) {
                var select = document.createElement('select');
                select.className = 'ms-chip-secondary form-select form-select-sm';
                select.name = secondaryName + '[]';
                secondaryOptions.forEach(function (opt) {
                    var o = document.createElement('option');
                    o.value = opt;
                    o.textContent = opt.charAt(0).toUpperCase() + opt.slice(1);
                    select.appendChild(o);
                });
                select.value = currentDefaultSecondary();
                chip.appendChild(select);
            }

            var removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'ms-chip-remove';
            removeBtn.setAttribute('aria-label', 'Remove ' + value);
            removeBtn.textContent = '×';
            removeBtn.addEventListener('click', function () {
                chip.remove();
                showRelatedSuggestions();
                root.dispatchEvent(new Event('ms:change', { bubbles: true }));
            });
            chip.appendChild(removeBtn);

            chipsContainer.appendChild(chip);
            input.value = '';
            closeDropdown();
            showRelatedSuggestions();
            root.dispatchEvent(new Event('ms:change', { bubbles: true }));
        }

        if (mode === 'chips') {
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (activeIndex >= 0 && currentMatches[activeIndex]) {
                        addChip(currentMatches[activeIndex]);
                    } else if (input.value.trim() !== '') {
                        addChip(input.value);
                    }
                } else if (e.key === ',') {
                    e.preventDefault();
                    if (input.value.trim() !== '') addChip(input.value);
                } else if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (currentMatches.length === 0) return;
                    activeIndex = (activeIndex + 1) % currentMatches.length;
                    renderDropdown(currentMatches, input.value);
                    activeIndex = Math.min(activeIndex, currentMatches.length - 1);
                    highlightActive();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (currentMatches.length === 0) return;
                    activeIndex = (activeIndex - 1 + currentMatches.length) % currentMatches.length;
                    highlightActive();
                } else if (e.key === 'Escape') {
                    closeDropdown();
                } else if (e.key === 'Backspace' && input.value === '') {
                    var chips = chipsContainer.querySelectorAll('.ms-chip');
                    if (chips.length > 0) {
                        chips[chips.length - 1].remove();
                        showRelatedSuggestions();
                    }
                }
            });

            input.addEventListener('paste', function (e) {
                var text = (e.clipboardData || window.clipboardData).getData('text');
                if (text.indexOf(',') === -1) return;
                e.preventDefault();
                text.split(',').forEach(function (part) { addChip(part); });
            });

            if (suggestionsBox) {
                suggestionsBox.addEventListener('click', function (e) {
                    var btn = e.target.closest('[data-ms-add]');
                    if (btn) addChip(btn.getAttribute('data-ms-add'));
                });
            }
        } else {
            // Single mode: Enter/click just fills the real input.
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    if (activeIndex >= 0 && currentMatches[activeIndex]) {
                        e.preventDefault();
                        input.value = currentMatches[activeIndex];
                        closeDropdown();
                    } else {
                        closeDropdown();
                    }
                } else if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (currentMatches.length === 0) return;
                    activeIndex = (activeIndex + 1) % currentMatches.length;
                    highlightActive();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (currentMatches.length === 0) return;
                    activeIndex = (activeIndex - 1 + currentMatches.length) % currentMatches.length;
                    highlightActive();
                } else if (e.key === 'Escape') {
                    closeDropdown();
                }
            });
        }

        function highlightActive() {
            dropdown.querySelectorAll('.ms-dropdown-item').forEach(function (el, i) {
                el.classList.toggle('ms-dropdown-item-active', i === activeIndex);
            });
        }

        dropdown.addEventListener('mousedown', function (e) {
            // mousedown (not click) so this fires before the input's blur closes the dropdown
            var item = e.target.closest('[data-ms-option]');
            if (!item) return;
            e.preventDefault();
            var value = item.getAttribute('data-ms-option');
            if (mode === 'chips') {
                addChip(value);
            } else {
                input.value = value;
                closeDropdown();
            }
        });

        document.addEventListener('click', function (e) {
            if (!root.contains(e.target)) closeDropdown();
        });

        // Public reset hook used by profile.js when a modal is reopened for "Add".
        root.msReset = function () {
            if (chipsContainer) chipsContainer.innerHTML = '';
            if (suggestionsBox) suggestionsBox.innerHTML = '';
            input.value = '';
            closeDropdown();
        };

        // Public populate hook used when a modal is opened for "Edit" with
        // existing comma-separated tag data (e.g. skills_used/technologies_used).
        root.msPopulate = function (values) {
            if (mode !== 'chips' || !Array.isArray(values)) return;
            chipsContainer.innerHTML = '';
            values.forEach(function (v) { addChip(v); });
        };

        if (mode === 'chips') showRelatedSuggestions();
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-multi-select]').forEach(initRoot);
    });
})();
