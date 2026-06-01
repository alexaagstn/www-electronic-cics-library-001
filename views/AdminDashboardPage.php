<?php
session_start();

require_once "../bl/UserManagement.php";
$usermanagement = new UserManagement();

if (!isset($_SESSION["user_id"])) {
    header("Location: LoginPage.php");
    exit;
}

if ($_SESSION["role"] != "admin") {
    header("Location: DashboardPage.php");
    exit;
}

if (isset($_POST["addMaterialBtn"])) {

    $title = $_POST["title"];
    $author = $_POST["author"];
    $category = $_POST["category"];
    $material_type = $_POST["material_type"];
    $total_copies = $_POST["total_copies"];
    $available_copies = $_POST["available_copies"];

    $usermanagement->addMaterialFunc($title, $author, $category, $material_type, $total_copies, $available_copies);

    header("Location: AdminDashboardPage.php");
    exit;
}

if (isset($_POST["updateUserBtn"])) {
    $user_id = $_POST["user_id"];
    $new_role = $_POST["new_role"];
    $new_status = $_POST["new_status"] == "active" ? 1 : 0;

    $usermanagement->updateUserRoleFunc($user_id, $new_role);
    $usermanagement->updateUserStatusFunc($user_id, $new_status);

    $usermanagement->logActionFunc(
        $_SESSION["user_id"],
        "Update User",
        "Updated user role and status."
    );

    header("Location: AdminDashboardPage.php");
    exit;
}

if (isset($_POST["changeRoleBtn"])) {
    $user_id = $_POST["user_id"];
    $new_role = $_POST["new_role"];

    $usermanagement->updateUserRoleFunc($user_id, $new_role);
    $usermanagement->logActionFunc($_SESSION["user_id"], "Update User Role", "Changed user role to " . $new_role);

    header("Location: AdminDashboardPage.php");
    exit;
}

if (isset($_POST["toggleUserStatusBtn"])) {
    $user_id = $_POST["user_id"];

    $usermanagement->toggleUserStatusFunc($user_id);
    $usermanagement->logActionFunc($_SESSION["user_id"], "Update User Status", "Toggled user active status.");

    header("Location: AdminDashboardPage.php");
    exit;
}

if (isset($_POST["deleteUserBtn"])) {
    $user_id = $_POST["user_id"];

    $usermanagement->deactivateUserFunc($user_id);
    $usermanagement->logActionFunc($_SESSION["user_id"], "Deactivate User", "Deactivated a user account.");

    header("Location: AdminDashboardPage.php");
    exit;
}

if (isset($_POST["updateMaterialCopiesBtn"])) {
    $material_id = $_POST["material_id"];
    $total_copies = $_POST["total_copies"];
    $available_copies = $_POST["available_copies"];

    if ($available_copies <= $total_copies) {
        $usermanagement->updateMaterialCopiesFunc($material_id, $total_copies, $available_copies);
        $usermanagement->logActionFunc($_SESSION["user_id"], "Update Material Copies", "Updated material copy count.");
    }

    header("Location: AdminDashboardPage.php");
    exit;
}

if (isset($_POST["toggleMaterialStatusBtn"])) {
    $material_id = $_POST["material_id"];

    $usermanagement->toggleMaterialStatusFunc($material_id);
    $usermanagement->logActionFunc($_SESSION["user_id"], "Update Material Status", "Toggled material status.");

    header("Location: AdminDashboardPage.php");
    exit;
}

if (isset($_POST["deleteMaterialBtn"])) {
    $material_id = $_POST["material_id"];

    $usermanagement->deactivateMaterialFunc($material_id);
    $usermanagement->logActionFunc($_SESSION["user_id"], "Deactivate Material", "Deactivated a library material.");

    header("Location: AdminDashboardPage.php");
    exit;
}

if (isset($_POST["markReturnedBtn"])) {
    $borrow_id = $_POST["borrow_id"];
    $material_id = $_POST["material_id"];

    $usermanagement->returnMaterialFunc($borrow_id, $material_id);
    $usermanagement->logActionFunc($_SESSION["user_id"], "Return Material", "Marked borrowed material as returned.");

    header("Location: AdminDashboardPage.php");
    exit;
}

if (isset($_POST["approveReservationBtn"])) {
    $reservation_id = $_POST["reservation_id"];

    $usermanagement->approveReservationFunc($reservation_id);
    $usermanagement->logActionFunc($_SESSION["user_id"], "Approve Reservation", "Approved a reservation request.");

    header("Location: AdminDashboardPage.php");
    exit;
}

if (isset($_POST["cancelReservationBtn"])) {
    $reservation_id = $_POST["reservation_id"];

    $usermanagement->cancelReservationFunc($reservation_id);
    $usermanagement->logActionFunc($_SESSION["user_id"], "Cancel Reservation", "Cancelled a reservation request.");

    header("Location: AdminDashboardPage.php");
    exit;
}

if (isset($_POST["sendSupportReplyBtn"])) {
    $message = trim($_POST["support_message"]);
    $message_type = $_POST["message_type"];

    if ($message != "") {
        $usermanagement->addSupportMessageFunc($_SESSION["user_id"], "admin", $message_type, $message);
        $usermanagement->logActionFunc($_SESSION["user_id"], "Support Reply", "Sent a support inbox reply.");
    }

    header("Location: AdminDashboardPage.php");
    exit;
}


// FETCH DATABASE DATA
$users = $usermanagement->getUserFunc();
$totalUsers = $usermanagement->totalUsersFunc();
$logs = $usermanagement->getLogsFunc();
$totalMaterials = $usermanagement->totalMaterialsFunc();
$materials = $usermanagement->getMaterialsFunc();
$materialBreakdown = $usermanagement->materialBreakdownFunc();
$activeBorrows = $usermanagement->activeBorrowsFunc();
$returnedItems = $usermanagement->returnedItemsFunc();
$reservedItems = $usermanagement->reservedItemsFunc();
$weeklyUsage = $usermanagement->weeklyUsageFunc();
$borrowRecords = $usermanagement->getBorrowRecordsFunc();
$reservationRecords = $usermanagement->getReservationRecordsFunc();
$supportMessages = $usermanagement->getSupportMessagesFunc();

// SESSION DATA
$first_name = $_SESSION["first_name"] ?? "";
$last_name = $_SESSION["last_name"] ?? "";
$full_name = trim($first_name . " " . $last_name);
$role = $_SESSION["role"] ?? "admin";
$avatarInitials = strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1));

// TOTAL COUNTS
$totalUserCount = $totalUsers["totalUsers"] ?? 0;
$totalLogsCount = is_array($logs) ? count($logs) : 0;
$totalMaterialsCount = $totalMaterials["totalMaterials"] ?? 0;
$activeBorrowsCount = $activeBorrows["activeBorrows"] ?? 0;
$returnedItemsCount = $returnedItems["returnedItems"] ?? 0;
$reservedItemsCount = $reservedItems["reservedItems"] ?? 0;

// USER ROLE COUNTS
$studentCount = 0;
$facultyCount = 0;
$adminCount = 0;

if (!empty($users)) {

    foreach ($users as $user) {

        if ($user["role"] == "student") {
            $studentCount++;
        }

        else if ($user["role"] == "faculty") {
            $facultyCount++;
        }

        else if ($user["role"] == "admin") {
            $adminCount++;
        }
    }
}

// MATERIAL BREAKDOWN
$printBooksCount = 0;
$electronicCount = 0;
$journalsCount = 0;

if (!empty($materialBreakdown)) {

    foreach ($materialBreakdown as $material) {

        $type = strtolower(trim($material["material_type"]));

        if ($type == "print") {
            $printBooksCount = $material["total"];
        }
        else if ($type == "electronic" || $type == "electronics") {
            $electronicCount = $material["total"];
        }
        else if ($type == "journal" || $type == "journals") {
            $journalsCount = $material["total"];
        }
    }
}

// COLLECTION PERCENTAGES
$totalCollectionCount = $printBooksCount + $electronicCount + $journalsCount;
$printBooksPercent = $totalCollectionCount > 0 ? ($printBooksCount / $totalCollectionCount) * 100 : 0;
$electronicPercent = $totalCollectionCount > 0 ? ($electronicCount / $totalCollectionCount) * 100 : 0;
$journalsPercent = $totalCollectionCount > 0 ? ($journalsCount / $totalCollectionCount) * 100 : 0;

// WEEKLY USAGE
$mondayUsage = 0;
$tuesdayUsage = 0;
$wednesdayUsage = 0;
$thursdayUsage = 0;
$fridayUsage = 0;
$saturdayUsage = 0;
$sundayUsage = 0;


if (!empty($weeklyUsage)) {

    foreach ($weeklyUsage as $usage) {

        if ($usage["dayName"] == "Monday") {
            $mondayUsage = $usage["totalUsage"];
        }

        else if ($usage["dayName"] == "Tuesday") {
            $tuesdayUsage = $usage["totalUsage"];
        }

        else if ($usage["dayName"] == "Wednesday") {
            $wednesdayUsage = $usage["totalUsage"];
        }

        else if ($usage["dayName"] == "Thursday") {
            $thursdayUsage = $usage["totalUsage"];
        }

        else if ($usage["dayName"] == "Friday") {
            $fridayUsage = $usage["totalUsage"];
        }

        else if ($usage["dayName"] == "Saturday") {
            $saturdayUsage = $usage["totalUsage"];
        }

        else if ($usage["dayName"] == "Sunday") {
            $sundayUsage = $usage["totalUsage"];
        }
    }
}

// WEEKLY PERCENTAGES
$maxUsage = max($mondayUsage, $tuesdayUsage, $wednesdayUsage, $thursdayUsage, $fridayUsage, $saturdayUsage, $sundayUsage, 10);
$mondayPercent = ($mondayUsage / $maxUsage) * 100;
$tuesdayPercent = ($tuesdayUsage / $maxUsage) * 100;
$wednesdayPercent = ($wednesdayUsage / $maxUsage) * 100;
$thursdayPercent = ($thursdayUsage / $maxUsage) * 100;
$fridayPercent = ($fridayUsage / $maxUsage) * 100;
$saturdayPercent = ($saturdayUsage / $maxUsage) * 100;
$sundayPercent = ($sundayUsage / $maxUsage) * 100;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard Page</title>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,400&family=Sora:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">

<body>

<div id="page-dashboard">

    <aside class="sidebar">
        <div class="sb-top">
            <div class="sb-logo">
                <div class="l-tag">UNIVERSITY OF SANTO TOMAS</div>
                <h2>CICS <em>E-Library</em></h2>
            </div>

            <div class="sb-user">
                <div class="sb-avatar"><?php echo $avatarInitials; ?></div>
                <div>
                    <div class="sb-uname">ADMIN</div>
                    <div class="sb-urole">System Administrator</div>
                </div>
            </div>
        </div>

            <div class="sb-nav">

                <div class="sb-section">Main</div>

                <button class="nav-btn active" onclick="showPanel('p-overview', this)" aria-current="page">
                    <span class="n-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <line x1="18" y1="20" x2="18" y2="10"/>
                            <line x1="12" y1="20" x2="12" y2="4"/>
                            <line x1="6" y1="20" x2="6" y2="14"/>
                        </svg>
                    </span>
                    <span>Overview</span>
                </button>

                <div class="sb-section">Library Operations</div>

                <button class="nav-btn" onclick="showPanel('p-materials', this)">
                    <span class="n-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                            <path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5z"/>
                        </svg>
                    </span>
                    <span>Materials</span>
                    <span class="n-badge"><?php echo $totalMaterialsCount; ?></span>
                </button>

                <button class="nav-btn" onclick="showPanel('p-circulation', this)">
                    <span class="n-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M7 7h10v10H7z"/>
                            <path d="M5 3h14a2 2 0 0 1 2 2v14"/>
                            <path d="M3 5v14a2 2 0 0 0 2 2h14"/>
                        </svg>
                    </span>
                    <span>Borrowed & Reservations</span>
                    <span class="n-badge"><?php echo $activeBorrowsCount + $reservedItemsCount; ?></span>
                </button>

                <div class="sb-section">User Administration</div>

                <button class="nav-btn" onclick="showPanel('p-users', this)">
                    <span class="n-icon">
                        <svg viewBox="0 0 24 24">
                            <circle cx="9" cy="7" r="3"/>
                            <path d="M2 20c0-3 4-5 7-5s7 2 7 5"/>
                            <circle cx="17" cy="8" r="2"/>
                            <path d="M17 14c2 0 5 1 5 3"/>
                        </svg>
                    </span>
                    <span>User Accounts</span>
                    <span class="n-badge"><?php echo $totalUserCount; ?></span>
                </button>

                <div class="sb-section">Communications</div>

                <button class="nav-btn" onclick="showPanel('p-support', this)">
                    <span class="n-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/>
                        </svg>
                    </span>
                    <span>Support Inbox</span>
                </button>

                <div class="sb-section">System Monitoring</div>

                <button class="nav-btn" onclick="showPanel('p-logs', this)">
                    <span class="n-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M3 12h18"/>
                            <path d="M3 6h18"/>
                            <path d="M3 18h18"/>
                        </svg>
                    </span>
                    <span>Activity Logs</span>
                    <span class="n-badge"><?php echo $totalLogsCount; ?></span>
                </button>

            </div>

        <div class="sb-bottom">
            <button class="btn-logout" onclick="doLogout()" aria-label="Sign out">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                Sign Out
            </button>
        </div>
    </aside>

    <main class="main-area">
  <div class="main-topbar">
    <div class="topbar-title" id="tb-title">Overview</div>

    <div class="topbar-right">
        <input type="text" class="topbar-search" placeholder="Quick search...">

        <div class="notif-wrap">
            <button class="topbar-notif" onclick="toggleNotifications()" aria-label="Notifications">
                <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>

                <span class="notif-count" id="notifCount">0</span>
            </button>

            <div class="notif-panel" id="notifPanel">
                <div class="notif-head">
                    <strong>Notifications</strong>
                    <button type="button" onclick="markNotificationsRead()">Mark all as read</button>
                </div>

                <div class="notif-list" id="notifList">
                    <div class="notif-empty">No notifications yet.</div>
                </div>
            </div>
        </div>
    </div>
</div>

        <section id="p-overview" class="panel active">
            <div class="sec-head">
                <div>
                    <h3>Welcome, <?php echo htmlspecialchars($first_name); ?></h3>
                    <p>Manage user accounts and monitor activity in the system.</p>
                </div>
            </div>

            <div class="stat-grid">
                <div class="stat-card">
                    <div class="s-label">Total Users</div>
                    <div class="s-value"><?php echo $totalUserCount; ?></div>
                    <div class="s-sub">Registered accounts</div>
                </div>

                <div class="stat-card">
                    <div class="s-label">Total Materials</div>
                    <div class="s-value"><?php echo $totalMaterialsCount; ?></div>
                    <div class="s-sub">Library collection count</div>
                </div>

                <div class="stat-card">
                    <div class="s-label">Active Borrows</div>
                    <div class="s-value"><?php echo $activeBorrowsCount; ?></div>
                    <div class="s-sub">Currently borrowed items</div>
                </div>

                <div class="stat-card">
                    <div class="s-label">Returned Items</div>
                    <div class="s-value"><?php echo $returnedItemsCount; ?></div>
                    <div class="s-sub">Successfully returned materials</div>
                </div>

                <div class="stat-card">
                    <div class="s-label">Reserved Items</div>
                    <div class="s-value"><?php echo $reservedItemsCount; ?></div>
                    <div class="s-sub">Pending material reservations</div>
                </div>
            </div>

            <div class="overview-grid">
                <div>
                    <div class="sec-head">
                        <div>
                            <h3>Collection Breakdown</h3>
                            <p>Overview of library collection categories.</p>
                        </div>
                    </div>

                    <div class="quick-stats">
                        <div class="qs-card chart-card-sm">
                            <div class="qs-title">Collection Breakdown</div>
                            <div class="chart-holder">
                                <canvas id="collectionChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="sec-head">
                        <div>
                            <h3>User Breakdown</h3>
                            <p>Summary of registered users by role.</p>
                        </div>
                    </div>

                    <div class="quick-stats">
                        <div class="qs-card chart-card-sm">
                            <div class="qs-title">User Breakdown</div>
                            <div class="chart-holder">
                                <canvas id="userChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sec-head" style="margin-top:35px;">
                <div>
                    <h3>Book Usage Trends</h3>
                    <p>Weekly activity based on users who accessed or used library materials.</p>
                </div>
            </div>

            <div class="quick-stats">
                <div class="qs-card chart-card-wide">
                    <div class="qs-title">Monday - Sunday Usage Overview</div>
                    <div class="chart-holder">
                        <canvas id="usageChart"></canvas>
                    </div>
                </div>
            </div>
        </section>

        <section id="p-users" class="panel">
            <div class="sec-head">
                <div>
                    <h3>Registered Users</h3>
                    <p>Manage registered student, faculty, and admin accounts.</p>
                </div>
            </div>

            <div class="admin-toolbar">
                <input type="search" id="adminUserSearch" placeholder="Search by name, UST ID, or email..." oninput="filterAdminUsers()">

                <select id="adminRoleFilter" onchange="filterAdminUsers()">
                    <option value="">All Roles</option>
                    <option value="student">Student</option>
                    <option value="faculty">Faculty</option>
                    <option value="admin">Admin</option>
                </select>

                <select id="adminStatusFilter" onchange="filterAdminUsers()">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="table-card">
                <table id="adminUsersTable">
                    <thead>
                        <tr>
                            <th class="sortable" onclick="sortUsersTable(0)">User</th>
                            <th class="sortable" onclick="sortUsersTable(1)">UST ID</th>
                            <th class="sortable" onclick="sortUsersTable(2)">Academic Info</th>
                            <th class="sortable" onclick="sortUsersTable(3)">Account Status</th>
                            <th class="sortable" onclick="sortUsersTable(4)">Date Joined</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($users)) : ?>
                            <?php $count = 1; ?>
                            <?php foreach ($users as $user) : ?>
                                <?php 
                                    $userStatus = !empty($user["is_active"]) ? "active" : "inactive";
                                    $userRole = strtolower($user["role"]);
                                ?>
                                <tr data-role="<?php echo htmlspecialchars($userRole); ?>" data-status="<?php echo htmlspecialchars($userStatus); ?>">
                                    <td>
                                        <div class="user-cell">
                                            <div class="user-name">
                                                <?php echo htmlspecialchars($user["first_name"] . " " . $user["last_name"]); ?>
                                            </div>
                                            <div class="user-email">
                                                <?php echo htmlspecialchars($user["email"]); ?>
                                            </div>
                                            <span class="user-role-badge">
                                                <?php echo htmlspecialchars(ucfirst($user["role"])); ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="ust-id-text">
                                            <?php echo htmlspecialchars($user["ust_id"]); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="academic-cell">
                                            <div>
                                                <?php echo !empty($user["course"]) ? htmlspecialchars($user["course"]) : "-"; ?>
                                            </div>

                                            <span>
                                                <?php echo !empty($user["year_level"]) ? htmlspecialchars($user["year_level"]) : "-"; ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-pill <?php echo $userStatus == "active" ? "pill-active" : "pill-inactive"; ?>">
                                            <?php echo ucfirst($userStatus); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="joined-date">
                                            <?php echo !empty($user["created_at"]) ? htmlspecialchars(date("M d, Y", strtotime($user["created_at"]))) : "-"; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button"
                                                class="btn-mini primary"
                                                onclick='openUserEditModal(
                                                    <?php echo json_encode($user["user_id"]); ?>,
                                                    <?php echo json_encode($user["first_name"] . " " . $user["last_name"]); ?>,
                                                    <?php echo json_encode($user["role"]); ?>,
                                                    <?php echo json_encode($userStatus); ?>
                                                )'>
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="10">No Data Found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="table-meta">Admin can review, filter, and manage user accounts.</div>
        </section>

        <section id="p-materials" class="panel">
            <div class="sec-head">
                <div>
                    <h3>Manage Materials</h3>
                    <p>Add, update, and organize library materials in the system.</p>
                </div>
            </div>

            <form class="form-card" method="POST" action="" onsubmit="return validateMaterialForm(this)">
                <div class="form-grid">
                    <div class="input-group">
                        <label>Book Title</label>
                        <input type="text" name="title" placeholder="Enter book title" maxlength="100" required>
                    </div>

                    <div class="input-group">
                        <label>Author</label>
                        <input type="text" name="author" placeholder="Enter author name" maxlength="80" required>
                    </div>

                    <div class="input-group">
                        <label>Genre / Category</label>
                        <input type="text" name="category" placeholder="Example: Programming" maxlength="60" required>
                    </div>

                    <div class="input-group">
                        <label>Material Type</label>
                        <select name="material_type" required>
                            <option value="Print">Print</option>
                            <option value="Electronic">Electronic</option>
                            <option value="Journals">Journals</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Total Copies</label>
                        <input type="number" name="total_copies" min="0" placeholder="0" required>
                    </div>

                    <div class="input-group">
                        <label>Available Copies</label>
                        <input type="number" name="available_copies" min="0" placeholder="0" required>
                    </div>
                </div>

                <button class="form-btn" type="submit" name="addMaterialBtn">Add Material</button>
            </form>

            <div class="admin-toolbar" style="margin-top: 24px;">
                <input type="search" id="adminMaterialSearch" placeholder="Search by title, author, category..." oninput="filterAdminMaterials()">

                <select id="adminMaterialTypeFilter" onchange="filterAdminMaterials()">
                    <option value="">All Types</option>
                    <option value="print">Print</option>
                    <option value="electronic">Electronic</option>
                    <option value="journals">Journals</option>
                </select>

                <select id="adminMaterialStatusFilter" onchange="filterAdminMaterials()">
                    <option value="">All Status</option>
                    <option value="available">Available</option>
                    <option value="unavailable">Unavailable</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="table-card" style="margin-top: 18px;">
                <table id="adminMaterialsTable">
                    <thead>
                        <tr>
                            <th onclick="sortTable(0)">#</th>
                            <th onclick="sortTable(1)">Material</th>
                            <th onclick="sortTable(2)">Category</th>
                            <th>Copies</th>
                            <th onclick="sortTable(4)">Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($materials)) : ?>
                            <?php $count = 1; ?>
                            <?php foreach ($materials as $material) : ?>
                                <?php 
                                    $matType = strtolower($material["material_type"]);
                                    $matStatus = strtolower($material["status"]);
                                    $matStatusClass = ($matStatus == "available" || $matStatus == "active") ? "pill-active" : "pill-inactive";
                                ?>
                                <tr data-type="<?php echo htmlspecialchars($matType); ?>" data-status="<?php echo htmlspecialchars($matStatus); ?>">
                                    <td><?php echo $count++; ?></td>
                                    <td>
                                        <div class="material-cell">
                                            <div class="material-title">
                                                <?php echo htmlspecialchars($material["title"]); ?>
                                            </div>

                                            <div class="material-author">
                                                by <?php echo htmlspecialchars($material["author"]); ?>
                                            </div>

                                            <span class="material-type-badge">
                                                <?php echo htmlspecialchars($material["material_type"]); ?>
                                            </span>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="category-cell">
                                            <?php echo htmlspecialchars($material["category"]); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <form method="POST" class="copies-form" onsubmit="return validateCopiesForm(this)">
                                            <input type="hidden" name="material_id" value="<?php echo $material["material_id"]; ?>">

                                            <div class="copies-grid">
                                                <div>
                                                    <label>Total</label>
                                                    <input type="number" name="total_copies" value="<?php echo $material["total_copies"]; ?>" min="0">
                                                </div>

                                                <div>
                                                    <label>Available</label>
                                                    <input type="number" name="available_copies" value="<?php echo $material["available_copies"]; ?>" min="0">
                                                </div>
                                            </div>

                                            <button type="submit" name="updateMaterialCopiesBtn" class="btn-mini primary material-save-btn">
                                                Save Copies
                                            </button>
                                        </form>
                                    </td>

                                    <td>
                                        <span class="status-pill <?php echo $matStatusClass; ?>">
                                            <?php echo htmlspecialchars($material["status"]); ?>
                                        </span>
                                    </td>

                                    <td>
                                        <form method="POST" class="material-action-form">
                                            <input type="hidden" name="material_id" value="<?php echo $material["material_id"]; ?>">

                                            <button type="submit" name="toggleMaterialStatusBtn" class="btn-mini neutral">
                                                Change Status
                                            </button>

                                            <button type="submit" name="deleteMaterialBtn" class="btn-mini danger" onclick="return confirm('Deactivate this material?')">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6">No materials found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

            <section id="p-circulation" class="panel">
                <div class="sec-head">
                    <div>
                        <h3>Borrow & Reservation Management</h3>
                        <p>Monitor borrowed, returned, and reserved library materials.</p>
                    </div>
                </div>

                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="s-label">Active Borrows</div>
                        <div class="s-value"><?php echo $activeBorrowsCount; ?></div>
                        <div class="s-sub">Currently borrowed materials</div>
                    </div>

                    <div class="stat-card">
                        <div class="s-label">Returned Items</div>
                        <div class="s-value"><?php echo $returnedItemsCount; ?></div>
                        <div class="s-sub">Completed returns</div>
                    </div>

                    <div class="stat-card">
                        <div class="s-label">Reservations</div>
                        <div class="s-value"><?php echo $reservedItemsCount; ?></div>
                        <div class="s-sub">Pending reservation requests</div>
                    </div>
                </div>

                <div class="circulation-stack">

                    <div class="table-card circulation-card">
                        <div class="table-head">
                            <div>
                                <div class="table-head-title">Borrowed Materials</div>
                                <p class="table-head-sub">Track borrowed items and mark returns.</p>
                            </div>
                        </div>

                        <table id="circulationTable" class="circulation-table">
                            <thead>
                                <tr>
                                    <th class="sortable" onclick="sortCirculationTable(0)">Borrower</th>
                                    <th class="sortable" onclick="sortCirculationTable(1)">Material</th>
                                    <th class="sortable" onclick="sortCirculationTable(2)">Due Date</th>
                                    <th class="sortable" onclick="sortCirculationTable(3)">Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if (!empty($borrowRecords)) : ?>
                                    <?php foreach ($borrowRecords as $borrow) : ?>
                                        <?php 
                                            $borrowStatus = strtolower($borrow["status"]);
                                            $borrowStatusClass = $borrowStatus == "borrowed" ? "pill-borrowed" : "pill-returned";
                                        ?>

                                        <tr>
                                            <td>
                                                <div class="borrow-user-cell">
                                                    <div class="borrow-user-name">
                                                        <?php echo htmlspecialchars($borrow["first_name"] . " " . $borrow["last_name"]); ?>
                                                    </div>
                                                    <span>Borrower</span>
                                                </div>
                                            </td>

                                            <td>
                                                <div class="borrow-material-cell">
                                                    <?php echo htmlspecialchars($borrow["title"]); ?>
                                                </div>
                                            </td>

                                            <td>
                                                <span class="date-pill">
                                                    <?php echo htmlspecialchars(date("M d, Y", strtotime($borrow["due_date"]))); ?>
                                                </span>
                                            </td>

                                            <td>
                                                <span class="status-pill <?php echo $borrowStatusClass; ?>">
                                                    <?php echo htmlspecialchars($borrow["status"]); ?>
                                                </span>
                                            </td>

                                            <td>
                                                <?php if ($borrowStatus == "borrowed") : ?>
                                                    <form method="POST" class="single-action-form">
                                                        <input type="hidden" name="borrow_id" value="<?php echo $borrow["borrow_id"]; ?>">
                                                        <input type="hidden" name="material_id" value="<?php echo $borrow["material_id"]; ?>">

                                                        <button type="submit" name="markReturnedBtn" class="btn-mini primary">
                                                            Mark Returned
                                                        </button>
                                                    </form>
                                                <?php else : ?>
                                                    <span class="no-action">No action</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="5">No borrow records found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="table-card circulation-card">
                        <div class="table-head">
                            <div>
                                <div class="table-head-title">Reservation Requests</div>
                                <p class="table-head-sub">Review pending material reservations.</p>
                            </div>
                        </div>

                        <table class="circulation-table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Material</th>
                                    <th>Date Reserved</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if (!empty($reservationRecords)) : ?>
                                    <?php foreach ($reservationRecords as $reserve) : ?>
                                        <?php 
                                            $reserveStatus = strtolower($reserve["status"]);

                                            if ($reserveStatus == "pending") {
                                                $reserveStatusClass = "pill-pending";
                                            } 
                                            else if ($reserveStatus == "approved") {
                                                $reserveStatusClass = "pill-approved";
                                            } 
                                            else {
                                                $reserveStatusClass = "pill-cancelled";
                                            }
                                        ?>

                                        <tr>
                                            <td>
                                                <div class="borrow-user-cell">
                                                    <div class="borrow-user-name">
                                                        <?php echo htmlspecialchars($reserve["first_name"] . " " . $reserve["last_name"]); ?>
                                                    </div>
                                                    <span>Requester</span>
                                                </div>
                                            </td>

                                            <td>
                                                <div class="borrow-material-cell">
                                                    <?php echo htmlspecialchars($reserve["title"]); ?>
                                                </div>
                                            </td>

                                            <td>
                                                <span class="date-pill">
                                                    <?php echo !empty($reserve["reserved_at"]) ? htmlspecialchars(date("M d, Y h:i A", strtotime($reserve["reserved_at"]))) : "-"; ?>
                                                </span>
                                            </td>

                                            <td>
                                                <span class="status-pill <?php echo $reserveStatusClass; ?>">
                                                    <?php echo htmlspecialchars($reserve["status"]); ?>
                                                </span>
                                            </td>

                                            <td>
                                                <?php if ($reserveStatus == "pending") : ?>
                                                    <form method="POST" class="dual-action-form">
                                                        <input type="hidden" name="reservation_id" value="<?php echo $reserve["reservation_id"]; ?>">

                                                        <button type="submit" name="approveReservationBtn" class="btn-mini primary">
                                                            Approve
                                                        </button>

                                                        <button type="submit" name="cancelReservationBtn" class="btn-mini danger">
                                                            Cancel
                                                        </button>
                                                    </form>
                                                <?php else : ?>
                                                    <span class="no-action">No action</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="5">No reservation records found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </section>

        <section id="p-support" class="panel">
            <div class="sec-head">
                <div>
                    <h3>Support Inbox</h3>
                    <p>View student messages and respond to library support concerns.</p>
                </div>
            </div>

            <div class="support-shell">
                <div class="support-list">
                    <button type="button" class="support-thread active" onclick="openSupportThread('support', this)">
                        <strong>CICS E-Library Support</strong>
                        <span>Borrowing, reservations, and account concerns</span>
                    </button>

                    <button type="button" class="support-thread" onclick="openSupportThread('student', this)">
                        <strong>Student Chat Monitor</strong>
                        <span>General student discussions and resource questions</span>
                    </button>

                    <button type="button" class="support-thread" onclick="openSupportThread('notice', this)">
                        <strong>Library Announcement</strong>
                        <span>Send notice or reminder to users</span>
                    </button>
                </div>

                <div class="support-chat">
                    <div class="support-chat-head">
                        <h4 id="supportThreadTitle">CICS E-Library Support</h4>
                        <p id="supportThreadDesc">Respond to student support questions.</p>
                    </div>

                    <div class="support-messages" id="supportAdminMessages">
                        <?php if (!empty($supportMessages)) : ?>
                            <?php foreach ($supportMessages as $msg) : ?>
                                <?php 
                                    $bubbleClass = $msg["sender_role"] == "admin" ? "me" : "other";
                                    $senderName = $msg["sender_role"] == "admin" 
                                        ? "Admin" 
                                        : trim(($msg["first_name"] ?? "Student") . " " . ($msg["last_name"] ?? ""));
                                ?>

                                <div class="support-bubble <?php echo $bubbleClass; ?>">
                                    <strong><?php echo htmlspecialchars($senderName); ?></strong>
                                    <span><?php echo htmlspecialchars($msg["message"]); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="support-bubble other">
                                <strong>System</strong>
                                <span>No support messages yet.</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <form method="POST" class="support-reply-row">
                        <input type="hidden" name="message_type" id="adminMessageType" value="support">
                        <input type="text" name="support_message" placeholder="Type admin reply..." required>
                        <button type="submit" name="sendSupportReplyBtn">Send</button>
                    </form>
                </div>
            </div>
        </section>

        <section id="p-logs" class="panel">
            <div class="sec-head">
                <div>
                    <h3>Activity Logs</h3>
                    <p>Recent activity in the system.</p>
                </div>
            </div>

            <div class="admin-toolbar">
                <input type="search" id="adminLogSearch" placeholder="Search logs..." oninput="filterAdminLogs()">
            </div>

            <div class="table-card">
                <table id="adminLogsTable">
                    <thead>
                        <tr>
                            <th class="sortable" onclick="sortLogsTable(0)">User</th>
                            <th class="sortable" onclick="sortLogsTable(1)">Action</th>
                            <th>Description</th>
                            <th class="sortable" onclick="sortLogsTable(3)">Date</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($logs)) : ?>
                            <?php foreach ($logs as $log) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($log["first_name"] . " " . $log["last_name"]); ?></td>
                                    <td><?php echo htmlspecialchars($log["action_type"]); ?></td>
                                    <td><?php echo htmlspecialchars($log["description"]); ?></td>
                                    <td><?php echo htmlspecialchars($log["created_at"]); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="4">No Data Found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="table-meta">This section is read from backend activity log records.</div>
        </section>
    </main>
</div>

        <div class="modal-bg" id="userEditModal">
            <div class="modal">
                <button type="button" class="modal-close" onclick="closeUserEditModal()">×</button>

                <h3>Edit User</h3>
                <p id="editUserName">Update user role and account status.</p>

                <form method="POST">
                    <input type="hidden" name="user_id" id="editUserId">

                    <div class="input-group">
                        <label>Role</label>
                        <select name="new_role" id="editUserRole" required>
                            <option value="student">Student</option>
                            <option value="faculty">Faculty</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Status</label>
                        <select name="new_status" id="editUserStatus" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" onclick="closeUserEditModal()">Cancel</button>
                        <button type="submit" name="updateUserBtn" class="btn-gold" style="width:auto; padding:10px 22px;">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

<div id="toast"></div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="../scripts/service.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
function showPanel(panelId, button) {
    document.querySelectorAll(".panel").forEach(function(panel) {
        panel.classList.remove("active");
    });

    document.querySelectorAll(".nav-btn").forEach(function(btn) {
        btn.classList.remove("active");
        btn.removeAttribute("aria-current");
    });

    const targetPanel = document.getElementById(panelId);

    if (targetPanel) {
        targetPanel.classList.add("active");
    }

    if (button) {
        button.classList.add("active");
        button.setAttribute("aria-current", "page");
    }

    const titleMap = {
        "p-overview": "Overview",
        "p-users": "User Accounts",
        "p-materials": "Materials",
        "p-circulation": "Borrowed & Reservations",
        "p-support": "Support Inbox",
        "p-logs": "Activity Logs"
    };

    const topTitle = document.getElementById("tb-title");

    if (topTitle && titleMap[panelId]) {
        topTitle.innerText = titleMap[panelId];
    }
}

function adminToast(message) {
    if (typeof toast === "function") {
        toast(message, "info");
    } else {
        alert(message);
    }
}

function filterAdminUsers() {
    const search = document.getElementById("adminUserSearch").value.toLowerCase();
    const role = document.getElementById("adminRoleFilter").value.toLowerCase();
    const status = document.getElementById("adminStatusFilter").value.toLowerCase();
    const rows = document.querySelectorAll("#adminUsersTable tbody tr");

    rows.forEach(function(row) {
        const text = row.innerText.toLowerCase();
        const rowRole = row.getAttribute("data-role") || "";
        const rowStatus = row.getAttribute("data-status") || "";

        const matchSearch = text.includes(search);
        const matchRole = role === "" || rowRole === role;
        const matchStatus = status === "" || rowStatus === status;

        row.style.display = matchSearch && matchRole && matchStatus ? "" : "none";
    });
}

function filterAdminMaterials() {
    const search = document.getElementById("adminMaterialSearch").value.toLowerCase();
    const type = document.getElementById("adminMaterialTypeFilter").value.toLowerCase();
    const status = document.getElementById("adminMaterialStatusFilter").value.toLowerCase();
    const rows = document.querySelectorAll("#adminMaterialsTable tbody tr");

    rows.forEach(function(row) {
        const text = row.innerText.toLowerCase();
        const rowType = row.getAttribute("data-type") || "";
        const rowStatus = row.getAttribute("data-status") || "";

        const matchSearch = text.includes(search);
        const matchType = type === "" || rowType === type;
        const matchStatus = status === "" || rowStatus === status;

        row.style.display = matchSearch && matchType && matchStatus ? "" : "none";
    });
}

function filterAdminLogs() {
    const search = document.getElementById("adminLogSearch").value.toLowerCase();
    const rows = document.querySelectorAll("#adminLogsTable tbody tr");

    rows.forEach(function(row) {
        row.style.display = row.innerText.toLowerCase().includes(search) ? "" : "none";
    });
}

function validateMaterialForm(form) {
    const total = Number(form.total_copies.value);
    const available = Number(form.available_copies.value);

    if (available > total) {
        adminToast("Available copies cannot be greater than total copies.");
        return false;
    }

    return true;
}

function validateCopiesForm(form) {
    const total = Number(form.total_copies.value);
    const available = Number(form.available_copies.value);

    if (available > total) {
        adminToast("Available copies cannot be greater than total copies.");
        return false;
    }

    return true;
}

function openSupportThread(type, button) {
    document.querySelectorAll(".support-thread").forEach(function(thread) {
        thread.classList.remove("active");
    });

    button.classList.add("active");

    const title = document.getElementById("supportThreadTitle");
    const desc = document.getElementById("supportThreadDesc");
    const messageType = document.getElementById("adminMessageType");

    if (type === "support") {
        title.innerText = "CICS E-Library Support";
        desc.innerText = "Respond to student support questions.";
        messageType.value = "support";
    }

    if (type === "student") {
        title.innerText = "Student Chat Monitor";
        desc.innerText = "Monitor general student discussions and resource-related questions.";
        messageType.value = "student";
    }

    if (type === "notice") {
        title.innerText = "Library Announcement";
        desc.innerText = "Send a notice or reminder to library users.";
        messageType.value = "announcement";
    }
}

if (typeof Chart !== "undefined") {
    const collectionCanvas = document.getElementById("collectionChart");
    const userCanvas = document.getElementById("userChart");
    const usageCanvas = document.getElementById("usageChart");

    if (collectionCanvas) {
        new Chart(collectionCanvas, {
            type: "doughnut",
            data: {
                labels: ["Print Books", "Electronic", "Journals"],
                datasets: [{
                    label: "Collection Breakdown",
                    data: [
                        <?php echo $printBooksCount; ?>,
                        <?php echo $electronicCount; ?>,
                        <?php echo $journalsCount; ?>
                    ],
                    backgroundColor: ["#c9a34d", "#3b82c4", "#39b8a5"],
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "right",
                        labels: {
                            color: "#f0ead6",
                            boxWidth: 14,
                            padding: 12,
                            font: {
                                family: "Sora",
                                size: 11,
                                weight: "500"
                            }
                        }
                    }
                },
                cutout: "65%"
            }
        });
    }

if (userCanvas) {

    const labels = ["Students", "Faculty", "Admin"];

    const data = {
        labels: labels,
        datasets: [{
            label: "User Breakdown",
            data: [
                <?php echo $studentCount; ?>,
                <?php echo $facultyCount; ?>,
                <?php echo $adminCount; ?>
            ],
            fill: false,
            borderColor: "rgb(201, 163, 77)",
            backgroundColor: "rgba(201, 163, 77, 0.25)",
            tension: 0.1,
            pointBackgroundColor: "rgb(201, 163, 77)",
            pointBorderColor: "#ffffff",
            pointRadius: 5,
            pointHoverRadius: 7
        }]
    };

    new Chart(userCanvas, {
        type: "line",
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,

            plugins: {
                legend: {
                    display: true,
                    labels: {
                        color: "#f0ead6",
                        font: {
                            family: "Sora",
                            size: 11
                        }
                    }
                }
            },

            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: "#b8b1a1",
                        font: {
                            family: "Sora",
                            size: 10
                        }
                    },
                    grid: {
                        color: "rgba(255,255,255,0.05)"
                    }
                },
                x: {
                    ticks: {
                        color: "#f0ead6",
                        font: {
                            family: "Sora",
                            size: 10
                        }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

    if (usageCanvas) {
        new Chart(usageCanvas, {
            type: "bar",
            data: {
                labels: ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
                datasets: [{
                    label: "Book Usage Trends",
                    data: [
                        <?php echo $mondayUsage; ?>,
                        <?php echo $tuesdayUsage; ?>,
                        <?php echo $wednesdayUsage; ?>,
                        <?php echo $thursdayUsage; ?>,
                        <?php echo $fridayUsage; ?>,
                        <?php echo $saturdayUsage; ?>,
                        <?php echo $sundayUsage; ?>
                    ],
                    backgroundColor: [
                        "rgba(201, 163, 77, 0.75)",
                        "rgba(180, 120, 55, 0.75)",
                        "rgba(57, 184, 165, 0.75)",
                        "rgba(59, 130, 196, 0.75)",
                        "rgba(120, 98, 220, 0.75)",
                        "rgba(82, 190, 110, 0.75)",
                        "rgba(120, 120, 120, 0.75)"
                    ],
                    borderColor: ["#c9a34d", "#b47837", "#39b8a5", "#3b82c4", "#7862dc", "#52be6e", "#787878"],
                    borderWidth: 1,
                    borderRadius: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: "#b8b1a1",
                            font: {
                                family: "Sora",
                                size: 10
                            }
                        },
                        grid: {
                            color: "rgba(255,255,255,0.05)"
                        }
                    },
                    x: {
                        ticks: {
                            color: "#f0ead6",
                            font: {
                                family: "Sora",
                                size: 10
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
}

function openUserEditModal(userId, fullName, role, status) {
    document.getElementById("editUserId").value = userId;
    document.getElementById("editUserName").innerText = "Editing account of " + fullName;
    document.getElementById("editUserRole").value = role.toLowerCase();
    document.getElementById("editUserStatus").value = status.toLowerCase();

    document.getElementById("userEditModal").classList.add("open");
}

function closeUserEditModal() {
    document.getElementById("userEditModal").classList.remove("open");
}

let logsSortDirection = {};

function sortLogsTable(columnIndex) {
    const table = document.getElementById("adminLogsTable");
    const tbody = table.querySelector("tbody");
    const rows = Array.from(tbody.querySelectorAll("tr"));

    logsSortDirection[columnIndex] = !logsSortDirection[columnIndex];

    rows.sort((a, b) => {
        let cellA = a.children[columnIndex].innerText.trim().toLowerCase();
        let cellB = b.children[columnIndex].innerText.trim().toLowerCase();

        // Date column
        if (columnIndex === 3) {
            cellA = new Date(cellA);
            cellB = new Date(cellB);
        }

        if (cellA < cellB) {
            return logsSortDirection[columnIndex] ? -1 : 1;
        }

        if (cellA > cellB) {
            return logsSortDirection[columnIndex] ? 1 : -1;
        }

        return 0;
    });

    rows.forEach(row => tbody.appendChild(row));
}

</script>

</body>
</html>