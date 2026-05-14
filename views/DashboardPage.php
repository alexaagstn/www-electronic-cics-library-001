<?php
session_start();

require_once "../bl/UserManagement.php";
$usermanagement = new UserManagement();

$materials = $usermanagement->getMaterialsFunc();
$eresources = $usermanagement->getEResourcesFunc();

$isGuest = isset($_GET["guest"]);

if (!isset($_SESSION["user_id"]) && !$isGuest) {
    header("Location: LoginPage.php");
    exit;
}

if (!$isGuest && isset($_SESSION["role"]) && $_SESSION["role"] == "admin") {
    header("Location: AdminDashboardPage.php");
    exit;
}

if(isset($_POST["returnBtn"])){
    $usermanagement->returnMaterialFunc(
        $_POST["borrow_id"],
        $_POST["material_id"]
    );

    header("Location: DashboardPage.php");
    exit;
}

if ($isGuest) {
    $first_name = "Guest";
    $last_name = "";
    $full_name = "Guest";
    $role = "guest";
    $ust_id = "-";
    $email = "-";
}
else {
    $first_name = $_SESSION["first_name"] ?? "";
    $last_name  = $_SESSION["last_name"] ?? "";
    $full_name  = trim($first_name . " " . $last_name);
    $role       = $_SESSION["role"] ?? "";
    $ust_id     = $_SESSION["ust_id"] ?? "-";
    $email      = $_SESSION["email"] ?? "-";

if(isset($_POST["borrowBtn"])){
    $materialID = $_POST["materialID"];
    $usermanagement->borrowMaterialFunc($_SESSION["user_id"], $materialID);

    header("Location: DashboardPage.php");
    exit;
}

if(isset($_POST["reserveBtn"])){
    $materialID = $_POST["materialID"];
    $usermanagement->reserveMaterialFunc($_SESSION["user_id"], $materialID);

    header("Location: DashboardPage.php");
    exit;
}

}

$activeBorrows = $usermanagement->activeBorrowsFunc();
$overdueItems = $usermanagement->overdueItemsFunc();
$reservedItems = $usermanagement->reservedItemsFunc();

$activeBorrowsCount = $activeBorrows["activeBorrows"] ?? 0;
$overdueItemsCount = $overdueItems["overdueItems"] ?? 0;
$reservedItemsCount = $reservedItems["reservedItems"] ?? 0;


$materials = $usermanagement->getMaterialsFunc();
$myLoans = $usermanagement->getMyLoansFunc($_SESSION["user_id"]);
$myReservations = $usermanagement->getMyReservationsFunc($_SESSION["user_id"]);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Page</title>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,400&family=Sora:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js" defer></script>
</head>
<body>

<div id="page-dashboard" class="screen active">

    <aside class="sidebar">
        <div class="sb-top">

            <div class="sb-logo">
                <div class="l-tag">UNIVERSITY OF SANTO TOMAS</div>
                <h2>CICS <em>E-Library</em></h2>
            </div>

            <div class="sb-user">
                <div class="sb-avatar" id="sb-av" aria-hidden="true"></div>
                <div class="sb-user-info">
                    <div class="sb-uname" id="sb-name"><?= $full_name ?></div>
                    <div class="sb-urole" id="sb-role"><?= $role ?></div>
                </div>
            </div>

        </div>

        <nav class="sb-nav" aria-label="Main navigation">

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

            <button class="nav-btn" onclick="showPanel('p-opac', this)">
                <span class="n-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </span>
                OPAC Search
            </button>

            <button class="nav-btn" onclick="showRestrictedPanel('p-mybooks', this)">
                <span class="n-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                        <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
                        <line x1="9" y1="12" x2="15" y2="12"/>
                        <line x1="9" y1="16" x2="13" y2="16"/>
                    </svg>
                </span>
                My Loans
                <span class="n-badge" id="loan-badge" style="display: none;" aria-label="active loans">0</span>
            </button>

            <div class="sb-section">Resources</div>

            <button class="nav-btn" onclick="showRestrictedPanel('p-eres', this)">
                <span class="n-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                </span>
                E-Resources
            </button>

            <button class="nav-btn" onclick="showRestrictedPanel('p-reserves', this)">
                <span class="n-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                    </svg>
                </span>
                Reservations
            </button>

            <div class="sb-section" id="sb-admin-sect" style="display: none;" aria-hidden="true">Admin</div>

            <button class="nav-btn" id="nb-users" style="display: none;" onclick="showPanel('p-users', this)">
                <span class="n-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </span>
                Users DB
            </button>

            <button class="nav-btn" id="nb-mats" style="display: none;" onclick="showPanel('p-materials', this)">
                <span class="n-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                    </svg>
                </span>
                Materials DB
            </button>

            <div class="sb-section">Account</div>

            <button class="nav-btn" onclick="showRestrictedPanel('p-profile', this)">
                <span class="n-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <rect x="2" y="5" width="20" height="14" rx="2"/>
                        <path d="M16 10h2M16 14h2M6 10h6M6 14h4"/>
                    </svg>
                </span>
                My Profile
            </button>

        </nav>

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

    <div class="main-area">

        <header class="main-topbar">
            <div class="topbar-title" id="tb-title">Overview</div>

            <div class="topbar-right">
                <input class="topbar-search" type="text"
                    placeholder="Quick search…"
                    aria-label="Quick search"
                    onkeydown="topbarSearch(event)" />

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
        </header>

        <!-- OVERVIEW -->
<section id="p-overview" class="panel active">

    <div class="announcement" id="ann">
        <div class="ann-text">
            <strong>Library Hours Update:</strong> Welcome to UST E-Library. Browse materials, manage loans, and access digital resources.
        </div>
        <button class="ann-close" onclick="document.getElementById('ann').style.display='none'">×</button>
    </div>

    <div class="hero-banner">
        <div class="hero-bg-pattern"></div>

        <div class="hero-content">
            <div class="hero-eyebrow">✦ University of Santo Tomas</div>

            <h1 class="hero-heading">
                Welcome back,<br>
                <em><?php echo htmlspecialchars($first_name); ?></em>
            </h1>

            <p class="hero-body">
                Continue exploring your resources and stay on track with your studies.
            </p>

            <div class="hero-actions">
                <button class="btn-primary" onclick="showPanel('p-opac', document.querySelector('[onclick*=p-opac]'))">
                    Search OPAC
                </button>

                <button class="btn-ghost" onclick="showPanel('p-eres', document.querySelector('[onclick*=p-eres]'))">
                    Browse E-Resources
                </button>
            </div>
        </div>

        <div class="hero-stats">
            <div class="hero-stat">
                <div class="hero-stat-number"><?php echo count($materials); ?></div>
                <div class="hero-stat-label">Total Materials</div>
            </div>

            <div class="hero-stat">
                <div class="hero-stat-number"><?php echo count($eresources); ?></div>
                <div class="hero-stat-label">E-Resources</div>
            </div>

            <div class="hero-stat">
                <div class="hero-stat-number"><?php echo $reservedItemsCount; ?></div>
                <div class="hero-stat-label">Reservations</div>
            </div>
        </div>
    </div>

    <div class="quick-search">
        <div class="qs-label">Quick OPAC Search</div>

        <div class="qs-row">
            <div class="qs-input-wrap">
                <input type="text"
                    id="overview-search"
                    placeholder="Search by title, author, ISBN, category…"
                    onkeydown="overviewEnterSearch(event)">
            </div>

            <button class="filter-pill active" onclick="goToType('', this)">All</button>
            <button class="filter-pill" onclick="goToType('print', this)">Print</button>
            <button class="filter-pill" onclick="goToType('electronic', this)">Electronic</button>

            <button class="btn-primary" onclick="goToOpacSearch()">
                Search
            </button>
        </div>
    </div>

    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-value"><?php echo $activeBorrowsCount; ?></div>
            <div class="stat-label">Books Borrowed</div>
            <div class="stat-change">active</div>
        </div>

        <div class="stat-card">
            <div class="stat-value"><?php echo $reservedItemsCount; ?></div>
            <div class="stat-label">Reservations</div>
            <div class="stat-change pending">pending</div>
        </div>

        <div class="stat-card">
            <div class="stat-value"><?php echo count($eresources); ?></div>
            <div class="stat-label">E-Resources</div>
            <div class="stat-change">digital</div>
        </div>

        <div class="stat-card">
            <div class="stat-value"><?php echo $overdueItemsCount; ?></div>
            <div class="stat-label">Overdue Items</div>
            <div class="stat-change danger">due</div>
        </div>
    </div>

    <div class="section-header">
        <div class="section-title">Browse by Category</div>
    </div>

    <div class="categories-row">
        <span class="cat-chip" onclick="goToCategory('Introduction to Computing')">Introduction to Computing</span>
        <span class="cat-chip" onclick="goToCategory('Database Management')">Database Management</span>
        <span class="cat-chip" onclick="goToCategory('Computer Networks')">Computer Networks</span>
        <span class="cat-chip" onclick="goToCategory('Software Engineering')">Software Engineering</span>
        <span class="cat-chip" onclick="goToCategory('Operating Systems')">Operating Systems</span>
    </div>

    <div class="section-header">
        <div class="section-title">New Arrivals</div>
        <button class="section-link" onclick="showPanel('p-opac', document.querySelector('[onclick*=p-opac]'))">View all →</button>
    </div>

    <div class="books-grid">
        <?php if(!empty($materials)){ ?>
            <?php foreach(array_slice($materials, 0, 6) as $m){ ?>
                <div class="book-card">
                    <div class="book-cover">
                        <div class="book-cover-inner">
                            <?= htmlspecialchars($m["title"]) ?>
                        </div>
                        <span class="book-type-badge">
                            <?= htmlspecialchars($m["material_type"]) ?>
                        </span>
                    </div>

                    <div class="book-info">
                        <div class="book-title"><?= htmlspecialchars($m["title"]) ?></div>
                        <div class="book-author"><?= htmlspecialchars($m["author"]) ?></div>

                        <div class="book-footer">
                            <div class="avail-indicator">
                                <?= htmlspecialchars($m["available_copies"]) ?> available
                            </div>

                            <button class="book-action" onclick="showPanel('p-opac', document.querySelector('[onclick*=p-opac]'))">
                                →
                            </button>
                        </div>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
    </div>

    <div class="ornament-divider">
        <div class="ornament-line"></div>
        <div class="ornament-text">My Library Activity</div>
        <div class="ornament-line"></div>
    </div>

    <div class="two-col">

        <div class="table-card">
            <div class="table-head">
                <div class="table-head-title">Borrowed Items</div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Due Date</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if(!empty($myLoans)){ ?>
                        <?php foreach(array_slice($myLoans, 0, 4) as $loan){ ?>
                            <tr>
                                <td><?= htmlspecialchars($loan["title"]) ?></td>
                                <td><?= htmlspecialchars($loan["due_date"]) ?></td>
                                <td>
                                    <?php if($loan["due_date"] < date("Y-m-d")){ ?>
                                        <span class="status-pill pill-overdue">Overdue</span>
                                    <?php } else { ?>
                                        <span class="status-pill pill-active">Active</span>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="3">No borrowed items</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="activity-card">
            <div class="activity-head">
                <div class="activity-head-title">Recent Activity</div>
            </div>

            <div class="activity-list">
                <?php if(!empty($myReservations)){ ?>
                    <?php foreach(array_slice($myReservations, 0, 4) as $reserve){ ?>
                        <div class="activity-item">
                            <div class="act-body">
                                <div class="act-text">
                                    Reserved <strong><?= htmlspecialchars($reserve["title"]) ?></strong>
                                </div>
                                <div class="act-time">
                                    <?= htmlspecialchars($reserve["reserved_at"]) ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="activity-item">
                        <div class="act-body">
                            <div class="act-text">No recent reservations yet.</div>
                            <div class="act-time">Start browsing OPAC materials.</div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</section>

        <section id="p-opac" class="panel">
            <div class="sec-head">
                <div>
                    <h3>OPAC Search</h3>
                    <p>Search print and electronic materials</p>
                </div>
            </div>

            <div class="db-toolbar" style="margin-bottom: 20px;">
                <input type="search" id="opac-q"
                       placeholder="Title, Author, ISBN, Category…"
                       aria-label="Search catalog"
                       oninput="renderOpac()" />
                <select id="opac-type" onchange="renderOpac()" aria-label="Filter by type">
                    <option value="">All Types</option>
                    <option value="print">Print</option>
                    <option value="electronic">Electronic</option>
                    <option value="journals">Journals</option>
                </select>
                <select id="opac-cat" onchange="renderOpac()" aria-label="Filter by category">
                    <option value="">All Categories</option>
                    <option>Introduction to Computing</option>
                    <option>Object-Oriented Programming</option>
                    <option>Data Structures</option>
                    <option>Database Management</option>
                    <option>Software Engineering</option>
                    <option>Computer Networks</option>
                    <option>Operating Systems</option>
                </select>
            </div>

            <div id="opac-grid" class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Available</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if(!empty($materials)){ ?>
                            <?php foreach($materials as $m){ ?>
                                <tr data-type="<?= strtolower(htmlspecialchars($m["material_type"])) ?>">
                                    <td><?= htmlspecialchars($m["title"]) ?></td>
                                    <td><?= htmlspecialchars($m["author"]) ?></td>
                                    <td><?= htmlspecialchars($m["material_type"]) ?></td>
                                    <td><?= htmlspecialchars($m["category"]) ?></td>
                                    <td><?= htmlspecialchars($m["available_copies"]) ?></td>
                                    <td>
                                        <?php if($m["available_copies"] > 0){ ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="materialID" value="<?= $m["material_id"] ?>">
                                                <button type="submit" name="borrowBtn" class="btn-sm">Borrow</button>
                                            </form>
                                        <?php } ?>

                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="materialID" value="<?= $m["material_id"] ?>">
                                            <button type="submit" name="reserveBtn" class="btn-sm">Reserve</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="6">No materials found</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

                    <section id="p-mybooks" class="panel">
                <div class="sec-head">
                    <div>
                        <h3>My Loans</h3>
                        <p>Currently borrowed items and return history</p>
                    </div>
                </div>

                <div class="table-card">
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Type</th>
                                <th>Borrow Date</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if(!empty($myLoans)){ ?>
                                <?php foreach($myLoans as $loan){ ?>
                                    <tr>
                                        <td><?= htmlspecialchars($loan["title"]) ?></td>
                                        <td><?= htmlspecialchars($loan["author"]) ?></td>
                                        <td><?= htmlspecialchars($loan["material_type"]) ?></td>
                                        <td><?= htmlspecialchars($loan["borrowed_at"]) ?></td>
                                        <td><?= htmlspecialchars($loan["due_date"]) ?></td>
                                            <td>
                                                <?php 
                                                    if($loan["due_date"] < date("Y-m-d")){
                                                        echo "Overdue";
                                                    }
                                                    else{
                                                        echo "Borrowed";
                                                    }
                                                ?>
                                            </td>

                                            <td>
                                                <form method="POST" action="">
                                                    <input type="hidden" name="borrow_id" value="<?= $loan["borrow_id"] ?>">
                                                    <input type="hidden" name="material_id" value="<?= $loan["material_id"] ?>">
                                                    <button type="submit" name="returnBtn" class="btn-sm">Return</button>
                                                </form>
                                            </td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="7">No borrowed items</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="p-eres" class="panel">
                <div class="sec-head">
                    <div>
                        <h3>E-Resources</h3>
                        <p>Digital journals, e-books, and academic databases</p>
                    </div>
                </div>

                <div class="table-card">
                    <table>
                        <thead>
                            <tr>
                                <th>Resource Title</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Access</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if(!empty($eresources)){ ?>
                                <?php foreach($eresources as $e){ ?>
                                    <tr>
                                        <td><?= htmlspecialchars($e["title"]) ?></td>
                                        <td><?= htmlspecialchars($e["resource_type"]) ?></td>
                                        <td><?= htmlspecialchars($e["description"]) ?></td>
                                        <td>
                                            <a href="<?= htmlspecialchars($e["resource_link"]) ?>" target="_blank">
                                                <button class="btn-sm">
                                                    <?= htmlspecialchars($e["access_label"]) ?>
                                                </button>
                                            </a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="4">No e-resources found</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </section>

        <section id="p-reserves" class="panel">
            <div class="sec-head">
                <div>
                    <h3>Reservations</h3>
                    <p>Items reserved — you will be notified when available</p>
                </div>
            </div>

            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Type</th>
                            <th>Date Reserved</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if(!empty($myReservations)){ ?>
                            <?php foreach($myReservations as $reserve){ ?>
                                <tr>
                                    <td><?= htmlspecialchars($reserve["title"]) ?></td>
                                    <td><?= htmlspecialchars($reserve["author"]) ?></td>
                                    <td><?= htmlspecialchars($reserve["material_type"]) ?></td>
                                    <td><?= htmlspecialchars($reserve["reserved_at"]) ?></td>
                                    <td>Reserved</td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="5">No reserved items</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section id="p-users" class="panel">
            <div class="sec-head">
                <div>
                    <h3>Users Database</h3>
                    <p>Registered library accounts</p>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button class="btn-sm" onclick="exportDB()">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Export JSON
                    </button>
                </div>
            </div>

            <div class="db-toolbar">
                <input type="search" id="usearch"
                       placeholder="Search by name, ID, email…"
                       aria-label="Search users"
                       oninput="renderUsersTable()" />
                <select id="ufilter-role" onchange="renderUsersTable()" aria-label="Filter by role">
                    <option value="">All Roles</option>
                    <option value="student">Student</option>
                    <option value="faculty">Faculty</option>
                    <option value="admin">Admin</option>
                </select>
                <select id="ufilter-status" onchange="renderUsersTable()" aria-label="Filter by status">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="users-tbody"></tbody>
                </table>
            </div>

            <div class="table-meta" id="users-count"></div>
        </section>

        <section id="p-materials" class="panel">
            <div class="sec-head">
                <div>
                    <h3>Materials Database</h3>
                    <p>Library catalog</p>
                </div>
                <button class="btn-sm" onclick="openAddMaterial()">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Add Material
                </button>
            </div>

            <div class="db-toolbar">
                <input type="search" id="msearch"
                       placeholder="Search materials…"
                       aria-label="Search materials"
                       oninput="renderMaterialsTable()" />
                <select id="mfilter-type" onchange="renderMaterialsTable()" aria-label="Filter by type">
                    <option value="">All Types</option>
                    <option value="print">Print</option>
                    <option value="electronic">Electronic</option>
                    <option value="journals">Journals</option>
                </select>
            </div>

            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>ISBN</th>
                            <th>Copies</th>
                            <th>Available</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="mats-tbody"></tbody>
                </table>
            </div>

            <div class="table-meta" id="mats-count"></div>
        </section>

        <section id="p-profile" class="panel">
            <div class="sec-head">
                <div>
                    <h3>My Profile</h3>
                    <p>Your account information</p>
                </div>
            </div>

            <div class="profile-layout">

                <div class="profile-card">
                    <div class="profile-avatar-lg" id="prof-av-lg" aria-hidden="true"></div>
                    <h3 id="prof-name"><?= $full_name ?></h3>
                    <div class="p-role" id="prof-role"><?= $role ?></div>
                    <div class="p-id" id="prof-id"><?= $ust_id ?></div>
                    <div class="profile-meta" id="prof-meta">
                        <div class="p-meta-row"><span>Email</span><span><?= $email ?></span></div>
                        <div class="p-meta-row"><span>UST ID</span><span><?= $ust_id ?></span></div>
                        <div class="p-meta-row"><span>Role</span><span><?= $role ?></span></div>
                    </div>
                </div>

                <div class="profile-info-card">
                    <div class="sec-head" style="margin-bottom: 20px;">
                        <div>
                            <h3>Edit Profile</h3>
                            <p>Update your information</p>
                        </div>
                    </div>

                    <div class="field-row">
                        <div class="field-group">
                            <label for="edit-fname">First Name</label>
                            <div class="field-wrap no-icon">
                                <input type="text" id="edit-fname"
                                       value="<?= $first_name ?>"
                                       autocomplete="given-name" />
                            </div>
                        </div>

                        <div class="field-group">
                            <label for="edit-lname">Last Name</label>
                            <div class="field-wrap no-icon">
                                <input type="text" id="edit-lname"
                                       value="<?= $last_name ?>"
                                       autocomplete="family-name" />
                            </div>
                        </div>
                    </div>

                    <div class="field-group">
                        <label for="edit-email">Email</label>
                        <div class="field-wrap no-icon">
                            <input type="email" id="edit-email"
                                   value="<?= $email ?>"
                                   autocomplete="email" />
                        </div>
                    </div>

                    <div class="field-group">
                        <label for="edit-pass">New Password <span style="font-weight: 400; color: var(--muted);">(leave blank to keep current)</span></label>
                        <div class="field-wrap no-icon">
                            <input type="password" id="edit-pass"
                                   placeholder="New password"
                                   autocomplete="new-password" />
                        </div>
                    </div>

                    <button type="button"
                            class="btn-gold"
                            style="width: auto; padding: 11px 28px; margin-top: 8px;"
                            onclick="updateProfileFunc()">

                        Save Changes

                    </button>
                </div>

            </div>
        </section>

    </div>
</div>

<div class="modal-bg" id="del-modal" role="dialog" aria-modal="true" aria-labelledby="del-modal-title">
    <div class="modal">
        <button type="button" class="modal-close" onclick="closeModal('del-modal')" aria-label="Close">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
        <h3 id="del-modal-title">Delete User?</h3>
        <p id="del-msg">This action cannot be undone. The user record will be permanently removed.</p>
        <div class="modal-actions">
            <button type="button" class="btn-cancel" onclick="closeModal('del-modal')">Cancel</button>
            <button type="button" class="btn-danger-confirm" id="del-confirm-btn">Delete</button>
        </div>
    </div>
</div>

<div class="modal-bg" id="mat-modal" role="dialog" aria-modal="true" aria-labelledby="mat-modal-title">
    <div class="modal" style="max-width: 560px;">
        <button type="button" class="modal-close" onclick="closeModal('mat-modal')" aria-label="Close">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
        <h3 id="mat-modal-title">Add Material</h3>
        <p>Fill in the details for the new library material.</p>

        <div class="field-row">
            <div class="field-group">
                <label for="m-title">Title</label>
                <div class="field-wrap no-icon"><input type="text" id="m-title" placeholder="Material title" /></div>
            </div>
            <div class="field-group">
                <label for="m-author">Author</label>
                <div class="field-wrap no-icon"><input type="text" id="m-author" placeholder="Author name" /></div>
            </div>
        </div>

            <div class="field-row">
                <div class="field-group">
                <label for="m-type">Type</label>
                <div class="field-wrap no-icon">
                    <select id="m-type">
                        <option value="">All Types</option>
                        <option value="print">Print</option>
                        <option value="electronic">Electronic</option>
                        <option value="journals">Journals</option>
                    </select>

                </div>
            </div>
        </div>
            <div class="field-group">
                <label for="m-cat">Category</label>
                <div class="field-wrap no-icon"><input type="text" id="m-cat" placeholder="e.g. Computer Science" /></div>
            </div>
        </div>

        <div class="field-row">
            <div class="field-group">
                <label for="m-isbn">ISBN / Call No.</label>
                <div class="field-wrap no-icon"><input type="text" id="m-isbn" placeholder="ISBN or call number" /></div>
            </div>
            <div class="field-group">
                <label for="m-copies">Copies</label>
                <div class="field-wrap no-icon"><input type="number" id="m-copies" value="1" min="1" /></div>
            </div>
        </div>

        <div class="modal-actions">
            <button type="button" class="btn-cancel" onclick="closeModal('mat-modal')">Cancel</button>
            <button type="button" class="btn-gold" style="width: auto; padding: 10px 24px;" onclick="saveMaterial()">Save Material</button>
        </div>
    </div>
</div>

<div id="toast" aria-live="polite" aria-atomic="false"></div>

<script>
    var isGuest = <?php echo $isGuest ? "true" : "false"; ?>;
    var firstName = <?php echo json_encode($first_name); ?>;
    var lastName = <?php echo json_encode($last_name); ?>;
</script>

<script src="../scripts/service.js"></script>

<script>
function goToOpacSearch() {
    let searchValue = document.getElementById("overview-search").value;

    showPanel('p-opac', document.querySelector('.nav-btn[onclick*="p-opac"]'));

    document.getElementById("opac-q").value = searchValue;
    renderOpac();
}

function topbarSearch(event) {
    if(event.key === "Enter") {
        showPanel('p-opac', document.querySelector('.nav-btn[onclick*="p-opac"]'));

        document.getElementById("opac-q").value = event.target.value;
        renderOpac();
    }
}

function overviewEnterSearch(event) {
    if(event.key === "Enter") {
        goToOpacSearch();
    }
}

function goToCategory(category) {
    showPanel('p-opac', document.querySelector('.nav-btn[onclick*="p-opac"]'));

    document.getElementById("opac-cat").value = category;
    renderOpac();
}

function renderOpac() {
    let search = document.getElementById("opac-q").value.toLowerCase();
    let selectedType = document.getElementById("opac-type").value.toLowerCase();
    let selectedCategory = document.getElementById("opac-cat").value.toLowerCase();

    let rows = document.querySelectorAll("#opac-grid tbody tr");

    rows.forEach(function(row) {
        let text = row.innerText.toLowerCase();

        let rowType = row.getAttribute("data-type");
        rowType = rowType ? rowType.toLowerCase() : "";

        let rowCategory = row.children[3]
            ? row.children[3].innerText.toLowerCase()
            : "";

        let matchesSearch = text.includes(search);
        let matchesType = selectedType === "" || rowType === selectedType;
        let matchesCategory = selectedCategory === "" || rowCategory.includes(selectedCategory);

        row.style.display = matchesSearch && matchesType && matchesCategory ? "" : "none";
    });
}

function goToType(type, btn) {
    document.querySelectorAll(".filter-pill").forEach(function(pill) {
        pill.classList.remove("active");
    });

    btn.classList.add("active");

    showPanel('p-opac', document.querySelector('.nav-btn[onclick*="p-opac"]'));

    document.getElementById("opac-type").value = type;

    renderOpac();
}
</script>

</body>
</html>