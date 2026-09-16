(function () {
    'use strict';

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const messagesEl = document.getElementById('messages');
    const form = document.getElementById('composer-form');
    const textarea = document.getElementById('composer-input');
    const sendBtn = document.getElementById('send-btn');
    const newChatBtn = document.getElementById('new-chat-btn');
    const errorEl = document.getElementById('composer-error');

    let conversationId = window.__AI_CHAT__.conversationId;
    const hasActiveProvider = window.__AI_CHAT__.hasActiveProvider;

    function showError(message) {
        errorEl.textContent = message;
        errorEl.style.display = 'block';
    }

    function clearError() {
        errorEl.style.display = 'none';
    }

    function scrollToBottom() {
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function removeEmptyState() {
        const empty = messagesEl.querySelector('.empty-state');
        if (empty) empty.remove();
    }

    function appendMessage(role, content) {
        removeEmptyState();
        const el = document.createElement('div');
        el.className = 'msg ' + role;
        el.textContent = content;
        messagesEl.appendChild(el);
        scrollToBottom();
        return el;
    }

    async function ensureConversation() {
        if (conversationId) return conversationId;

        const res = await fetch('/ai/conversations', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
            },
        });

        if (!res.ok) {
            const body = await res.json().catch(() => ({}));
            throw new Error(body.message || 'Gagal membuat percakapan baru.');
        }

        const data = await res.json();
        conversationId = data.id;
        window.history.replaceState({}, '', '/ai/conversations/' + conversationId);

        const item = document.createElement('a');
        item.href = '/ai/conversations/' + conversationId;
        item.className = 'conversation-item active';
        item.textContent = 'Percakapan baru';
        document.querySelectorAll('.conversation-item').forEach((el) => el.classList.remove('active'));
        document.getElementById('conversation-list').prepend(item);

        return conversationId;
    }

    async function sendMessage(content) {
        clearError();
        appendMessage('user', content);

        const assistantEl = appendMessage('assistant', '');
        assistantEl.classList.add('pending');

        try {
            const id = await ensureConversation();

            const res = await fetch('/ai/conversations/' + id + '/messages', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'text/event-stream',
                },
                body: JSON.stringify({ content }),
            });

            if (!res.ok || !res.body) {
                const body = await res.json().catch(() => ({}));
                throw new Error(body.message || 'AI Provider tidak merespons.');
            }

            const reader = res.body.getReader();
            const decoder = new TextDecoder();
            let buffer = '';
            let text = '';

            while (true) {
                const { value, done } = await reader.read();
                if (done) break;

                buffer += decoder.decode(value, { stream: true });
                const parts = buffer.split('\n\n');
                buffer = parts.pop();

                for (const part of parts) {
                    const line = part.trim();
                    if (!line.startsWith('data:')) continue;
                    const payload = line.slice(5).trim();
                    if (!payload) continue;

                    let json;
                    try {
                        json = JSON.parse(payload);
                    } catch (e) {
                        continue;
                    }

                    if (json.error) {
                        throw new Error(json.error);
                    }

                    const delta = json.choices && json.choices[0] && json.choices[0].delta
                        ? json.choices[0].delta.content
                        : '';

                    if (delta) {
                        text += delta;
                        assistantEl.textContent = text;
                        scrollToBottom();
                    }
                }
            }
        } catch (err) {
            showError(err.message || 'Terjadi kesalahan saat menghubungi AI Provider.');
        } finally {
            assistantEl.classList.remove('pending');
            if (!assistantEl.textContent) {
                assistantEl.remove();
            }
        }
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        if (!hasActiveProvider) {
            showError('Belum ada AI Provider yang aktif. Atur salah satu di Administration → AI Providers.');
            return;
        }

        const content = textarea.value.trim();
        if (!content) return;

        textarea.value = '';
        textarea.style.height = 'auto';
        sendBtn.disabled = true;

        sendMessage(content).finally(() => {
            sendBtn.disabled = false;
            textarea.focus();
        });
    });

    textarea.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form.requestSubmit();
        }
    });

    textarea.addEventListener('input', function () {
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 128) + 'px';
    });

    if (newChatBtn) {
        newChatBtn.addEventListener('click', function () {
            window.location.href = '/ai';
        });
    }

    scrollToBottom();
})();
