<?php 
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,400&family=Sora:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>

<body class="auth-page">

<div id="page-login" class="screen page active">

    <div class="auth-left">
        <div class="auth-brand">
            <div class="tag">University of Santo Tomas &middot; CICS</div>
            <h1>E<em>-</em>Library</h1>
            <p><em>Veritas in Caritate</em></p>
        </div>

        <div class="auth-features">

            <div class="auth-feature">
                <div class="feat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </div>
                <div class="feat-text">
                    <strong>OPAC Search</strong>
                    <span>Search thousands of print and electronic materials</span>
                </div>
            </div>

            <div class="auth-feature">
                <div class="feat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                        <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                    </svg>
                </div>
                <div class="feat-text">
                    <strong>Borrow &amp; Reserve</strong>
                    <span>Manage your loans and get notified when items are available</span>
                </div>
            </div>

            <div class="auth-feature">
                <div class="feat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                </div>
                <div class="feat-text">
                    <strong>E-Resources</strong>
                    <span>Access journals, e-books, and academic databases</span>
                </div>
            </div>

        </div>
    </div>

    <div class="auth-right">

        <div class="form-head">
            <span class="step-tag">Sign In</span>
            <h2>Welcome back</h2>
            <p>Access your E-Library account</p>
        </div>

        <div class="alert error" id="login-alert" role="alert" aria-live="polite"></div>

        <div class="field-group fade-up">
            <label for="l-user">Email Address <span class="required">*</span></label>
            <div class="field-wrap">
                <span class="field-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </span>

                <input 
                    type="email" 
                    id="l-user" 
                    name="email"
                    placeholder="e.g. juan@ust.edu.ph" 
                    autocomplete="username" 
                    maxlength="50"
                    pattern="^[a-zA-Z0-9._%+-]+@ust\.edu\.ph$"
                    title="Use your UST email address only"
                    oninput="validateLoginEmail();"
                    onkeydown="if(event.key==='Enter')doLogin()" 
                    required 
                />
            </div>
            <small class="login-msg" id="login-email-msg"></small>
        </div>

        <div class="field-group fade-up-2">
            <label for="l-pass">Password <span class="required">*</span></label>
            <div class="field-wrap" style="position: relative;">
                <span class="field-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </span>

                <input 
                    type="password" 
                    id="l-pass" 
                    name="password"
                    placeholder="Enter your password" 
                    autocomplete="current-password" 
                    minlength="8" 
                    maxlength="30"
                    oninput="validateLoginPassword();"
                    onkeydown="if(event.key==='Enter')doLogin()" 
                    required 
                />

                <button type="button" class="pass-toggle" onclick="togglePass('l-pass')" aria-label="Show or hide password" title="Toggle visibility"></button>
            </div>
            <small class="login-msg" id="login-password-msg"></small>
        </div>

        <a class="forgot-link fade-up-2"
            onclick="openForgotModal()"
            role="button"
            tabindex="0"
            onkeydown="if(event.key==='Enter')openForgotModal()">
            Forgot password?
        </a>

        <button class="btn-gold fade-up-3" type="button" onclick="doLogin()">
            Sign In to E-Library
        </button>

        <div class="divider">or</div>

        <button class="btn-outline" type="button" onclick="guestAccess()">
            Continue as Guest
        </button>

        <div class="auth-footer" style="margin-top: 24px;">
            No account? <a href="RegistrationPage.php">Create one here</a>
        </div>

    </div>
</div>

<div class="forgot-modal" id="forgotModal">

    <div class="forgot-box">

        <button class="forgot-close" onclick="closeForgotModal()">×</button>

        <h3>Forgot Password</h3>

        <p>
            Enter your registered UST email address. If the account exists,
            password reset instructions will be sent.
        </p>

        <input 
            type="email"
            id="forgot-email"
            name="forgot_email"
            placeholder="example@ust.edu.ph"
            maxlength="50"
            pattern="^[a-zA-Z0-9._%+-]+@ust\.edu\.ph$"
            oninput="validateForgotEmail();"
        >

        <small class="login-msg" id="forgot-email-msg"></small>

        <button class="forgot-send" onclick="sendResetFunc()">
            Send Reset Instructions
        </button>

    </div>

</div>

<script src="../scripts/service.js"></script>

</body>
</html>