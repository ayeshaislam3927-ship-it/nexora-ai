<?php
/**
 * NEXORA - Main Landing / Public Entry Page
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'NEXORA — Next-Generation AI Platform';
$extraCss = ['/assets/css/auth.css'];

require_once __DIR__ . '/includes/header.php';
?>

<div class="landing-container">
    <nav class="landing-nav">
        <div class="brand">
            <img src="/assets/logo/nexora-logo.png" alt="NEXORA Logo" class="brand-logo">
            <span class="brand-text">NEXORA</span>
        </div>
        <div class="nav-actions">
            <?php if (is_logged_in()): ?>
                <a href="/assistant" class="btn btn-primary">Open Chat</a>
                <?php if (is_admin()): ?>
                    <a href="/admin/dashboard" class="btn btn-secondary">Admin</a>
                <?php endif; ?>
                <a href="/logout" class="btn btn-outline">Logout</a>
            <?php else: ?>
                <a href="/login" class="btn btn-outline">Sign In</a>
                <a href="/signup" class="btn btn-primary">Sign Up</a>
            <?php endif; ?>
        </div>
    </nav>

    <main class="hero-section">
        <div class="hero-badge">⚡ Powered by Gemini 2.5 & Multimodal AI</div>
        <h1 class="hero-title">Experience the Future of AI with <span class="gradient-text">NEXORA</span></h1>
        <p class="hero-subtitle">
            An intelligent, production-ready AI platform designed for seamless conversations, complex programming, image understanding, writing, and analytical reasoning.
        </p>

        <div class="hero-cta-group">
            <a href="/assistant" class="btn btn-lg btn-primary shadow-glow">Start Chatting Now</a>
            <?php if (!is_logged_in()): ?>
                <a href="/signup" class="btn btn-lg btn-outline">Create Free Account</a>
            <?php endif; ?>
        </div>

        <!-- Capability Cards Grid -->
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🧠</div>
                <h3>Advanced Reasoning</h3>
                <p>Multi-model artificial intelligence capable of deep contextual analysis and multi-turn problem solving.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💻</div>
                <h3>Expert Coding</h3>
                <p>Generate, debug, and optimize code in dozens of languages with built-in syntax formatting and one-click copy.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🎙️</div>
                <h3>Voice & Multimodal</h3>
                <p>Integrated speech-to-text voice dictation, audio response synthesis, and safe file uploads.</p>
            </div>
        </div>
    </main>

    <footer class="landing-footer">
        <p>&copy; <?= date('Y') ?> NEXORA AI Platform. All rights reserved.</p>
    </footer>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
