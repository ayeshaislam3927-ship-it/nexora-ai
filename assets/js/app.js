/**
 * NEXORA - Main Application JS Helpers
 */

document.addEventListener('DOMContentLoaded', () => {
    // Theme toggle button
    const themeBtn = document.getElementById('btnThemeToggle');
    if (themeBtn) {
        themeBtn.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('nexora_theme', newTheme);
        });
    }
});

// Toast / Alert Notification
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : 'success'}`;
    toast.style.position = 'fixed';
    toast.style.bottom = '20px';
    toast.style.right = '20px';
    toast.style.zIndex = '9999';
    toast.style.boxShadow = '0 10px 25px rgba(0,0,0,0.5)';
    toast.innerText = message;

    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}

// Copy Code Block to Clipboard
function copyCodeBlock(button) {
    const codeBlock = button.parentElement.nextElementSibling.querySelector('code');
    if (codeBlock) {
        navigator.clipboard.writeText(codeBlock.innerText).then(() => {
            const orig = button.innerText;
            button.innerText = 'Copied!';
            setTimeout(() => button.innerText = orig, 2000);
        });
    }
}
