(function () {
    'use strict';

    var root = document.getElementById('chat-thread-root');
    if (!root) return;

    var csrfToken = root.dataset.csrf;
    var sendUrl = root.dataset.sendUrl;
    var pollUrl = root.dataset.pollUrl;
    var readUrl = root.dataset.readUrl;
    var currentUserId = parseInt(root.dataset.currentUser, 10);

    var messagesEl = document.getElementById('chat-messages');
    var form = document.getElementById('chat-send-form');
    var bodyInput = document.getElementById('chat-message-body');
    var attachmentInput = document.getElementById('chat-attachment-input');
    var attachmentNameEl = document.getElementById('chat-attachment-name');
    var errorEl = document.getElementById('chat-send-error');

    var POLL_INTERVAL_MS = 6000;

    function lastMessageId() {
        var bubbles = messagesEl.querySelectorAll('[data-message-id]');
        if (bubbles.length === 0) return 0;
        return parseInt(bubbles[bubbles.length - 1].dataset.messageId, 10) || 0;
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatTime(iso) {
        var d = new Date(iso.replace(' ', 'T'));
        var hours = d.getHours();
        var minutes = d.getMinutes();
        var ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12;
        return hours + ':' + (minutes < 10 ? '0' : '') + minutes + ' ' + ampm;
    }

    function attachmentIcon(mime) {
        return (mime && mime.indexOf('pdf') !== -1) ? 'bi-file-earmark-pdf' : 'bi-file-earmark-word';
    }

    function buildBubble(message) {
        var isMine = parseInt(message.sender_id, 10) === currentUserId;
        var wrapper = document.createElement('div');
        wrapper.className = 'd-flex ' + (isMine ? 'justify-content-end' : 'justify-content-start');
        wrapper.setAttribute('data-message-id', message.id);

        var bubbleClasses = 'p-2 px-3 rounded-3 ' + (isMine ? 'bg-primary text-white' : 'bg-body-tertiary');
        var html = '<div class="' + bubbleClasses + '" style="max-width: 75%;">';

        if (message.body) {
            html += '<div class="small">' + escapeHtml(message.body).replace(/\n/g, '<br>') + '</div>';
        }

        if (message.attachment_path) {
            var url = '/' + message.attachment_path.replace(/^\/+/, '');
            if (message.attachment_type === 'image') {
                html += '<a href="' + url + '" target="_blank"><img src="' + url + '" class="rounded mt-1" style="max-width:240px;max-height:240px;object-fit:cover;"></a>';
            } else {
                var chipClasses = isMine ? 'bg-white bg-opacity-25 text-white' : 'bg-white border text-reset';
                html += '<a href="' + url + '" target="_blank" class="d-flex align-items-center gap-2 mt-1 p-2 rounded ' + chipClasses + ' text-decoration-none">'
                      + '<i class="bi ' + attachmentIcon(message.attachment_mime) + '"></i>'
                      + '<span class="small">' + escapeHtml(message.attachment_original_name || 'Download file') + '</span></a>';
            }
        }

        html += '<div class="small mt-1 ' + (isMine ? 'text-white-50' : 'text-muted') + '">' + formatTime(message.created_at) + '</div>';
        html += '</div>';

        wrapper.innerHTML = html;
        return wrapper;
    }

    function scrollToBottom() {
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function markRead() {
        var body = new URLSearchParams();
        body.append('_csrf_token', csrfToken);
        fetch(readUrl, { method: 'POST', body: body, headers: { 'X-Requested-With': 'XMLHttpRequest' } }).catch(function () {});
    }

    function poll() {
        fetch(pollUrl + '?after=' + lastMessageId(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data.messages || data.messages.length === 0) return;
                var hasIncoming = false;
                data.messages.forEach(function (message) {
                    messagesEl.appendChild(buildBubble(message));
                    if (parseInt(message.sender_id, 10) !== currentUserId) hasIncoming = true;
                });
                scrollToBottom();
                if (hasIncoming) markRead();
            })
            .catch(function () {});
    }

    if (attachmentInput) {
        attachmentInput.addEventListener('change', function () {
            attachmentNameEl.textContent = attachmentInput.files.length > 0 ? attachmentInput.files[0].name : '';
        });
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            errorEl.textContent = '';

            var body = bodyInput.value.trim();
            var hasFile = attachmentInput && attachmentInput.files.length > 0;

            if (body === '' && !hasFile) return;

            var formData = new FormData();
            formData.append('_csrf_token', csrfToken);
            formData.append('body', body);
            if (hasFile) formData.append('attachment', attachmentInput.files[0]);

            fetch(sendUrl, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (!data.ok) {
                        errorEl.textContent = data.message || 'Could not send message.';
                        return;
                    }
                    messagesEl.appendChild(buildBubble(data.message));
                    scrollToBottom();
                    bodyInput.value = '';
                    if (attachmentInput) attachmentInput.value = '';
                    attachmentNameEl.textContent = '';
                })
                .catch(function () {
                    errorEl.textContent = 'Could not send message. Check your connection and try again.';
                });
        });
    }

    scrollToBottom();
    markRead();
    setInterval(poll, POLL_INTERVAL_MS);
})();
