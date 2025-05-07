<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}

require_once "config/database.php";

$brand = $model = $imei = $problem = $password = $customer_id = "";
$brand_err = $model_err = $problem_err = $customer_err = "";

// Get customers for dropdown
$sql = "SELECT id, name, phone FROM customers ORDER BY name";
$customers = mysqli_query($conn, $sql);

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate brand
    if(empty(trim($_POST["brand"]))){
        $brand_err = "Please enter device brand.";
    } else{
        $brand = trim($_POST["brand"]);
    }
    
    // Validate model
    if(empty(trim($_POST["model"]))){
        $model_err = "Please enter device model.";
    } else{
        $model = trim($_POST["model"]);
    }
    
    // Validate problem
    if(empty(trim($_POST["problem"]))){
        $problem_err = "Please enter device problem.";
    } else{
        $problem = trim($_POST["problem"]);
    }
    
    // Validate customer
    if(empty($_POST["customer_id"])){
        $customer_err = "Please select a customer.";
    } else{
        $customer_id = $_POST["customer_id"];
    }
    
    // Get other fields
    $imei = trim($_POST["imei"]);
    $password = trim($_POST["password"]);
    
    // Check input errors before inserting in database
    if(empty($brand_err) && empty($model_err) && empty($problem_err) && empty($customer_err)){
        $sql = "INSERT INTO devices (customer_id, brand, model, imei, problem, password, status) VALUES (?, ?, ?, ?, ?, ?, 'Received')";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "isssss", $customer_id, $brand, $model, $imei, $problem, $password);
            
            if(mysqli_stmt_execute($stmt)){
                header("location: devices.php");
                exit();
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        }
    }
    
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Device - Mobile Management System</title>
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
                    <h2>Add New Device</h2>
                    <a href="devices.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Devices
                    </a>
                </div>

                <div class="card">
                    <div class="card-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Customer</label>
                                    <select name="customer_id" class="form-select <?php echo (!empty($customer_err)) ? 'is-invalid' : ''; ?>">
                                        <option value="">Select Customer</option>
                                        <?php while($customer = mysqli_fetch_assoc($customers)): ?>
                                        <option value="<?php echo $customer['id']; ?>" <?php echo $customer_id == $customer['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($customer['name'] . ' (' . $customer['phone'] . ')'); ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                    <span class="invalid-feedback"><?php echo $customer_err; ?></span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Brand</label>
                                    <input type="text" name="brand" class="form-control <?php echo (!empty($brand_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $brand; ?>">
                                    <span class="invalid-feedback"><?php echo $brand_err; ?></span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Model</label>
                                    <input type="text" name="model" class="form-control <?php echo (!empty($model_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $model; ?>">
                                    <span class="invalid-feedback"><?php echo $model_err; ?></span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">IMEI Number</label>
                                    <input type="text" name="imei" class="form-control" value="<?php echo $imei; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Password/Pattern</label>
                                    <input type="text" name="password" class="form-control" value="<?php echo $password; ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Problem Description</label>
                                <textarea name="problem" class="form-control <?php echo (!empty($problem_err)) ? 'is-invalid' : ''; ?>" rows="3"><?php echo $problem; ?></textarea>
                                <span class="invalid-feedback"><?php echo $problem_err; ?></span>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-primary">Add Device</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 