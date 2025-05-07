<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}

require_once "config/database.php";

// Get device information
if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    $sql = "SELECT d.*, c.name as customer_name, c.phone as customer_phone, c.email as customer_email, c.address as customer_address, 
            u.username as technician_name 
            FROM devices d 
            LEFT JOIN customers c ON d.customer_id = c.id 
            LEFT JOIN users u ON d.technician_id = u.id 
            WHERE d.id = ?";
    
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $_GET["id"]);
        
        if(mysqli_stmt_execute($stmt)){
            $result = mysqli_stmt_get_result($stmt);
    
            if(mysqli_num_rows($result) == 1){
                $device = mysqli_fetch_array($result);
            } else{
                header("location: devices.php");
                exit();
            }
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
    
    mysqli_stmt_close($stmt);
} else{
    header("location: devices.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Device - Mobile Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            padding-top: 1rem;
        }
        .sidebar a {
            color: #fff;
            text-decoration: none;
            padding: 0.5rem 1rem;
            display: block;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .main-content {
            padding: 2rem;
        }
        .status-badge {
            font-size: 1rem;
            padding: 0.5rem 1rem;
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
                    <a href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                    <a href="devices.php" class="active"><i class="bi bi-phone"></i> Devices</a>
                    <a href="customers.php"><i class="bi bi-people"></i> Customers</a>
                    <?php if($_SESSION["role"] == "admin"): ?>
                    <a href="users.php"><i class="bi bi-person-gear"></i> Users</a>
                    <?php endif; ?>
                    <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Device Details</h2>
                    <div>
                        <a href="edit_device.php?id=<?php echo $device['id']; ?>" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Edit Device
                        </a>
                        <a href="devices.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Devices
                        </a>
                    </div>
                </div>

                <div class="row">
                    <!-- Device Information -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Device Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Status</label>
                                    <div>
                                        <span class="badge bg-<?php 
                                            echo $device['status'] == 'Completed' ? 'success' : 
                                                ($device['status'] == 'In Progress' ? 'warning' : 
                                                ($device['status'] == 'Received' ? 'info' : 'secondary')); 
                                        ?> status-badge">
                                            <?php echo $device['status']; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Device</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($device['brand'] . ' ' . $device['model']); ?></p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">IMEI Number</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($device['imei']); ?></p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Password/Pattern</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($device['password']); ?></p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Problem Description</label>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($device['problem'])); ?></p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Notes</label>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($device['notes'] ?? '')); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Customer Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Name</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($device['customer_name']); ?></p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Phone</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($device['customer_phone']); ?></p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Email</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($device['customer_email']); ?></p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Address</label>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($device['customer_address'])); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Repair Information -->
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Repair Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Assigned Technician</label>
                                        <p class="mb-0"><?php echo htmlspecialchars($device['technician_name'] ?? 'Not Assigned'); ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Received Date</label>
                                        <p class="mb-0"><?php echo date('F d, Y H:i', strtotime($device['received_date'])); ?></p>
                                    </div>
                                    <?php if($device['completed_date']): ?>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Completed Date</label>
                                        <p class="mb-0"><?php echo date('F d, Y H:i', strtotime($device['completed_date'])); ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 