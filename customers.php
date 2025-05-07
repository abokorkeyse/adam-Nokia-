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
$where_clause = "1=1";

// Handle search
if($_SERVER["REQUEST_METHOD"] == "GET") {
    if(isset($_GET["search"])) {
        $search = trim($_GET["search"]);
        if(!empty($search)) {
            $where_clause .= " AND (name LIKE '%$search%' OR phone LIKE '%$search%' OR email LIKE '%$search%')";
        }
    }
}

// Get customers with device count
$sql = "SELECT c.*, COUNT(d.id) as device_count 
        FROM customers c 
        LEFT JOIN devices d ON c.id = d.customer_id 
        WHERE $where_clause 
        GROUP BY c.id 
        ORDER BY c.name";
$customers = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - Mobile Management System</title>
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
                    <h2>Customers</h2>
                    <a href="add_customer.php" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Add New Customer
                    </a>
                </div>

                <!-- Search -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-10">
                                <input type="text" class="form-control" name="search" placeholder="Search customers..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Search</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Customers Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Address</th>
                                        <th>Devices</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($customer = mysqli_fetch_assoc($customers)): ?>
                                    <tr>
                                        <td><?php echo $customer['id']; ?></td>
                                        <td><?php echo htmlspecialchars($customer['name']); ?></td>
                                        <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                        <td><?php echo htmlspecialchars($customer['address']); ?></td>
                                        <td>
                                            <span class="badge bg-primary"><?php echo $customer['device_count']; ?></span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="edit_customer.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="view_customer.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <?php if($_SESSION["role"] == "admin"): ?>
                                                <button type="button" class="btn btn-sm btn-danger delete-customer" data-id="<?php echo $customer['id']; ?>">
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
        // Handle customer deletion
        document.querySelectorAll('.delete-customer').forEach(button => {
            button.addEventListener('click', function() {
                if(confirm('Are you sure you want to delete this customer? This will also delete all their devices.')) {
                    const customerId = this.dataset.id;
                    
                    fetch('delete_customer.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id=${customerId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            this.closest('tr').remove();
                            alert('Customer deleted successfully!');
                        } else {
                            alert('Error deleting customer!');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html> 