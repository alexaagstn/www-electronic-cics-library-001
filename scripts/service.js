function toast(message, type) {
    var container = document.getElementById("toast");
    var item = document.createElement("div");

    item.className = "toast-item " + (type || "info");
    item.textContent = message;

    container.appendChild(item);

    setTimeout(function () {
        item.classList.add("visible");
    }, 10);

    setTimeout(function () {
        item.classList.remove("visible");

        setTimeout(function () {
            item.remove();
        }, 300);
    }, 3000);
}

function buildInitials(first, last) {
    var a = first ? first.charAt(0) : '';
    var b = last ? last.charAt(0) : '';
    return (a + b).toUpperCase() || 'G';
}

function showPanel(panelId, button) {
    document.querySelectorAll('.panel').forEach(function (p) {
        p.classList.remove('active');
    });

    document.getElementById(panelId).classList.add('active');

    document.querySelectorAll('.nav-btn').forEach(function (btn) {
        btn.classList.remove('active');
        btn.removeAttribute('aria-current');
    });

    button.classList.add('active');
    button.setAttribute('aria-current', 'page');

    var clone = button.cloneNode(true);

    clone.querySelectorAll('.n-badge, .n-icon').forEach(function (el) {
        el.remove();
    });

    document.getElementById('tb-title').textContent = clone.textContent.trim();
}

function showRestrictedPanel(panelId, button) {
    if (isGuest) {
        toast("Please login to use this feature.", "error");
        return;
    }

    showPanel(panelId, button);
}

function doLogout() {
    window.location.href = "LoginPage.php";
}

$(document).ready(function () {
    if (typeof firstName !== "undefined" && typeof lastName !== "undefined") {

        var initials = buildInitials(firstName, lastName);

        if (document.getElementById('sb-av')) {
            document.getElementById('sb-av').textContent = initials;
        }

        if (document.getElementById('prof-av-lg')) {
            document.getElementById('prof-av-lg').textContent = initials;
        }
    }
});

function guestAccess() {
    window.location.href = "DashboardPage.php?guest=1";
}

function togglePass(inputId) {
    var input = document.getElementById(inputId);

    if (input.type === "password") {
        input.type = "text";
    } 
    else {
        input.type = "password";
    }
}

function showForgotNotice() {
    alert("Password reset is handled by the CICS E-Library system.");
}

function doLogin() {
    var email = document.getElementById("l-user").value.trim();
    var password = document.getElementById("l-pass").value;

    document.getElementById("login-alert").classList.remove("show");
    document.getElementById("login-alert").innerHTML = "";

    if (email === "" || password === "") {
        document.getElementById("login-alert").classList.add("show");
        document.getElementById("login-alert").innerHTML = "Please fill in all fields.";
        return;
    }

    $.ajax({
        url: "../controllers/Controller.php",
        type: "POST",
        data: {
            loginEmail: email,
            loginPassword: password
        },
        success: function (returnedData) {

            returnedData = returnedData.trim();

            if (returnedData == "admin") {
                window.location.href = "AdminDashboardPage.php";
            }
            else if (returnedData == "student" || returnedData == "faculty") {
                window.location.href = "DashboardPage.php";
            }
            else if (returnedData == "invalid") {
                document.getElementById("login-alert").classList.add("show");
                document.getElementById("login-alert").innerHTML = "Invalid account or password.";
            }
            else {
                document.getElementById("login-alert").classList.add("show");
                document.getElementById("login-alert").innerHTML = "Login error. Check console.";
                console.log("UNEXPECTED RESPONSE:", returnedData);
            }
        },
        error: function (xhr) {
            document.getElementById("login-alert").classList.add("show");
            document.getElementById("login-alert").innerHTML = "Server error.";
            console.log(xhr.responseText);
        }
    });
}

let selectedRole = "student";

function selectRole(role) {
    selectedRole = role;

    document.getElementById("chip-student").classList.remove("selected");
    document.getElementById("chip-faculty").classList.remove("selected");

    document.getElementById("chip-" + role).classList.add("selected");

    let courseGroup = document.getElementById("course-group");
    let yearGroup = document.getElementById("year-group");
    let courseSelect = document.getElementById("r-course");
    let yearSelect = document.getElementById("r-year");

    if (role === "student") {
        courseGroup.style.display = "";
        yearGroup.style.display = "";
        courseSelect.required = true;
        yearSelect.required = true;
    } 
    else {
        courseGroup.style.display = "none";
        yearGroup.style.display = "none";
        courseSelect.required = false;
        yearSelect.required = false;
        courseSelect.value = "";
        yearSelect.value = "";
    }
}

function checkStrength() {
    let pass = document.getElementById("r-pass").value;
    let fill = document.getElementById("strength-fill");
    let text = document.getElementById("strength-text");

    let score = 0;

    if (pass.length >= 6) score++;
    if (/[A-Z]/.test(pass)) score++;
    if (/[0-9]/.test(pass)) score++;
    if (/[^A-Za-z0-9]/.test(pass)) score++;

    if (pass.length === 0) {
        fill.style.width = "0%";
        text.innerHTML = "";
        return;
    }

    if (score === 1) {
        fill.style.width = "25%";
        fill.style.background = "#F05252";
        text.innerHTML = "Weak password";
    }
    else if (score === 2) {
        fill.style.width = "50%";
        fill.style.background = "#F0A030";
        text.innerHTML = "Fair password";
    }
    else if (score === 3) {
        fill.style.width = "75%";
        fill.style.background = "#3B9FD4";
        text.innerHTML = "Good password";
    }
    else {
        fill.style.width = "100%";
        fill.style.background = "#34C47A";
        text.innerHTML = "Strong password";
    }
}

function doRegister() {
    var firstName = document.getElementById("r-fname").value.trim();
    var lastName = document.getElementById("r-lname").value.trim();
    var ustID = document.getElementById("r-id").value.trim();
    var email = document.getElementById("r-email").value.trim();
    var password = document.getElementById("r-pass").value;
    var confirmPassword = document.getElementById("r-pass2").value;
    var username = document.getElementById("r-username").value.trim();
    var course = document.getElementById("r-course").value;
    var year = document.getElementById("r-year").value;

    document.getElementById("reg-alert").classList.remove("show");
    document.getElementById("reg-success").classList.remove("show");
    document.getElementById("reg-alert").innerHTML = "";
    document.getElementById("reg-success").innerHTML = "";

    if (firstName === "" || lastName === "" || ustID === "" || email === "" || password === "" || confirmPassword === "" || username === "") {
        document.getElementById("reg-alert").classList.add("show");
        document.getElementById("reg-alert").innerHTML = "Please fill in all required fields.";
        return;
    }

    if (selectedRole === "student" && (course === "" || year === "")) {
        document.getElementById("reg-alert").classList.add("show");
        document.getElementById("reg-alert").innerHTML = "Please fill in all required fields.";
        return;
    }

    if (password !== confirmPassword) {
        document.getElementById("reg-alert").classList.add("show");
        document.getElementById("reg-alert").innerHTML = "Passwords do not match.";
        return;
    }

    $.ajax({
        url: "../controllers/Controller.php",
        type: "POST",
        data: {
            ustID: ustID,
            firstName: firstName,
            lastName: lastName,
            email: email,
            password: password,
            role: selectedRole,
            username: username,
            course: course,
            year_level: year
        },
        success: function (returnedData) {

            returnedData = returnedData.trim();

            if (returnedData == "success") {
                document.getElementById("reg-success").classList.add("show");
                document.getElementById("reg-success").innerHTML = "Registration successful.";

                setTimeout(function () {
                    window.location.href = "LoginPage.php";
                }, 1500);
            }
            else if (returnedData == "exists") {
                document.getElementById("reg-alert").classList.add("show");
                document.getElementById("reg-alert").innerHTML = "UST ID or Email already exists.";
            }
            else {
                document.getElementById("reg-alert").classList.add("show");
                document.getElementById("reg-alert").innerHTML = "Registration failed.";
            }
        },
        error: function (xhr) {
            alert(xhr.status + " : " + xhr.responseText);
        }
    });
}

document.addEventListener("DOMContentLoaded", function () {
    if (document.getElementById("chip-student") && document.getElementById("chip-faculty")) {
        selectRole("student");
    }
});