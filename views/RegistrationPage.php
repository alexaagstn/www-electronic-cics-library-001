<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Page</title>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,400&family=Sora:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>

<div id="page-register" class="screen page active">

    <div class="auth-left">
        <div class="auth-brand">
            <div class="tag">New Account</div>
            <h1>Join<br> UST CICS <em>E-Library</em></h1>
            <p>Create your E-library account</p>
        </div>

        <div class="auth-features">

            <div class="auth-feature">
                <div class="feat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <div class="feat-text">
                    <strong>Student &amp; Faculty Access</strong>
                    <span>Different privileges based on your role</span>
                </div>
            </div>

            <div class="auth-feature">
                <div class="feat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                </div>
                <div class="feat-text">
                    <strong>Due Date Alerts</strong>
                    <span>Get notified before your borrowed items are due</span>
                </div>
            </div>

            <div class="auth-feature">
                <div class="feat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </div>
                <div class="feat-text">
                    <strong>Secure Portal</strong>
                    <span>Your data is securely protected within the UST CICS E-Library system.</span>
                </div>
            </div>

        </div>
    </div>

    <div class="auth-right">

        <div class="form-head">
            <span class="step-tag">Registration</span>
            <h2>Create Account</h2>
            <p>Fill in your details to register</p>
        </div>

        <div class="alert error" id="reg-alert"></div>
        <div class="alert success" id="reg-success"></div>

        <div class="field-group fade-up">
            <label>I am a</label>
            <div class="role-select" role="group" aria-label="Select your role">

                <div class="role-chip selected" id="chip-student"
                    role="radio" aria-checked="true" tabindex="0"
                    onclick="selectRole('student')"
                    onkeydown="if(event.key==='Enter'||event.key===' ')selectRole('student')">
                    <svg viewBox="0 0 24 24">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                        <path d="M6 12v5c0 1.657 2.686 3 6 3s6-1.343 6-3v-5"/>
                    </svg>
                    Student
                </div>

                <div class="role-chip" id="chip-faculty"
                    role="radio" aria-checked="false" tabindex="0"
                    onclick="selectRole('faculty')"
                    onkeydown="if(event.key==='Enter'||event.key===' ')selectRole('faculty')">
                    <svg viewBox="0 0 24 24">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                    </svg>
                    Faculty
                </div>

            </div>
        </div>

        <div class="field-row fade-up-2">
            <div class="field-group">
                <label for="r-fname">First Name</label>
                <div class="field-wrap">
                    <span class="field-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                    </span>
                    <input type="text" id="r-fname" placeholder="Juan" autocomplete="given-name" required />
                </div>
            </div>

            <div class="field-group">
                <label for="r-lname">Last Name</label>
                <div class="field-wrap">
                    <span class="field-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                    </span>
                    <input type="text" id="r-lname" placeholder="Santos" autocomplete="family-name" required />
                </div>
            </div>
        </div>

        <div class="field-group fade-up-2">
            <label for="r-id">UST ID Number</label>
            <div class="field-wrap">
                <span class="field-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <rect x="2" y="5" width="20" height="14" rx="2"/>
                        <path d="M16 10h2M16 14h2M6 10h6M6 14h4"/>
                    </svg>
                </span>
                <input type="text" id="r-id"
                    placeholder="e.g., 2026XXXXXX"
                    pattern="^\d{10}$"
                    title="UST ID must contain 10 digits only"
                    autocomplete="off" required />
            </div>
        </div>

        <div class="field-group fade-up-3">
            <label for="r-email">Email Address</label>
            <div class="field-wrap">
                <span class="field-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                </span>
                    <input type="email" id="r-email"
                        placeholder="juan.santos@ust.edu.ph"
                        pattern="^[a-zA-Z0-9._%+-]+@ust\.edu\.ph$"
                        title="UST email only"
                        autocomplete="email"
                        required />
            </div>
        </div>

        <div class="field-group fade-up-3" id="course-group">
            <label for="r-course">Course / Department</label>
            <div class="field-wrap">
                <span class="field-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                </span>
                <select id="r-course" required>
                    <option value="" disabled selected>Select your course / department</option>
                    <option value="BS Computer Science">BS Computer Science</option>
                    <option value="BS Information Technology">BS Information Technology</option>
                    <option value="BS Information Systems">BS Information Systems</option>
                </select>
            </div>
        </div>

        <div class="field-row fade-up-3">

            <div class="field-group">
                <label for="r-username">Username</label>
                <div class="field-wrap">
                    <span class="field-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </span>
                    <input type="text" id="r-username"
                        placeholder="username"
                        autocomplete="username"
                        required />
                </div>
            </div>

            <div class="field-group" id="year-group">
                <label for="r-year">Year Level</label>
                <div class="field-wrap">
                    <span class="field-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                    </span>
                    <select id="r-year" required>
                        <option value="" disabled selected>Select</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                    </select>
                </div>
            </div>

        </div>

        <div class="field-group fade-up-4">
            <label for="r-pass">Password</label>
            <div class="field-wrap">
                <span class="field-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </span>
                <input type="password" id="r-pass"
                    placeholder="Minimum 6 characters"
                    autocomplete="new-password"
                    oninput="checkStrength()"
                    required />
            </div>
            <div class="strength-bar">
                <div class="strength-fill" id="strength-fill"></div>
            </div>
            <div id="strength-text"></div>
        </div>

        <div class="field-group fade-up-4">
            <label for="r-pass2">Confirm Password</label>
            <div class="field-wrap">
                <span class="field-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </span>
                <input type="password" id="r-pass2"
                    placeholder="Repeat your password"
                    autocomplete="new-password"
                    required />
            </div>
        </div>

        <button class="btn-gold" type="button" onclick="doRegister()" style="margin-top: 8px;">
            Create Library Account
        </button>

        <div class="auth-footer">
            Already have an account? <a href="LoginPage.php">Sign In</a>
        </div>

    </div>
</div>

<script src="../scripts/service.js"></script>


</body>
</html>