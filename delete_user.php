<?php
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: index.php");
    exit;
}

require_once "config/database.php";

// Check if it's a POST request and user ID is provided
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"])) {
    $id = $_POST["id"];
    
    // Don't allow deleting own account
    if($id == $_SESSION["id"]) {
        echo json_encode(["success" => false, "message" => "You cannot delete your own account!"]);
        exit;
    }
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Delete related records first (if any)
        // For example, if there are any records in other tables that reference this user
        // $sql = "DELETE FROM some_table WHERE user_id = ?";
        // $stmt = mysqli_prepare($conn, $sql);
        // mysqli_stmt_bind_param($stmt, "i", $id);
        // mysqli_stmt_execute($stmt);
        
        // Delete the user
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if(mysqli_stmt_execute($stmt)) {
            mysqli_commit($conn);
            echo json_encode(["success" => true]);
        } else {
            throw new Exception("Error deleting user");
        }
    } catch(Exception $e) {
        mysqli_rollback($conn);
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}

mysqli_close($conn);
?> 