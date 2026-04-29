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

$first_name = $_SESSION["first_name"] ?? "";
$last_name = $_SESSION["last_name"] ?? "";
$full_name = trim($first_name . " " . $last_name);
$role = $_SESSION["role"] ?? "admin";

$avatarInitials = strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1));
$totalUserCount = $totalUsers["totalUsers"] ?? 0;
$totalLogsCount = is_array($logs) ? count($logs) : 0;

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

$totalMaterialsCount = $totalMaterials["totalMaterials"] ?? 0;
$activeBorrowsCount = $activeBorrows["activeBorrows"] ?? 0;
$returnedItemsCount = $returnedItems["returnedItems"] ?? 0;
$reservedItemsCount = $reservedItems["reservedItems"] ?? 0;

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

$totalCollectionCount = $printBooksCount + $electronicCount + $journalsCount;

$printBooksPercent = $totalCollectionCount > 0 ? ($printBooksCount / $totalCollectionCount) * 100 : 0;
$electronicPercent = $totalCollectionCount > 0 ? ($electronicCount / $totalCollectionCount) * 100 : 0;
$journalsPercent = $totalCollectionCount > 0 ? ($journalsCount / $totalCollectionCount) * 100 : 0;

$weeklyUsage = $usermanagement->weeklyUsageFunc();

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
</head>
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
                        <div class="sb-uname"><?php echo htmlspecialchars($full_name); ?></div>
                        <div class="sb-urole"><?php echo htmlspecialchars($role); ?></div>
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
                    Overview
                </button>

                <button class="nav-btn" onclick="showPanel('p-users', this)">
                    <span class="n-icon">
                        <svg viewBox="0 0 24 24">
                            <circle cx="9" cy="7" r="3"/>
                            <path d="M2 20c0-3 4-5 7-5s7 2 7 5"/>
                            <circle cx="17" cy="8" r="2"/>
                            <path d="M17 14c2 0 5 1 5 3"/>
                        </svg>
                    </span>
                    <span>Registered Users</span>
                    <span class="n-badge"><?php echo $totalUserCount; ?></span>
                </button>

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
                    <button class="topbar-notif"
                        onclick="toast('No new notifications.', 'info')"
                        aria-label="Notifications">
                        <svg viewBox="0 0 24 24"
                             aria-hidden="true"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.8"
                             stroke-linecap="round"
                             stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                        <div class="notif-dot" aria-hidden="true"></div>
                    </button>
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
                            <div class="qs-card">
                                <div class="qs-title">Collection Breakdown</div>

                                <div class="progress-item">
                                    <div class="prog-head">
                                        <span>Print Books</span>
                                        <span><?php echo $printBooksCount; ?></span>
                                    </div>
                                    <div class="prog-bar">
                                        <div class="prog-fill" style="width: <?php echo $printBooksPercent; ?>%; background: var(--gold);"></div>
                                    </div>
                                </div>

                                <div class="progress-item">
                                    <div class="prog-head">
                                        <span>Electronic</span>
                                        <span><?php echo $electronicCount; ?></span>
                                    </div>
                                    <div class="prog-bar">
                                        <div class="prog-fill" style="width: <?php echo $electronicPercent; ?>%; background: var(--info);"></div>
                                    </div>
                                </div>

                                <div class="progress-item">
                                    <div class="prog-head">
                                        <span>Journals</span>
                                        <span><?php echo $journalsCount; ?></span>
                                    </div>
                                    <div class="prog-bar">
                                        <div class="prog-fill" style="width: <?php echo $journalsPercent; ?>%; background: var(--success);"></div>
                                    </div>
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
                            <div class="qs-card">
                                <div class="qs-title">User Breakdown</div>

                                <div class="progress-item">
                                    <div class="prog-head">
                                        <span>Students</span>
                                        <span><?php echo $studentCount; ?></span>
                                    </div>
                                    <div class="prog-bar">
                                        <div class="prog-fill" style="width: <?php echo $totalUserCount > 0 ? ($studentCount / $totalUserCount) * 100 : 0; ?>%; background: var(--gold);"></div>
                                    </div>
                                </div>

                                <div class="progress-item">
                                    <div class="prog-head">
                                        <span>Faculty</span>
                                        <span><?php echo $facultyCount; ?></span>
                                    </div>
                                    <div class="prog-bar">
                                        <div class="prog-fill" style="width: <?php echo $totalUserCount > 0 ? ($facultyCount / $totalUserCount) * 100 : 0; ?>%; background: var(--info);"></div>
                                    </div>
                                </div>

                                <div class="progress-item">
                                    <div class="prog-head">
                                        <span>Admins</span>
                                        <span><?php echo $adminCount; ?></span>
                                    </div>
                                    <div class="prog-bar">
                                        <div class="prog-fill" style="width: <?php echo $totalUserCount > 0 ? ($adminCount / $totalUserCount) * 100 : 0; ?>%; background: var(--success);"></div>
                                    </div>
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
                    <div class="qs-card">
                        <div class="qs-title">Monday - Sunday Usage Overview</div>

                        <div class="ust-chart-box">
                            <div class="ust-chart-bg"></div>

                            <div class="ust-chart-item" title="<?php echo $mondayUsage; ?> usage">
                                <div class="ust-chart-bar gold" style="height:<?php echo $mondayPercent; ?>%;"></div>
                                <span>Monday</span>
                            </div>

                            <div class="ust-chart-item" title="<?php echo $tuesdayUsage; ?> usage">
                                <div class="ust-chart-bar blue" style="height:<?php echo $tuesdayPercent; ?>%;"></div>
                                <span>Tuesday</span>
                            </div>

                            <div class="ust-chart-item" title="<?php echo $wednesdayUsage; ?> usage">
                                <div class="ust-chart-bar green" style="height:<?php echo $wednesdayPercent; ?>%;"></div>
                                <span>Wednesday</span>
                            </div>

                            <div class="ust-chart-item" title="<?php echo $thursdayUsage; ?> usage">
                                <div class="ust-chart-bar gold" style="height:<?php echo $thursdayPercent; ?>%;"></div>
                                <span>Thursday</span>
                            </div>

                            <div class="ust-chart-item" title="<?php echo $fridayUsage; ?> usage">
                                <div class="ust-chart-bar blue" style="height:<?php echo $fridayPercent; ?>%;"></div>
                                <span>Friday</span>
                            </div>

                            <div class="ust-chart-item" title="<?php echo $saturdayUsage; ?> usage">
                                <div class="ust-chart-bar green" style="height:<?php echo $saturdayPercent; ?>%;"></div>
                                <span>Saturday</span>
                            </div>

                            <div class="ust-chart-item" title="<?php echo $sundayUsage; ?> usage">
                                <div class="ust-chart-bar gray" style="height:<?php echo $sundayPercent; ?>%;"></div>
                                <span>Sunday</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="p-users" class="panel">
                <div class="sec-head">
                    <div>
                        <h3>Registered Users</h3>
                        <p>All registered users in the system.</p>
                    </div>
                </div>

                <div class="table-card">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>UST ID</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Course</th>
                                <th>Year</th>
                                <th>Status</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users)) : ?>
                                <?php $count = 1; ?>
                                <?php foreach ($users as $user) : ?>
                                    <tr>
                                        <td><?php echo $count++; ?></td>
                                        <td><?php echo htmlspecialchars($user["first_name"] . " " . $user["last_name"]); ?></td>
                                        <td><?php echo htmlspecialchars($user["ust_id"]); ?></td>
                                        <td><?php echo htmlspecialchars($user["email"]); ?></td>
                                        <td><?php echo htmlspecialchars(ucfirst($user["role"])); ?></td>
                                        <td><?php echo !empty($user["course"]) ? htmlspecialchars($user["course"]) : "-"; ?></td>
                                        <td><?php echo !empty($user["year_level"]) ? htmlspecialchars($user["year_level"]) : "-"; ?></td>
                                        <td><?php echo !empty($user["is_active"]) ? "Active" : "Inactive"; ?></td>
                                        <td><?php echo !empty($user["created_at"]) ? htmlspecialchars(date("Y-m-d", strtotime($user["created_at"]))) : "-"; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="9">No Data Found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="table-meta">Backend synced table for registered users. Action buttons are front-end functional for now.</div>
            </section>
                
            <section id="p-logs" class="panel">
                <div class="sec-head">
                    <div>
                        <h3>Activity Logs</h3>
                        <p>Recent activity in the system.</p>
                    </div>
                </div>

                <div class="table-card">
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Action</th>
                                <th>Description</th>
                                <th>Date</th>
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

            <section id="p-materials" class="panel">
                <div class="sec-head">
                    <div>
                        <h3>Manage Materials</h3>
                        <p>Add and organize library materials in the system.</p>
                    </div>
                </div>

                <form class="form-card" method="POST" action="">
                    <div class="form-grid">
                        <div class="input-group">
                            <label>Book Title</label>
                            <input type="text" name="title" placeholder="Enter book title" required>
                        </div>

                        <div class="input-group">
                            <label>Author</label>
                            <input type="text" name="author" placeholder="Enter author name" required>
                        </div>

                        <div class="input-group">
                            <label>Genre / Category</label>
                            <input type="text" name="category" placeholder="Example: Programming" required>
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

                <div class="table-card" style="margin-top: 24px;">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Genre</th>
                                <th>Type</th>
                                <th>Total Copies</th>
                                <th>Available</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($materials)) : ?>
                                <?php $count = 1; ?>
                                <?php foreach ($materials as $material) : ?>
                                    <tr>
                                        <td><?php echo $count++; ?></td>
                                        <td><?php echo htmlspecialchars($material["title"]); ?></td>
                                        <td><?php echo htmlspecialchars($material["author"]); ?></td>
                                        <td><?php echo htmlspecialchars($material["category"]); ?></td>
                                        <td><?php echo htmlspecialchars($material["material_type"]); ?></td>
                                        <td><?php echo htmlspecialchars($material["total_copies"]); ?></td>
                                        <td><?php echo htmlspecialchars($material["available_copies"]); ?></td>
                                        <td><?php echo htmlspecialchars($material["status"]); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="8">No Materials Found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
                                
    <div id="toast"></div>

    <script src="../scripts/service.js"></script>

    
</body>
</html>