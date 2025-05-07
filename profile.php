<?php
// Start session and include database connection
require_once "config/database.php";
require_once "includes/data_flow.php";

$page_title = "Profile Settings";

// Initialize variables
$username = $email = "";
$username_err = $email_err = $password_err = $confirm_password_err = $success_msg = "";

// Get user information
$sql = "SELECT * FROM users WHERE id = ?";
if($stmt = mysqli_prepare($conn, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["id"]);
    
    if(mysqli_stmt_execute($stmt)){
        $result = mysqli_stmt_get_result($stmt);
        if(mysqli_num_rows($result) == 1){
            $user = mysqli_fetch_array($result);
            $username = $user['username'];
            $email = $user['email'];
        }
    }
    mysqli_stmt_close($stmt);
}

// Get user statistics
$user_stats = $dataFlow->getUserStats($_SESSION["id"]);

// Get recent activity
$recent_activity = $dataFlow->getRecentActivity(3);

// Process form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate username
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter a username.";
    } else{
        $sql = "SELECT id FROM users WHERE username = ? AND id != ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "si", $param_username, $param_id);
            $param_username = trim($_POST["username"]);
            $param_id = $_SESSION["id"];
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) > 0){
                    $username_err = "This username is already taken.";
                } else{
                    $username = trim($_POST["username"]);
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    // Validate email
    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter an email.";
    } else{
        $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "si", $param_email, $param_id);
            $param_email = trim($_POST["email"]);
            $param_id = $_SESSION["id"];
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) > 0){
                    $email_err = "This email is already taken.";
                } else{
                    $email = trim($_POST["email"]);
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    // Validate password if provided
    if(!empty(trim($_POST["password"]))){
        if(strlen(trim($_POST["password"])) < 6){
            $password_err = "Password must have at least 6 characters.";
        } else{
            $password = trim($_POST["password"]);
        }
        
        // Validate confirm password
        if(empty(trim($_POST["confirm_password"]))){
            $confirm_password_err = "Please confirm password.";     
        } else{
            $confirm_password = trim($_POST["confirm_password"]);
            if(empty($password_err) && ($password != $confirm_password)){
                $confirm_password_err = "Password did not match.";
            }
        }
    }
    
    // Check input errors before updating the database
    if(empty($username_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)){
        $sql = "UPDATE users SET username = ?, email = ?";
        $params = array($username, $email);
        $types = "ss";
        
        if(!empty($password)){
            $sql .= ", password = ?";
            $params[] = password_hash($password, PASSWORD_DEFAULT);
            $types .= "s";
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $_SESSION["id"];
        $types .= "i";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            
            if(mysqli_stmt_execute($stmt)){
                $success_msg = "Profile updated successfully.";
                $_SESSION["username"] = $username;
                
                // Log the activity
                $dataFlow->logActivity(
                    $_SESSION["id"],
                    "Profile Update",
                    "Updated profile information"
                );
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

require_once "includes/header.php";
?>

<?php if(!empty($success_msg)): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>
    <?php echo $success_msg; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- Profile Overview -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center p-4">
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($username); ?>&background=4e73df&color=fff" 
                 alt="Profile Picture" 
                 class="profile-picture mx-auto mb-3">
            <h4><?php echo htmlspecialchars($username); ?></h4>
            <p class="text-muted"><?php echo ucfirst($_SESSION["role"]); ?></p>
            <button class="btn btn-outline-primary btn-sm">
                <i class="bi bi-camera me-2"></i>Change Photo
            </button>
        </div>
    </div>
    <div class="col-md-8">
        <div class="row">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stats-number"><?php echo $user_stats['total_devices']; ?></div>
                            <div class="stats-label">Devices</div>
                        </div>
                        <div class="stats-icon">
                            <i class="bi bi-phone"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stats-number"><?php echo $user_stats['completed_repairs']; ?></div>
                            <div class="stats-label">Completed</div>
                        </div>
                        <div class="stats-icon">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stats-number"><?php echo date('Y'); ?></div>
                            <div class="stats-label">Member Since</div>
                        </div>
                        <div class="stats-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person-circle me-2"></i>Account Information</h5>
            </div>
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                        </div>
                        <div class="invalid-feedback"><?php echo $username_err; ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                        </div>
                        <div class="invalid-feedback"><?php echo $email_err; ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                            <input type="text" class="form-control" value="<?php echo ucfirst($_SESSION["role"]); ?>" readonly>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-save me-2"></i>Update Profile
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-key me-2"></i>Change Password</h5>
            </div>
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                        </div>
                        <div class="invalid-feedback"><?php echo $password_err; ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
                        </div>
                        <div class="invalid-feedback"><?php echo $confirm_password_err; ?></div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-key-fill me-2"></i>Change Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-activity me-2"></i>Recent Activity</h5>
            </div>
            <div class="card-body">
                <?php while($activity = mysqli_fetch_assoc($recent_activity)): ?>
                <div class="activity-item">
                    <div class="d-flex align-items-center">
                        <div class="activity-icon primary">
                            <i class="bi bi-person"></i>
                        </div>
                        <div>
                            <h6 class="mb-1"><?php echo htmlspecialchars($activity['action']); ?></h6>
                            <p class="text-muted mb-0">
                                <?php echo htmlspecialchars($activity['description']); ?> - 
                                <?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once "includes/footer.php"; ?> 