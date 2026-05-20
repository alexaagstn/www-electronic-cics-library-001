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

    if (!validateLoginFormLive()) {
        document.getElementById("login-alert").textContent = "Kindly check your email and password before signing in.";
        document.getElementById("login-alert").style.display = "block";
        return;
    }

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

    let weakPasswords = [
        "123456",
        "000000",
        "abcdef",
        "password",
        "login",
        "qwerty",
        "p@ssw0rd123"
    ];

    let lowerPass = pass.toLowerCase();

    if (pass.length === 0) {
        fill.style.width = "0%";
        text.innerHTML = "";
        return;
    }

    for (let i = 0; i < weakPasswords.length; i++) {

        if (lowerPass.includes(weakPasswords[i])) {
            fill.style.width = "25%";
            fill.style.background = "#F05252";
            text.innerHTML = "Weak password";
            return;
        }
    }

    if (pass.length >= 12) score++;

    if (/[A-Z]/.test(pass)) score++;

    if (/[a-z]/.test(pass)) score++;

    if (/[0-9]/.test(pass)) score++;

    if (/[^A-Za-z0-9]/.test(pass)) score++;

    if (pass.includes("-")) score++;

    if (score <= 2) {
        fill.style.width = "25%";
        fill.style.background = "#F05252";
        text.innerHTML = "Weak password";
    }

    else if (score === 3 || score === 4) {
        fill.style.width = "60%";
        fill.style.background = "#F0A030";
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

    if (!validateRegisterFormLive()) {
    document.getElementById("reg-alert").textContent = "Kindly check the highlighted fields before creating your account.";
    document.getElementById("reg-alert").style.display = "block";
    return;
}

    if (firstName === "" || lastName === "" || ustID === "" || email === "" || password === "" || confirmPassword === "" || username === "") {
        document.getElementById("reg-alert").classList.add("show");
        document.getElementById("reg-alert").innerHTML = "Please fill in all required fields.";
        return;
    }

    if (!/^\d{10}$/.test(ustID)) {
        document.getElementById("reg-alert").classList.add("show");
        document.getElementById("reg-alert").innerHTML = "Please enter a valid 10-digit UST ID.";
        return;
    }

    let yearPrefix = parseInt(ustID.substring(0,4));

    if(yearPrefix > 2025){
        document.getElementById("reg-alert").classList.add("show");
        document.getElementById("reg-alert").innerHTML = "Please enter a valid UST ID number.";
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

    if (selectedRole === "student") {

        if (!email.toLowerCase().endsWith(".cics@ust.edu.ph")) {

            document.getElementById("reg-alert").classList.add("show");

            document.getElementById("reg-alert").innerHTML =
            "Please use your official CICS email address.";

            return;
        }
    }

    else if (selectedRole === "faculty") {

        if (!email.toLowerCase().endsWith("@ust.edu.ph")) {

            document.getElementById("reg-alert").classList.add("show");

            document.getElementById("reg-alert").innerHTML =
            "Please use your official UST email address.";

            return;
        }
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
            else if (returnedData == "invalid_email") {
                    document.getElementById("reg-alert").classList.add("show");
                    document.getElementById("reg-alert").innerHTML = "UST email only.";
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

function renderOpac() {

    let search =
    document.getElementById("opac-q").value.toLowerCase();

    let category =
    document.getElementById("opac-cat").value.toLowerCase();

    let rows =
    document.querySelectorAll("#opac-grid tbody tr");

    rows.forEach(function(row) {

        let text =
        row.innerText.toLowerCase();

        let categoryCell =
        row.children[3].innerText.toLowerCase();

        let matchesSearch =
        text.includes(search);

        let matchesCategory =
        category === "" ||
        categoryCell.includes(category);

        if(matchesSearch && matchesCategory){
            row.style.display = "";
        }
        else{
            row.style.display = "none";
        }
    });
}

function updateProfileFunc() {
    let firstName = document.getElementById("edit-fname").value;
    let lastName = document.getElementById("edit-lname").value;
    let email = document.getElementById("edit-email").value;
    let password = document.getElementById("edit-pass").value;

    $.ajax({
        url: "../controllers/Controller.php",
        type: "POST",
        data: {
            updateProfile: true,
            firstName: firstName,
            lastName: lastName,
            email: email,
            password: password
        },
        success: function(response) {
            if(response.trim() == "1") {
                alert("Profile updated successfully.");
                location.reload();
            }
            else {
                alert(response);
            }
        }
    });
}

function openForgotModal() {
    document.getElementById("forgotModal").style.display = "flex";
}

function closeForgotModal() {
    document.getElementById("forgotModal").style.display = "none";
}

function sendResetFunc() {
    if (!validateForgotEmail()) {
        return;
    }

    const email = document.getElementById("forgot-email").value.trim();

    const formData = new FormData();
    formData.append("email", email);

    fetch("../controllers/forgot_password.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(result => {
        result = result.trim();

        if (result === "success") {
            alert("If the email is registered, password reset instructions will be sent.");
            closeForgotModal();
        } else if (result === "invalid_email") {
            alert("Please use your UST email address.");
        } else if (result === "email_failed") {
            alert("Reset link was created, but the email could not be sent.");
        } else {
            alert("Something went wrong. Please try again.");
            console.log(result);
        }
    })
    .catch(error => {
        console.log("Forgot password error:", error);
        alert("Something went wrong. Please try again.");
    });
}

let sortDirection = {};

function sortTable(columnIndex) {
    const table = document.getElementById("adminMaterialsTable");
    const tbody = table.querySelector("tbody");
    const rows = Array.from(tbody.querySelectorAll("tr"));

    sortDirection[columnIndex] = !sortDirection[columnIndex];

    rows.sort((a, b) => {
        let cellA = a.children[columnIndex].innerText.trim().toLowerCase();
        let cellB = b.children[columnIndex].innerText.trim().toLowerCase();

        // For number columns like # and Copies
        if (!isNaN(cellA) && !isNaN(cellB)) {
            cellA = Number(cellA);
            cellB = Number(cellB);
        }

        if (cellA < cellB) {
            return sortDirection[columnIndex] ? -1 : 1;
        }

        if (cellA > cellB) {
            return sortDirection[columnIndex] ? 1 : -1;
        }

        return 0;
    });

    rows.forEach(row => tbody.appendChild(row));
}

let circulationSortDirection = {};

function sortCirculationTable(columnIndex) {
    const table = document.getElementById("circulationTable");
    const tbody = table.querySelector("tbody");
    const rows = Array.from(tbody.querySelectorAll("tr"));

    circulationSortDirection[columnIndex] = !circulationSortDirection[columnIndex];

    rows.sort((a, b) => {
        let cellA = a.children[columnIndex].innerText.trim().toLowerCase();
        let cellB = b.children[columnIndex].innerText.trim().toLowerCase();

        // For date column
        if (columnIndex === 2) {
            cellA = new Date(cellA);
            cellB = new Date(cellB);
        }

        if (cellA < cellB) {
            return circulationSortDirection[columnIndex] ? -1 : 1;
        }

        if (cellA > cellB) {
            return circulationSortDirection[columnIndex] ? 1 : -1;
        }

        return 0;
    });

    rows.forEach(row => tbody.appendChild(row));
}

let usersSortDirection = {};

function sortUsersTable(columnIndex) {
    const table = document.getElementById("adminUsersTable");
    const tbody = table.querySelector("tbody");
    const rows = Array.from(tbody.querySelectorAll("tr"));

    usersSortDirection[columnIndex] = !usersSortDirection[columnIndex];

    rows.sort((a, b) => {
        let cellA = a.children[columnIndex].innerText.trim().toLowerCase();
        let cellB = b.children[columnIndex].innerText.trim().toLowerCase();

        // Date Joined column
        if (columnIndex === 4) {
            cellA = new Date(cellA);
            cellB = new Date(cellB);
        }

        // UST ID column, if numeric
        if (columnIndex === 1 && !isNaN(cellA) && !isNaN(cellB)) {
            cellA = Number(cellA);
            cellB = Number(cellB);
        }

        if (cellA < cellB) {
            return usersSortDirection[columnIndex] ? -1 : 1;
        }

        if (cellA > cellB) {
            return usersSortDirection[columnIndex] ? 1 : -1;
        }

        return 0;
    });

    rows.forEach(row => tbody.appendChild(row));
}

document.getElementById("r-role").value = role;

function setFieldMessage(messageId, message, isValid) {
    const msg = document.getElementById(messageId);

    if (!msg) return;

    msg.textContent = message;
    msg.classList.remove("valid", "invalid");

    if (message !== "") {
        msg.classList.add(isValid ? "valid" : "invalid");
    }
}

function validateFirstName() {
    const fname = document.getElementById("r-fname").value.trim();

    if (fname === "") {
        setFieldMessage("fname-msg", "First name is required.", false);
        return false;
    }

    setFieldMessage("fname-msg", "Valid first name.", true);
    return true;
}

function validateLastName() {
    const lname = document.getElementById("r-lname").value.trim();

    if (lname === "") {
        setFieldMessage("lname-msg", "Last name is required.", false);
        return false;
    }

    setFieldMessage("lname-msg", "Valid last name.", true);
    return true;
}

function validateUstId() {
    const ustId = document.getElementById("r-id").value.trim();

    if (ustId === "") {
        setFieldMessage("ustid-msg", "UST ID number is required.", false);
        return false;
    }

    if (ustId.length !== 10) {
        setFieldMessage("ustid-msg", "UST ID must be exactly 10 digits.", false);
        return false;
    }

    setFieldMessage("ustid-msg", "Valid UST ID number.", true);
    return true;
}

function validateEmail() {
    const email = document.getElementById("r-email").value.trim();
    const ustEmailPattern = /^[a-zA-Z0-9._%+-]+@ust\.edu\.ph$/;

    if (email === "") {
        setFieldMessage("email-msg", "Email address is required.", false);
        return false;
    }

    if (email.length > 50) {
        setFieldMessage("email-msg", "Email must not exceed 50 characters.", false);
        return false;
    }

    if (!ustEmailPattern.test(email)) {
        setFieldMessage("email-msg", "Use your UST email address only.", false);
        return false;
    }

    setFieldMessage("email-msg", "Valid UST email address.", true);
    return true;
}

function validateCourse() {
    const course = document.getElementById("r-course").value;

    if (course === "") {
        setFieldMessage("course-msg", "Course or department is required.", false);
        return false;
    }

    setFieldMessage("course-msg", "Course selected.", true);
    return true;
}

function validateUsername() {
    const username = document.getElementById("r-username").value.trim();
    const usernamePattern = /^[A-Za-z0-9_]{5,30}$/;

    if (username === "") {
        setFieldMessage("username-msg", "Username is required.", false);
        return false;
    }

    if (username.includes(" ")) {
        setFieldMessage("username-msg", "Username cannot contain spaces.", false);
        return false;
    }

    if (username.length < 5) {
        setFieldMessage("username-msg", "Username must be at least 5 characters.", false);
        return false;
    }

    if (username.length > 30) {
        setFieldMessage("username-msg", "Username must not exceed 30 characters.", false);
        return false;
    }

    if (!usernamePattern.test(username)) {
        setFieldMessage("username-msg", "Use letters, numbers, and underscore only.", false);
        return false;
    }

    setFieldMessage("username-msg", "Valid username.", true);
    return true;
}

function validateYearLevel() {
    const year = document.getElementById("r-year").value;

    if (year === "") {
        setFieldMessage("year-msg", "Year level is required.", false);
        return false;
    }

    setFieldMessage("year-msg", "Year level selected.", true);
    return true;
}

function validatePassword() {
    const password = document.getElementById("r-pass").value;

    if (password === "") {
        setFieldMessage("password-msg", "Password is required.", false);
        return false;
    }

    if (password.length < 8) {
        setFieldMessage("password-msg", "Password must be at least 8 characters.", false);
        return false;
    }

    if (password.length > 30) {
        setFieldMessage("password-msg", "Password must not exceed 30 characters.", false);
        return false;
    }

    setFieldMessage("password-msg", "Password length is valid.", true);
    return true;
}

function validateConfirmPassword() {
    const password = document.getElementById("r-pass").value;
    const confirmPassword = document.getElementById("r-pass2").value;

    if (confirmPassword === "") {
        setFieldMessage("confirm-msg", "Please confirm your password.", false);
        return false;
    }

    if (confirmPassword.length < 8) {
        setFieldMessage("confirm-msg", "Confirm password must be at least 8 characters.", false);
        return false;
    }

    if (password !== confirmPassword) {
        setFieldMessage("confirm-msg", "Passwords do not match.", false);
        return false;
    }

    setFieldMessage("confirm-msg", "Passwords match.", true);
    return true;
}

function validateRegisterFormLive() {
    return (
        validateFirstName() &&
        validateLastName() &&
        validateUstId() &&
        validateEmail() &&
        validateCourse() &&
        validateUsername() &&
        validateYearLevel() &&
        validatePassword() &&
        validateConfirmPassword()
    );
}

function validateLoginEmail() {
    const email = document.getElementById("l-user").value.trim();
    const ustEmailPattern = /^[a-zA-Z0-9._%+-]+@ust\.edu\.ph$/;

    if (email === "") {
        setFieldMessage("login-email-msg", "Email address is required.", false);
        return false;
    }

    if (email.length > 50) {
        setFieldMessage("login-email-msg", "Email must not exceed 50 characters.", false);
        return false;
    }

    if (!ustEmailPattern.test(email)) {
        setFieldMessage("login-email-msg", "Use your UST email address only.", false);
        return false;
    }

    setFieldMessage("login-email-msg", "Valid UST email address.", true);
    return true;
}

function validateLoginPassword() {
    const password = document.getElementById("l-pass").value;

    if (password === "") {
        setFieldMessage("login-password-msg", "Password is required.", false);
        return false;
    }

    if (password.length < 8) {
        setFieldMessage("login-password-msg", "Password must be at least 8 characters.", false);
        return false;
    }

    if (password.length > 30) {
        setFieldMessage("login-password-msg", "Password must not exceed 30 characters.", false);
        return false;
    }

    setFieldMessage("login-password-msg", "Password length is valid.", true);
    return true;
}

function validateLoginFormLive() {
    return validateLoginEmail() && validateLoginPassword();
}

function validateForgotEmail() {
    const email = document.getElementById("forgot-email").value.trim();
    const ustEmailPattern = /^[a-zA-Z0-9._%+-]+@ust\.edu\.ph$/;

    if (email === "") {
        setFieldMessage("forgot-email-msg", "Enter your registered UST email address.", false);
        return false;
    }

    if (email.length > 50) {
        setFieldMessage("forgot-email-msg", "Email must not exceed 50 characters.", false);
        return false;
    }

    if (!ustEmailPattern.test(email)) {
        setFieldMessage("forgot-email-msg", "Use your UST email address only.", false);
        return false;
    }

    setFieldMessage("forgot-email-msg", "Valid UST email address.", true);
    return true;
}

function resetPasswordFunc() {
    const token = document.getElementById("reset-token").value;
    const password = document.getElementById("new-password").value;
    const confirmPassword = document.getElementById("confirm-new-password").value;

    if (password.length < 8 || password.length > 30) {
        alert("Password must be 8 to 30 characters.");
        return;
    }

    if (password !== confirmPassword) {
        alert("Passwords do not match.");
        return;
    }

    const formData = new FormData();
    formData.append("token", token);
    formData.append("password", password);
    formData.append("confirm_password", confirmPassword);

    fetch("../controllers/reset_password.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(result => {
        result = result.trim();

        if (result === "success") {
            alert("Your password has been updated. You may now sign in.");
            window.location.href = "LoginPage.php";
        } else if (result === "invalid_token") {
            alert("This reset link is invalid or expired.");
        } else if (result === "not_match") {
            alert("Passwords do not match.");
        } else {
            alert("Something went wrong. Please try again.");
            console.log(result);
        }
    })
    .catch(error => {
        console.log("Reset password error:", error);
        alert("Something went wrong. Please try again.");
    });
}

function validateResetPassword() {
    const password = document.getElementById("new-password").value;
    const msg = document.getElementById("new-password-msg");

    msg.classList.remove("valid", "invalid");

    if (password === "") {
        msg.textContent = "";
        return false;
    }

    if (password.length < 8) {
        msg.textContent = "Password must be at least 8 characters.";
        msg.classList.add("invalid");
        return false;
    }

    if (password.length > 30) {
        msg.textContent = "Password must not exceed 30 characters.";
        msg.classList.add("invalid");
        return false;
    }

    msg.textContent = "Password length is valid.";
    msg.classList.add("valid");
    return true;
}

function validateResetConfirmPassword() {
    const password = document.getElementById("new-password").value;
    const confirmPassword = document.getElementById("confirm-new-password").value;
    const msg = document.getElementById("confirm-new-password-msg");

    msg.classList.remove("valid", "invalid");

    if (confirmPassword === "") {
        msg.textContent = "";
        return false;
    }

    if (password !== confirmPassword) {
        msg.textContent = "Passwords do not match.";
        msg.classList.add("invalid");
        return false;
    }

    msg.textContent = "Passwords match.";
    msg.classList.add("valid");
    return true;
}