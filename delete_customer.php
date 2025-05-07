<?php
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: index.php");
    exit;
}

require_once "config/database.php";

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"])){
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Delete related devices first
        $sql = "DELETE FROM devices WHERE customer_id = ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "i", $_POST["id"]);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        
        // Then delete the customer
        $sql = "DELETE FROM customers WHERE id = ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "i", $_POST["id"]);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        
        // Commit transaction
        mysqli_commit($conn);
        echo json_encode(["success" => true]);
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
    
    mysqli_close($conn);
} else{
    echo json_encode(["success" => false, "error" => "Invalid request"]);
}
?> 