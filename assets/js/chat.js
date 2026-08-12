/**
 * NEXORA - Complete Chat Engine & Frontend Logic
 */

let activeChatId = null;
let currentAttachmentUrl = '';

function getAuthHeaders(customHeaders = {}) {
    const headers = { ...customHeaders };
    const token = localStorage.getItem('nexora_auth_token');
    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }
    return headers;
}

document.addEventListener('DOMContentLoaded', () => {
    // Elements
    const chatSidebar = document.getElementById('chatSidebar');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');
    const btnToggleSidebar = document.getElementById('btnToggleSidebar');
    const btnCloseSidebar = document.getElementById('btnCloseSidebar');
    const btnNewChat = document.getElementById('btnNewChat');
    const chatSearchInput = document.getElementById('chatSearchInput');

    const conversationFeed = document.getElementById('conversationFeed');
    const welcomeScreen = document.getElementById('welcomeScreen');

    const chatForm = document.getElementById('chatForm');
    const promptInput = document.getElementById('promptInput');
    const btnSend = document.getElementById('btnSend');
    const modelSelect = document.getElementById('modelSelect');

    const fileInput = document.getElementById('fileInput');
    const btnAttach = document.getElementById('btnAttach');
    const attachmentPreview = document.getElementById('attachmentPreview');
    const attachmentName = document.getElementById('attachmentName');
    const btnRemoveAttachment = document.getElementById('btnRemoveAttachment');
    const btnVoice = document.getElementById('btnVoice');

    // Load Chat History
    loadRecentChats();

    // Sidebar Mobile Drawer Controls
    if (btnToggleSidebar) {
        btnToggleSidebar.addEventListener('click', () => {
            chatSidebar.classList.add('open');
            sidebarBackdrop.classList.add('active');
        });
    }

    if (btnCloseSidebar) {
        btnCloseSidebar.addEventListener('click', closeSidebar);
    }

    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', closeSidebar);
    }

    function closeSidebar() {
        chatSidebar.classList.remove('open');
        sidebarBackdrop.classList.remove('active');
    }

    // New Chat Button
    if (btnNewChat) {
        btnNewChat.addEventListener('click', () => {
            activeChatId = null;
            conversationFeed.innerHTML = '';
            if (welcomeScreen) conversationFeed.appendChild(welcomeScreen);
            promptInput.focus();
            closeSidebar();
        });
    }

    // Search Chats Listener
    if (chatSearchInput) {
        chatSearchInput.addEventListener('input', (e) => {
            loadRecentChats(e.target.value.trim());
        });
    }

    // Auto-grow textarea & Keyboard shortcuts
    if (promptInput) {
        promptInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        promptInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                chatForm.dispatchEvent(new Event('submit'));
            }
        });
    }

    // File Attachment Handler
    if (btnAttach && fileInput) {
        btnAttach.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', async () => {
            if (fileInput.files.length === 0) return;
            const file = fileInput.files[0];

            const formData = new FormData();
            formData.append('file', file);

            btnAttach.disabled = true;

            try {
                const res = await fetch('/api/upload.php', {
                    method: 'POST',
                    headers: getAuthHeaders(),
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    currentAttachmentUrl = data.file_url;
                    attachmentName.innerText = data.original_name;
                    attachmentPreview.classList.remove('hidden');
                } else {
                    alert(data.error || 'Upload failed.');
                }
            } catch (err) {
                alert('File upload error.');
            } finally {
                btnAttach.disabled = false;
            }
        });
    }

    if (btnRemoveAttachment) {
        btnRemoveAttachment.addEventListener('click', () => {
            currentAttachmentUrl = '';
            attachmentPreview.classList.add('hidden');
            fileInput.value = '';
        });
    }

    // Speech-To-Text Dictation
    if (btnVoice) {
        let recognition = null;
        if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            recognition = new SpeechRecognition();
            recognition.continuous = false;
            recognition.interimResults = false;

            recognition.onresult = (e) => {
                const transcript = e.results[0][0].transcript;
                promptInput.value += (promptInput.value ? ' ' : '') + transcript;
                promptInput.dispatchEvent(new Event('input'));
                btnVoice.classList.remove('active');
            };

            recognition.onerror = () => btnVoice.classList.remove('active');
            recognition.onend = () => btnVoice.classList.remove('active');

            btnVoice.addEventListener('click', () => {
                if (btnVoice.classList.contains('active')) {
                    recognition.stop();
                } else {
                    recognition.start();
                    btnVoice.classList.add('active');
                }
            });
        } else {
            btnVoice.style.display = 'none';
        }
    }

    // Submit Chat Form
    if (chatForm) {
        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const prompt = promptInput.value.trim();
            if (!prompt) return;

            // Clear welcome screen if present
            if (welcomeScreen && conversationFeed.contains(welcomeScreen)) {
                conversationFeed.removeChild(welcomeScreen);
            }

            // Append User Message Bubble
            appendMessage('user', prompt);

            // Reset Composer State
            promptInput.value = '';
            promptInput.style.height = 'auto';
            const attachmentToSend = currentAttachmentUrl;
            currentAttachmentUrl = '';
            attachmentPreview.classList.add('hidden');

            // Append Loading Indicator
            const loadingBubble = appendLoadingBubble();
            btnSend.disabled = true;

            const selectedModel = modelSelect ? modelSelect.value : 'gemini-2.5-flash';

            try {
                const res = await fetch('/api/ask.php', {
                    method: 'POST',
                    headers: getAuthHeaders({ 'Content-Type': 'application/json' }),
                    body: JSON.stringify({
                        prompt: prompt,
                        chat_id: activeChatId,
                        model: selectedModel,
                        attachment_url: attachmentToSend
                    })
                });

                const data = await res.json();
                loadingBubble.remove();

                if (data.success) {
                    activeChatId = data.chat_id;
                    appendMessage('assistant', data.formatted_response, true);
                    loadRecentChats(); // Refresh sidebar list
                } else {
                    appendMessage('assistant', `<p class="text-danger">Error: ${data.error || 'Server processing error'}</p>`, true);
                }
            } catch (err) {
                loadingBubble.remove();
                appendMessage('assistant', `<p class="text-danger">Network error connecting to NEXORA AI Gateway.</p>`, true);
            } finally {
                btnSend.disabled = false;
            }
        });
    }
});

// Load Sidebar Recent Chats
async function loadRecentChats(searchQuery = '') {
    const listEl = document.getElementById('recentChatsList');
    if (!listEl) return;

    try {
        const url = `/api/chat-history.php` + (searchQuery ? `?search=${encodeURIComponent(searchQuery)}` : '');
        const res = await fetch(url, { headers: getAuthHeaders() });
        const data = await res.json();

        if (data.success && data.chats && data.chats.length > 0) {
            listEl.innerHTML = '';
            data.chats.forEach(chat => {
                const item = document.createElement('div');
                item.className = `chat-item ${chat.id == activeChatId ? 'active' : ''}`;
                item.innerHTML = `
                    <span class="chat-title">${escapeHtml(chat.title)}</span>
                    <button class="btn-delete-chat" onclick="deleteChatSession(event, ${chat.id})">&times;</button>
                `;
                item.addEventListener('click', () => loadChatSession(chat.id));
                listEl.appendChild(item);
            });
        } else {
            listEl.innerHTML = '<div class="chat-item-placeholder">No recent conversations</div>';
        }
    } catch (e) {
        console.error('Error loading chats:', e);
    }
}

// Load Specific Chat Messages
async function loadChatSession(chatId) {
    activeChatId = chatId;
    const conversationFeed = document.getElementById('conversationFeed');
    conversationFeed.innerHTML = '<div class="text-center p-4">Loading conversation...</div>';

    try {
        const res = await fetch(`/api/chat-history.php?chat_id=${chatId}`, { headers: getAuthHeaders() });
        const data = await res.json();

        conversationFeed.innerHTML = '';

        if (data.success && data.messages) {
            data.messages.forEach(m => {
                appendMessage(m.role, m.role === 'assistant' ? m.formatted_content : escapeHtml(m.content), m.role === 'assistant');
            });
            loadRecentChats();
        }
    } catch (e) {
        conversationFeed.innerHTML = '<div class="text-danger p-4">Failed to load chat history.</div>';
    }
}

// Delete Chat Session
async function deleteChatSession(event, chatId) {
    event.stopPropagation();
    if (!confirm('Are you sure you want to delete this chat session?')) return;

    try {
        const res = await fetch('/api/chat-delete.php', {
            method: 'POST',
            headers: getAuthHeaders({ 'Content-Type': 'application/json' }),
            body: JSON.stringify({ chat_id: chatId, action: 'delete' })
        });
        const data = await res.json();

        if (data.success) {
            if (activeChatId == chatId) {
                activeChatId = null;
                document.getElementById('conversationFeed').innerHTML = '';
            }
            loadRecentChats();
        }
    } catch (e) {
        alert('Error deleting chat session.');
    }
}

// Global Suggestion Chip Trigger
function useSuggestion(text) {
    const promptInput = document.getElementById('promptInput');
    const chatForm = document.getElementById('chatForm');
    if (promptInput) {
        promptInput.value = text;
        promptInput.dispatchEvent(new Event('input'));
        chatForm.dispatchEvent(new Event('submit'));
    }
}

function appendMessage(role, content, isHtml = false) {
    const feed = document.getElementById('conversationFeed');
    if (!feed) return;

    const bubble = document.createElement('div');
    bubble.className = `chat-bubble ${role}`;

    const avatarHtml = role === 'user' ? 'U' : 'N';
    const bodyHtml = isHtml ? content : escapeHtml(content);

    bubble.innerHTML = `
        <div class="bubble-avatar">${avatarHtml}</div>
        <div class="bubble-content">${bodyHtml}</div>
    `;

    feed.appendChild(bubble);
    feed.scrollTop = feed.scrollHeight;
}

function appendLoadingBubble() {
    const feed = document.getElementById('conversationFeed');
    const bubble = document.createElement('div');
    bubble.className = 'chat-bubble assistant';
    bubble.innerHTML = `
        <div class="bubble-avatar">N</div>
        <div class="bubble-content"><span class="pulse-text">NEXORA is thinking...</span></div>
    `;
    feed.appendChild(bubble);
    feed.scrollTop = feed.scrollHeight;
    return bubble;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.innerText = text;
    return div.innerHTML;
}
