<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}

// Only include database if not already included
if (!isset($conn)) {
    require_once "config/database.php";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Mobile Management System'; ?></title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --sidebar-bg: #4e73df;
            --sidebar-hover: #2e59d9;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
        }

        body {
            background-color: #f8f9fc;
        }

        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, var(--sidebar-bg) 0%, var(--sidebar-hover) 100%);
            padding-top: 1rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .sidebar a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            padding: 0.8rem 1rem;
            display: block;
            transition: all 0.3s ease;
            border-radius: 0.35rem;
            margin: 0.2rem 0.5rem;
        }

        .sidebar a:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .sidebar a.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.2);
        }

        .main-content {
            padding: 2rem;
        }

        .card {
            border: none;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            transition: transform 0.2s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem 1.25rem;
        }

        .form-control {
            border-radius: 0.35rem;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d3e2;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.75rem 1.5rem;
            border-radius: 0.35rem;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background-color: var(--sidebar-hover);
            border-color: var(--sidebar-hover);
            transform: translateY(-2px);
        }

        .alert {
            border-radius: 0.35rem;
            border: none;
        }

        .alert-success {
            background-color: rgba(28, 200, 138, 0.1);
            color: var(--success-color);
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            color: var(--secondary-color);
        }

        .badge {
            padding: 0.5em 0.75em;
            font-weight: 500;
        }

        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #fff;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            transition: transform 0.3s ease;
        }

        .profile-picture:hover {
            transform: scale(1.05);
        }

        .activity-item {
            padding: 1rem;
            border-left: 3px solid var(--primary-color);
            margin-bottom: 1rem;
            background-color: #fff;
            border-radius: 0.35rem;
            transition: transform 0.2s ease;
        }

        .activity-item:hover {
            transform: translateX(5px);
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }

        .activity-icon.primary {
            background-color: rgba(78, 115, 223, 0.1);
            color: var(--primary-color);
        }

        .activity-icon.success {
            background-color: rgba(28, 200, 138, 0.1);
            color: var(--success-color);
        }

        .activity-icon.warning {
            background-color: rgba(246, 194, 62, 0.1);
            color: var(--warning-color);
        }

        .stats-card {
            background: linear-gradient(45deg, var(--primary-color), var(--sidebar-hover));
            color: white;
            border-radius: 0.35rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .stats-icon {
            font-size: 2rem;
            opacity: 0.8;
        }

        .stats-number {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .stats-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
                position: fixed;
                bottom: 0;
                width: 100%;
                z-index: 1000;
                padding: 0.5rem;
            }

            .sidebar nav {
                display: flex;
                justify-content: space-around;
            }

            .sidebar a {
                padding: 0.5rem;
                margin: 0;
                text-align: center;
            }

            .sidebar a i {
                font-size: 1.2rem;
            }

            .sidebar a span {
                display: none;
            }

            .main-content {
                margin-bottom: 4rem;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <h3 class="text-white text-center mb-4">Mobile MS</h3>
                <nav>
                    <a href="dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'class="active"' : ''; ?>>
                        <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
                    </a>
                    <a href="devices.php" <?php echo basename($_SERVER['PHP_SELF']) == 'devices.php' ? 'class="active"' : ''; ?>>
                        <i class="bi bi-phone"></i> <span>Devices</span>
                    </a>
                    <a href="customers.php" <?php echo basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'class="active"' : ''; ?>>
                        <i class="bi bi-people"></i> <span>Customers</span>
                    </a>
                    <?php if($_SESSION["role"] == "admin"): ?>
                    <a href="users.php" <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'class="active"' : ''; ?>>
                        <i class="bi bi-person-gear"></i> <span>Users</span>
                    </a>
                    <?php endif; ?>
                    <a href="profile.php" <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'class="active"' : ''; ?>>
                        <i class="bi bi-person-circle"></i> <span>Profile</span>
                    </a>
                    <a href="logout.php">
                        <i class="bi bi-box-arrow-right"></i> <span>Logout</span>
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-gray-800"><?php echo $page_title ?? 'Dashboard'; ?></h2>
                    <div class="d-flex align-items-center">
                        <a href="profile.php" class="text-decoration-none me-3">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION["username"]); ?>&background=4e73df&color=fff" 
                                 alt="Profile" 
                                 class="rounded-circle"
                                 style="width: 40px; height: 40px;">
                        </a>
                        <span class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                    </div>
                </div> 