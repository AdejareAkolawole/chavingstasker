<?php
// admin/admin_reports.php
require_once 'admin_check.php';
require_once '../db.php';
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics</title>
    <link rel="stylesheet" href="../dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .report-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }
        .report-card {
            background: var(--bg-sidebar);
            padding: 1.5rem;
            border-radius: var(--radius);
            border: 1px solid var(--border-color);
        }
        .report-card h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .report-card p {
            color: var(--text-secondary);
        }
        .report-card ul {
            list-style: none;
            padding-left: 0;
            margin-top: 1rem;
        }
        .report-card li {
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--border-color);
        }
        .report-card li:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="admin_dashboard.php" class="logo">Admin Panel</a>
            </div>
            <nav class="sidebar-nav">
                <p class="nav-section-title">Admin Menu</p>
                <a href="admin_dashboard.php" class="nav-item"><i class="fas fa-chart-line"></i><span>Dashboard</span></a>
                <a href="admin_users.php" class="nav-item"><i class="fas fa-users"></i><span>Manage Users</span></a>
                <a href="admin_tasks.php" class="nav-item"><i class="fas fa-briefcase"></i><span>Manage Gigs</span></a>
                <a href="admin_promotions.php" class="nav-item"><i class="fas fa-rocket"></i><span>Promotions</span></a>
                <a href="admin_transactions.php" class="nav-item"><i class="fas fa-credit-card"></i><span>Transactions</span></a>
                <a href="admin_reports.php" class="nav-item active"><i class="fas fa-chart-bar"></i><span>Reports</span></a>
            </nav>
            <div class="sidebar-footer">
                <a href="../logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Log Out</span></a>
            </div>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <div class="header-greeting">
                    <h1>Reports & Analytics</h1>
                    <p>Detailed insights and data on platform activity.</p>
                </div>
            </header>

            <section class="report-grid">
                <div class="report-card">
                    <h3>User Growth</h3>
                    <p>Track new user registrations over time.</p>
                    <ul>
                        <li>Total Users: <span style="float:right; font-weight:600;"><?php
                            $stmt = $pdo->query("SELECT COUNT(*) FROM users");
                            echo $stmt->fetchColumn();
                        ?></span></li>
                        <li>Clients: <span style="float:right; font-weight:600;"><?php
                            $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'Client'");
                            echo $stmt->fetchColumn();
                        ?></span></li>
                        <li>Lancers: <span style="float:right; font-weight:600;"><?php
                            $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'Lancer'");
                            echo $stmt->fetchColumn();
                        ?></span></li>
                    </ul>
                </div>

                <div class="report-card">
                    <h3>Gig Performance</h3>
                    <p>Monitor gig creation and completion rates.</p>
                    <ul>
                        <li>Total Gigs: <span style="float:right; font-weight:600;"><?php
                            $stmt = $pdo->query("SELECT COUNT(*) FROM tasks");
                            echo $stmt->fetchColumn();
                        ?></span></li>
                        <li>Open Gigs: <span style="float:right; font-weight:600;"><?php
                            $stmt = $pdo->query("SELECT COUNT(*) FROM tasks WHERE status = 'Open'");
                            echo $stmt->fetchColumn();
                        ?></span></li>
                        <li>Completed Gigs: <span style="float:right; font-weight:600;"><?php
                            $stmt = $pdo->query("SELECT COUNT(*) FROM tasks WHERE status = 'Completed'");
                            echo $stmt->fetchColumn();
                        ?></span></li>
                    </ul>
                </div>
            </section>
        </main>
    </div>
</body>
</html>