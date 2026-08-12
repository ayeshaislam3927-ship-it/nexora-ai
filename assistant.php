<?php
/**
 * NEXORA - Main Application Interface
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';

$currentUser = get_logged_in_user();
$isGuest = !$currentUser;

$pageTitle = 'NEXORA AI — Chat';
$extraCss = ['/assets/css/chat.css'];
$extraJs = ['/assets/js/chat.js'];

require_once __DIR__ . '/includes/header.php';
?>

<div class="chat-app-container" id="chatApp">
    <!-- Backdrop overlay for mobile sidebar -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- LEFT SIDEBAR -->
    <aside class="chat-sidebar" id="chatSidebar">
        <!-- Sidebar Header / Logo -->
        <div class="sidebar-header">
            <a href="/" class="sidebar-brand">
                <img src="/assets/logo/nexora-logo.png" alt="NEXORA Logo" class="sidebar-logo">
                <span class="sidebar-brand-name">NEXORA</span>
            </a>
            <button type="button" class="sidebar-close-btn" id="btnCloseSidebar" aria-label="Close sidebar">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- New Chat Button -->
        <div class="sidebar-action">
            <button type="button" class="btn btn-new-chat" id="btnNewChat">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>New Chat</span>
            </button>
        </div>

        <!-- Search Input -->
        <div class="sidebar-search">
            <div class="search-input-wrapper">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="chatSearchInput" placeholder="Search chats..." class="search-input">
            </div>
        </div>

        <!-- Recent Chats List -->
        <div class="recent-chats-container">
            <div class="sidebar-section-title">Recent Chats</div>
            <div class="chats-list" id="recentChatsList">
                <!-- Populated via JS -->
                <div class="chat-item-placeholder">No recent chats</div>
            </div>
        </div>

        <!-- Sidebar Footer -->
        <div class="sidebar-footer">
            <a href="/settings" class="sidebar-footer-item">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Settings</span>
            </a>
            <?php if (is_admin()): ?>
                <a href="/admin/dashboard" class="sidebar-footer-item text-primary">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Admin Panel</span>
                </a>
            <?php endif; ?>
            <?php if ($isGuest): ?>
                <a href="/login" class="sidebar-footer-item text-primary">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    <span>Sign In</span>
                </a>
            <?php else: ?>
                <a href="/logout" class="sidebar-footer-item text-danger">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span>Log Out</span>
                </a>
            <?php endif; ?>
        </div>
    </aside>

    <!-- MAIN CHAT VIEW -->
    <main class="chat-main">
        <!-- Top Bar Header -->
        <header class="chat-topbar">
            <div class="topbar-left">
                <button type="button" class="btn-toggle-sidebar" id="btnToggleSidebar" aria-label="Toggle Navigation">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>

                <!-- Model Selector Dropdown -->
                <div class="model-selector-wrapper">
                    <select id="modelSelect" class="model-select">
                        <option value="gemini-2.5-flash" selected>Gemini 2.5 Flash</option>
                        <option value="gemini-2.5-pro">Gemini 2.5 Pro</option>
                        <option value="gpt-4o">GPT-4o</option>
                        <option value="claude-3-5-sonnet">Claude 3.5 Sonnet</option>
                        <option value="grok-2">Grok 2</option>
                    </select>
                </div>
            </div>

            <div class="topbar-right">
                <!-- Theme Toggle Button -->
                <button type="button" class="btn-icon" id="btnThemeToggle" title="Toggle Light/Dark Theme">
                    <svg class="sun-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>

                <!-- User Profile / Auth State -->
                <?php if ($isGuest): ?>
                    <a href="/login" class="btn btn-sm btn-primary">Sign In</a>
                <?php else: ?>
                    <div class="user-avatar-badge" title="<?= htmlspecialchars($currentUser['email']) ?>">
                        <?= strtoupper(substr($currentUser['first_name'] ?? $currentUser['email'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </div>
        </header>

        <!-- Guest Warning Banner -->
        <?php if ($isGuest): ?>
            <div class="guest-banner">
                <span>⚡ You are chatting as a Guest. <a href="/signup" class="banner-link">Sign in</a> to save your conversation history permanently.</span>
            </div>
        <?php endif; ?>

        <!-- Conversation Feed Area -->
        <div class="conversation-container" id="conversationFeed">
            <!-- Initial Welcome Screen -->
            <div class="chat-welcome-screen" id="welcomeScreen">
                <img src="/assets/logo/nexora-logo.png" alt="NEXORA Logo" class="welcome-logo">
                <h1 class="welcome-title">What can NEXORA help with today?</h1>
                <div class="welcome-suggestions">
                    <button class="suggestion-chip" onclick="useSuggestion('Write a clean PHP PDO database class with error handling')">
                        <span class="chip-title">Writing Code</span>
                        <span class="chip-desc">Clean PHP PDO database class</span>
                    </button>
                    <button class="suggestion-chip" onclick="useSuggestion('Explain quantum computing in simple terms')">
                        <span class="chip-title">Explanations</span>
                        <span class="chip-desc">Quantum computing concepts</span>
                    </button>
                    <button class="suggestion-chip" onclick="useSuggestion('Draft a professional email proposing a tech architecture upgrade')">
                        <span class="chip-title">Content Writing</span>
                        <span class="chip-desc">Professional proposal email</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Chat Composer Bar -->
        <div class="composer-container">
            <!-- Attachment Preview Box -->
            <div id="attachmentPreview" class="attachment-preview hidden">
                <span id="attachmentName"></span>
                <button type="button" id="btnRemoveAttachment" class="btn-remove">&times;</button>
            </div>

            <form id="chatForm" class="composer-box">
                <input type="file" id="fileInput" class="hidden" accept="image/*,.pdf,.txt,.csv,.json,.md,.doc,.docx">

                <button type="button" class="composer-btn" id="btnAttach" title="Attach image or file">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                </button>

                <textarea id="promptInput" class="composer-textarea" placeholder="Message NEXORA..." rows="1" required></textarea>

                <button type="button" class="composer-btn" id="btnVoice" title="Speech to text dictation">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                </button>

                <button type="submit" id="btnSend" class="btn-send" title="Send message">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>
            <div class="composer-disclaimer">NEXORA can make mistakes. Verify important information.</div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
