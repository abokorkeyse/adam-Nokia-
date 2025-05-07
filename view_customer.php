<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}

require_once "config/database.php";

// Get customer information
if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    // Get customer details
    $sql = "SELECT * FROM customers WHERE id = ?";
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $_GET["id"]);
        
        if(mysqli_stmt_execute($stmt)){
            $result = mysqli_stmt_get_result($stmt);
    
            if(mysqli_num_rows($result) == 1){
                $customer = mysqli_fetch_array($result);
            } else{
                header("location: customers.php");
                exit();
            }
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
    
    mysqli_stmt_close($stmt);
    
    // Get customer's devices
    $sql = "SELECT d.*, u.username as technician_name 
            FROM devices d 
            LEFT JOIN users u ON d.technician_id = u.id 
            WHERE d.customer_id = ? 
            ORDER BY d.received_date DESC";
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $_GET["id"]);
        mysqli_stmt_execute($stmt);
        $devices = mysqli_stmt_get_result($stmt);
    }
    
    mysqli_stmt_close($stmt);
} else{
    header("location: customers.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Customer - Mobile Management System</title>
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
            font-size: 0.875rem;
            padding: 0.25rem 0.5rem;
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
                    <a href="devices.php"><i class="bi bi-phone"></i> Devices</a>
                    <a href="customers.php" class="active"><i class="bi bi-people"></i> Customers</a>
                    <?php if($_SESSION["role"] == "admin"): ?>
                    <a href="users.php"><i class="bi bi-person-gear"></i> Users</a>
                    <?php endif; ?>
                    <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Customer Details</h2>
                    <div>
                        <a href="edit_customer.php?id=<?php echo $customer['id']; ?>" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Edit Customer
                        </a>
                        <a href="add_device.php?customer_id=<?php echo $customer['id']; ?>" class="btn btn-success">
                            <i class="bi bi-plus-lg"></i> Add Device
                        </a>
                        <a href="customers.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Customers
                        </a>
                    </div>
                </div>

                <div class="row">
                    <!-- Customer Information -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Customer Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Name</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($customer['name']); ?></p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Phone</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($customer['phone']); ?></p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Email</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($customer['email']); ?></p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Address</label>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($customer['address'])); ?></p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Registered On</label>
                                    <p class="mb-0"><?php echo date('F d, Y', strtotime($customer['created_at'])); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Device Statistics -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Device Statistics</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $total_devices = mysqli_num_rows($devices);
                                $completed_devices = 0;
                                $in_progress_devices = 0;
                                $received_devices = 0;
                                
                                mysqli_data_seek($devices, 0);
                                while($device = mysqli_fetch_assoc($devices)) {
                                    switch($device['status']) {
                                        case 'Completed':
                                            $completed_devices++;
                                            break;
                                        case 'In Progress':
                                            $in_progress_devices++;
                                            break;
                                        case 'Received':
                                            $received_devices++;
                                            break;
                                    }
                                }
                                ?>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="card bg-primary text-white">
                                            <div class="card-body">
                                                <h6 class="card-title">Total Devices</h6>
                                                <h2 class="mb-0"><?php echo $total_devices; ?></h2>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="card bg-success text-white">
                                            <div class="card-body">
                                                <h6 class="card-title">Completed</h6>
                                                <h2 class="mb-0"><?php echo $completed_devices; ?></h2>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="card bg-warning text-dark">
                                            <div class="card-body">
                                                <h6 class="card-title">In Progress</h6>
                                                <h2 class="mb-0"><?php echo $in_progress_devices; ?></h2>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="card bg-info text-white">
                                            <div class="card-body">
                                                <h6 class="card-title">Received</h6>
                                                <h2 class="mb-0"><?php echo $received_devices; ?></h2>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Device History -->
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Device History</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Device</th>
                                                <th>Problem</th>
                                                <th>Status</th>
                                                <th>Technician</th>
                                                <th>Received Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            mysqli_data_seek($devices, 0);
                                            while($device = mysqli_fetch_assoc($devices)): 
                                            ?>
                                            <tr>
                                                <td><?php echo $device['id']; ?></td>
                                                <td><?php echo htmlspecialchars($device['brand'] . ' ' . $device['model']); ?></td>
                                                <td><?php echo htmlspecialchars($device['problem']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $device['status'] == 'Completed' ? 'success' : 
                                                            ($device['status'] == 'In Progress' ? 'warning' : 
                                                            ($device['status'] == 'Received' ? 'info' : 'secondary')); 
                                                    ?> status-badge">
                                                        <?php echo $device['status']; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($device['technician_name'] ?? 'Not Assigned'); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($device['received_date'])); ?></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="view_device.php?id=<?php echo $device['id']; ?>" class="btn btn-sm btn-info">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <a href="edit_device.php?id=<?php echo $device['id']; ?>" class="btn btn-sm btn-primary">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
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