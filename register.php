<?php
/**
 * NEXORA - ChatGPT-Style Onboarding Registration Page
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

if (is_logged_in()) {
    header('Location: /assistant');
    exit;
}

$pageTitle = 'Sign Up — NEXORA';
$extraCss = ['/assets/css/auth.css'];
$extraJs = ['/assets/js/auth.js'];

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card onboarding-card">
        <div class="auth-header">
            <a href="/" class="auth-logo-link">
                <img src="/assets/logo/nexora-logo.png" alt="NEXORA Logo" class="auth-logo">
            </a>
            <h1 class="auth-title" id="onboardingTitle">Create your account</h1>
            <p class="auth-subtitle" id="onboardingSubtitle">Step 1 of 4: Enter your email address</p>
        </div>

        <div id="authAlert" class="alert alert-danger hidden"></div>

        <!-- Multi-Step Onboarding Form -->
        <form id="onboardingForm" class="auth-form">
            <?= csrf_input_field() ?>

            <!-- STEP 1: EMAIL & OTP -->
            <div id="step1" class="onboarding-step active">
                <div class="google-auth-wrapper">
                    <div id="g_id_onload"
                        data-client_id="<?= htmlspecialchars(GOOGLE_CLIENT_ID) ?>"
                        data-callback="handleGoogleSignIn"
                        data-auto_prompt="false">
                    </div>
                    <div class="g_id_signin" data-type="standard" data-size="large" data-theme="dark" data-text="signup_with" data-shape="rectangular" data-logo_alignment="left" data-width="100%"></div>
                </div>

                <div class="auth-divider">
                    <span>OR</span>
                </div>

                <div class="form-group">
                    <label for="regEmail">Email address</label>
                    <input type="email" id="regEmail" name="email" class="form-control" placeholder="name@example.com" required>
                </div>

                <div id="otpContainer" class="form-group hidden">
                    <label for="otpCode">6-Digit Verification Code</label>
                    <input type="text" id="otpCode" class="form-control letter-spacing-lg" placeholder="123456" maxlength="6" pattern="[0-9]{6}">
                    <small class="form-text text-muted">We sent a verification code to your email. (Check server logs if SMTP is offline)</small>
                </div>

                <button type="button" id="btnStep1" class="btn btn-primary btn-block btn-lg mt-3">
                    <span>Continue</span>
                </button>
            </div>

            <!-- STEP 2: NAME -->
            <div id="step2" class="onboarding-step hidden">
                <div class="form-group">
                    <label for="firstName">First name</label>
                    <input type="text" id="firstName" name="first_name" class="form-control" placeholder="John">
                </div>
                <div class="form-group">
                    <label for="lastName">Last name</label>
                    <input type="text" id="lastName" name="last_name" class="form-control" placeholder="Doe">
                </div>
                <button type="button" id="btnStep2" class="btn btn-primary btn-block btn-lg">
                    <span>Continue</span>
                </button>
            </div>

            <!-- STEP 3: DATE OF BIRTH -->
            <div id="step3" class="onboarding-step hidden">
                <div class="form-group">
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="date_of_birth" class="form-control">
                </div>
                <button type="button" id="btnStep3" class="btn btn-primary btn-block btn-lg">
                    <span>Continue</span>
                </button>
            </div>

            <!-- STEP 4: PASSWORD -->
            <div id="step4" class="onboarding-step hidden">
                <div class="form-group">
                    <label for="regPassword">Create Password</label>
                    <input type="password" id="regPassword" name="password" class="form-control" placeholder="At least 8 characters">
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirm_password" class="form-control" placeholder="Re-enter password">
                </div>
                <button type="submit" id="btnFinalSubmit" class="btn btn-primary btn-block btn-lg">
                    <span>Complete Registration</span>
                </button>
            </div>
        </form>

        <div class="auth-footer">
            <p>Already have an account? <a href="/login" class="auth-link">Sign in</a></p>
        </div>
    </div>
</div>

<script src="https://accounts.google.com/gsi/client" async defer></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
