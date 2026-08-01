/**
 * Quick-add helper for the Social & coding profiles section: paste any
 * profile URL into #social-quick-add, detect the platform from the
 * hostname, and fill the matching named field. The per-platform fields
 * stay the real storage/fallback - this is a convenience layer on top,
 * not a replacement for them.
 */
(function () {
    'use strict';

    var PATTERNS = [
        { test: /linkedin\.com/i, field: 'linkedin_url' },
        { test: /github\.com/i, field: 'github_url' },
        { test: /leetcode\.com/i, field: 'leetcode_url' },
        { test: /hackerrank\.com/i, field: 'hackerrank_url' },
        { test: /codechef\.com/i, field: 'codechef_url' },
        { test: /behance\.net/i, field: 'behance_url' },
        { test: /dribbble\.com/i, field: 'dribbble_url' },
        { test: /(youtube\.com|youtu\.be)/i, field: 'youtube_url' },
    ];

    var LABELS = {
        linkedin_url: 'LinkedIn', github_url: 'GitHub', leetcode_url: 'LeetCode',
        hackerrank_url: 'HackerRank', codechef_url: 'CodeChef', behance_url: 'Behance',
        dribbble_url: 'Dribbble', youtube_url: 'YouTube', website_url: 'Website'
    };

    function detectField(url) {
        for (var i = 0; i < PATTERNS.length; i++) {
            if (PATTERNS[i].test.test(url)) return PATTERNS[i].field;
        }
        return 'website_url';
    }

    document.addEventListener('DOMContentLoaded', function () {
        var quickAdd = document.getElementById('social-quick-add');
        var status = document.getElementById('social-quick-add-status');
        if (!quickAdd) return;

        function applyLink() {
            var value = quickAdd.value.trim();
            if (value === '') return;
            if (!/^https?:\/\//i.test(value)) value = 'https://' + value;

            var fieldName = detectField(value);
            var input = document.querySelector('[name="' + fieldName + '"]');
            if (!input) return;

            input.value = value;
            quickAdd.value = '';
            if (status) {
                status.textContent = 'Added as ' + (LABELS[fieldName] || fieldName) + '.';
                setTimeout(function () { status.textContent = ''; }, 3000);
            }
        }

        quickAdd.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyLink();
            }
        });
        quickAdd.addEventListener('blur', applyLink);
    });
})();
