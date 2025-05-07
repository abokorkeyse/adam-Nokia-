<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}

require_once "config/database.php";

// Initialize variables
$search = "";
$status_filter = "";
$where_clause = "1=1";

// Handle search and filters
if($_SERVER["REQUEST_METHOD"] == "GET") {
    if(isset($_GET["search"])) {
        $search = trim($_GET["search"]);
        if(!empty($search)) {
            $where_clause .= " AND (d.brand LIKE '%$search%' OR d.model LIKE '%$search%' OR d.imei LIKE '%$search%' OR c.name LIKE '%$search%')";
        }
    }
    if(isset($_GET["status"]) && !empty($_GET["status"])) {
        $status_filter = $_GET["status"];
        $where_clause .= " AND d.status = '$status_filter'";
    }
}

// Get devices with customer and technician information
$sql = "SELECT d.*, c.name as customer_name, c.phone as customer_phone, u.username as technician_name 
        FROM devices d 
        LEFT JOIN customers c ON d.customer_id = c.id 
        LEFT JOIN users u ON d.technician_id = u.id 
        WHERE $where_clause 
        ORDER BY d.received_date DESC";
$devices = mysqli_query($conn, $sql);

// Get technicians for assignment
$sql = "SELECT id, username FROM users WHERE role = 'technician'";
$technicians = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devices - Mobile Management System</title>
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
                    <h2>Devices</h2>
                    <a href="add_device.php" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Add New Device
                    </a>
                </div>

                <!-- Search and Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="search" placeholder="Search devices..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" name="status">
                                    <option value="">All Status</option>
                                    <option value="Received" <?php echo $status_filter == 'Received' ? 'selected' : ''; ?>>Received</option>
                                    <option value="In Progress" <?php echo $status_filter == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="Completed" <?php echo $status_filter == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="Delivered" <?php echo $status_filter == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Devices Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Customer</th>
                                        <th>Device</th>
                                        <th>IMEI</th>
                                        <th>Problem</th>
                                        <th>Status</th>
                                        <th>Technician</th>
                                        <th>Received Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($device = mysqli_fetch_assoc($devices)): ?>
                                    <tr>
                                        <td><?php echo $device['id']; ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($device['customer_name']); ?><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($device['customer_phone']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($device['brand'] . ' ' . $device['model']); ?></td>
                                        <td><?php echo htmlspecialchars($device['imei']); ?></td>
                                        <td><?php echo htmlspecialchars($device['problem']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $device['status'] == 'Completed' ? 'success' : 
                                                    ($device['status'] == 'In Progress' ? 'warning' : 
                                                    ($device['status'] == 'Received' ? 'info' : 'secondary')); 
                                            ?>">
                                                <?php echo $device['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if($_SESSION["role"] == "admin"): ?>
                                            <select class="form-select form-select-sm technician-select" data-device-id="<?php echo $device['id']; ?>">
                                                <option value="">Not Assigned</option>
                                                <?php 
                                                mysqli_data_seek($technicians, 0);
                                                while($tech = mysqli_fetch_assoc($technicians)): 
                                                ?>
                                                <option value="<?php echo $tech['id']; ?>" <?php echo $device['technician_id'] == $tech['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($tech['username']); ?>
                                                </option>
                                                <?php endwhile; ?>
                                            </select>
                                            <?php else: ?>
                                            <?php echo htmlspecialchars($device['technician_name'] ?? 'Not Assigned'); ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($device['received_date'])); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="edit_device.php?id=<?php echo $device['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="view_device.php?id=<?php echo $device['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <?php if($_SESSION["role"] == "admin"): ?>
                                                <button type="button" class="btn btn-sm btn-danger delete-device" data-id="<?php echo $device['id']; ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                <?php endif; ?>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle technician assignment
        document.querySelectorAll('.technician-select').forEach(select => {
            select.addEventListener('change', function() {
                const deviceId = this.dataset.deviceId;
                const technicianId = this.value;
                
                fetch('update_technician.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `device_id=${deviceId}&technician_id=${technicianId}`
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        alert('Technician assigned successfully!');
                    } else {
                        alert('Error assigning technician!');
                    }
                });
            });
        });

        // Handle device deletion
        document.querySelectorAll('.delete-device').forEach(button => {
            button.addEventListener('click', function() {
                if(confirm('Are you sure you want to delete this device?')) {
                    const deviceId = this.dataset.id;
                    
                    fetch('delete_device.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id=${deviceId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            this.closest('tr').remove();
                            alert('Device deleted successfully!');
                        } else {
                            alert('Error deleting device!');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html> 