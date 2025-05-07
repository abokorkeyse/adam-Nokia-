<?php
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: index.php");
    exit;
}

require_once "config/database.php";

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["device_id"]) && isset($_POST["technician_id"])){
    $technician_id = !empty($_POST["technician_id"]) ? $_POST["technician_id"] : null;
    
    $sql = "UPDATE devices SET technician_id = ? WHERE id = ?";
    
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "ii", $technician_id, $_POST["device_id"]);
        
        if(mysqli_stmt_execute($stmt)){
            echo json_encode(["success" => true]);
        } else{
            echo json_encode(["success" => false, "error" => "Error updating technician"]);
        }

        mysqli_stmt_close($stmt);
    } else{
        echo json_encode(["success" => false, "error" => "Error preparing statement"]);
    }
    
    mysqli_close($conn);
} else{
    echo json_encode(["success" => false, "error" => "Invalid request"]);
}
?> 