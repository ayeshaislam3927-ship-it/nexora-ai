/**
 * NEXORA - Authentication & Onboarding Handler
 */

document.addEventListener('DOMContentLoaded', () => {
    const alertBox = document.getElementById('authAlert');

    function showAlert(msg, type = 'danger') {
        if (!alertBox) return;
        alertBox.className = `alert alert-${type}`;
        alertBox.innerText = msg;
        alertBox.classList.remove('hidden');
    }

    function hideAlert() {
        if (alertBox) alertBox.classList.add('hidden');
    }

    // LOGIN FORM
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            hideAlert();

            const btn = document.getElementById('btnLogin');
            btn.disabled = true;
            btn.innerText = 'Signing in...';

            const formData = new FormData(loginForm);

            try {
                const res = await fetch('/api/auth/login.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    if (data.token) localStorage.setItem('nexora_auth_token', data.token);
                    window.location.href = data.redirect || '/assistant';
                } else {
                    showAlert(data.error || 'Login failed.');
                    btn.disabled = false;
                    btn.innerText = 'Continue';
                }
            } catch (err) {
                showAlert('Network error. Please try again.');
                btn.disabled = false;
                btn.innerText = 'Continue';
            }
        });
    }

    // ONBOARDING REGISTRATION FORM (ChatGPT Style)
    let currentStep = 1;
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const step3 = document.getElementById('step3');
    const step4 = document.getElementById('step4');

    const titleEl = document.getElementById('onboardingTitle');
    const subEl = document.getElementById('onboardingSubtitle');

    const btnStep1 = document.getElementById('btnStep1');
    const btnStep2 = document.getElementById('btnStep2');
    const btnStep3 = document.getElementById('btnStep3');
    const otpContainer = document.getElementById('otpContainer');

    // Step 1: Send OTP & Verify
    if (btnStep1) {
        let otpSent = false;

        btnStep1.addEventListener('click', async () => {
            hideAlert();
            const email = document.getElementById('regEmail').value.trim();

            if (!email) {
                showAlert('Please enter a valid email address.');
                return;
            }

            if (!otpSent) {
                // Send OTP
                btnStep1.disabled = true;
                btnStep1.innerText = 'Sending code...';

                try {
                    const res = await fetch('/api/auth/send-otp.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ email })
                    });
                    const data = await res.json();

                    if (data.success) {
                        otpSent = true;
                        otpContainer.classList.remove('hidden');
                        showAlert('6-digit code sent to your email. Enter it below.', 'success');
                        btnStep1.disabled = false;
                        btnStep1.innerText = 'Verify & Continue';
                    } else {
                        showAlert(data.error || 'Error sending code.');
                        btnStep1.disabled = false;
                        btnStep1.innerText = 'Continue';
                    }
                } catch (e) {
                    showAlert('Server error. Please try again.');
                    btnStep1.disabled = false;
                    btnStep1.innerText = 'Continue';
                }
            } else {
                // Verify OTP Code
                const otpCode = document.getElementById('otpCode').value.trim();
                if (!otpCode || otpCode.length !== 6) {
                    showAlert('Please enter the 6-digit verification code.');
                    return;
                }

                btnStep1.disabled = true;
                btnStep1.innerText = 'Verifying...';

                try {
                    const res = await fetch('/api/auth/verify-otp.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ email, otp_code: otpCode })
                    });
                    const data = await res.json();

                    if (data.success) {
                        // Advance to Step 2
                        step1.classList.add('hidden');
                        step2.classList.remove('hidden');
                        titleEl.innerText = 'Tell us about yourself';
                        subEl.innerText = 'Step 2 of 4: Enter your name';
                        currentStep = 2;
                    } else {
                        showAlert(data.error || 'Invalid verification code.');
                        btnStep1.disabled = false;
                        btnStep1.innerText = 'Verify & Continue';
                    }
                } catch (e) {
                    showAlert('Verification server error.');
                    btnStep1.disabled = false;
                    btnStep1.innerText = 'Verify & Continue';
                }
            }
        });
    }

    // Step 2 -> Step 3
    if (btnStep2) {
        btnStep2.addEventListener('click', () => {
            const fn = document.getElementById('firstName').value.trim();
            const ln = document.getElementById('lastName').value.trim();

            if (!fn || !ln) {
                showAlert('Please enter both first and last name.');
                return;
            }
            hideAlert();
            step2.classList.add('hidden');
            step3.classList.remove('hidden');
            titleEl.innerText = 'When were you born?';
            subEl.innerText = 'Step 3 of 4: Date of birth';
            currentStep = 3;
        });
    }

    // Step 3 -> Step 4
    if (btnStep3) {
        btnStep3.addEventListener('click', () => {
            hideAlert();
            step3.classList.add('hidden');
            step4.classList.remove('hidden');
            titleEl.innerText = 'Secure your account';
            subEl.innerText = 'Step 4 of 4: Choose a password';
            currentStep = 4;
        });
    }

    // Final Submit Step 4
    const onboardingForm = document.getElementById('onboardingForm');
    if (onboardingForm) {
        onboardingForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            hideAlert();

            const p1 = document.getElementById('regPassword').value;
            const p2 = document.getElementById('confirmPassword').value;

            if (p1 !== p2) {
                showAlert('Passwords do not match.');
                return;
            }

            const btn = document.getElementById('btnFinalSubmit');
            btn.disabled = true;
            btn.innerText = 'Creating account...';

            const payload = {
                email: document.getElementById('regEmail').value.trim(),
                first_name: document.getElementById('firstName').value.trim(),
                last_name: document.getElementById('lastName').value.trim(),
                date_of_birth: document.getElementById('dob').value,
                password: p1,
                confirm_password: p2
            };

            try {
                const res = await fetch('/api/auth/register.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();

                if (data.success) {
                    if (data.token) localStorage.setItem('nexora_auth_token', data.token);
                    window.location.href = data.redirect || '/assistant';
                } else {
                    showAlert(data.error || 'Registration failed.');
                    btn.disabled = false;
                    btn.innerText = 'Complete Registration';
                }
            } catch (err) {
                showAlert('Server error completing registration.');
                btn.disabled = false;
                btn.innerText = 'Complete Registration';
            }
        });
    }
});

// Google Identity Callback
async function handleGoogleSignIn(response) {
    if (!response.credential) return;

    try {
        // Decode JWT payload locally or send to backend
        const base64Url = response.credential.split('.')[1];
        const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
        const jsonPayload = decodeURIComponent(atob(base64).split('').map(c => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)).join(''));
        const profile = JSON.parse(jsonPayload);

        const res = await fetch('/api/auth/google-login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                credential: response.credential,
                email: profile.email,
                first_name: profile.given_name || 'Google',
                last_name: profile.family_name || 'User',
                google_id: profile.sub
            })
        });
        const data = await res.json();

        if (data.success) {
            if (data.token) localStorage.setItem('nexora_auth_token', data.token);
            window.location.href = data.redirect || '/assistant';
        } else {
            alert(data.error || 'Google login failed.');
        }
    } catch (e) {
        alert('Google authentication error.');
    }
}
