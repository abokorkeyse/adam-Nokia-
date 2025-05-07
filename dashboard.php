<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}

require_once "config/database.php";

$page_title = "Dashboard";

// Get statistics
$stats = array();

// Total devices
$sql = "SELECT COUNT(*) as total FROM devices";
$result = mysqli_query($conn, $sql);
$stats['total_devices'] = mysqli_fetch_assoc($result)['total'];

// Devices by status
$sql = "SELECT status, COUNT(*) as count FROM devices GROUP BY status";
$result = mysqli_query($conn, $sql);
$stats['status_counts'] = array();
while($row = mysqli_fetch_assoc($result)) {
    $stats['status_counts'][$row['status']] = $row['count'];
}

// Recent devices
$sql = "SELECT d.*, c.name as customer_name, u.username as technician_name 
        FROM devices d 
        LEFT JOIN customers c ON d.customer_id = c.id 
        LEFT JOIN users u ON d.technician_id = u.id 
        ORDER BY d.received_date DESC LIMIT 5";
$recent_devices = mysqli_query($conn, $sql);

require_once "includes/header.php";
?>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-md-3">
        <div class="stat-card">
            <h5>Total Devices</h5>
            <h2><?php echo $stats['total_devices']; ?></h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <h5>Received</h5>
            <h2><?php echo isset($stats['status_counts']['Received']) ? $stats['status_counts']['Received'] : 0; ?></h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <h5>In Progress</h5>
            <h2><?php echo isset($stats['status_counts']['In Progress']) ? $stats['status_counts']['In Progress'] : 0; ?></h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <h5>Completed</h5>
            <h2><?php echo isset($stats['status_counts']['Completed']) ? $stats['status_counts']['Completed'] : 0; ?></h2>
        </div>
    </div>
</div>

<!-- Recent Devices -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-phone me-2"></i>Recent Devices</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Device</th>
                        <th>Status</th>
                        <th>Technician</th>
                        <th>Received Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($device = mysqli_fetch_assoc($recent_devices)): ?>
                    <tr>
                        <td><?php echo $device['id']; ?></td>
                        <td><?php echo htmlspecialchars($device['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($device['brand'] . ' ' . $device['model']); ?></td>
                        <td>
                            <span class="badge bg-<?php 
                                echo $device['status'] == 'Completed' ? 'success' : 
                                    ($device['status'] == 'In Progress' ? 'warning' : 
                                    ($device['status'] == 'Received' ? 'info' : 'secondary')); 
                            ?>">
                                <?php echo $device['status']; ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($device['technician_name'] ?? 'Not Assigned'); ?></td>
                        <td><?php echo date('M d, Y', strtotime($device['received_date'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once "includes/footer.php"; ?> 