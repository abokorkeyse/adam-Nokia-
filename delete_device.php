<?php
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: index.php");
    exit;
}

require_once "config/database.php";

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"])){
    $sql = "DELETE FROM devices WHERE id = ?";
    
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $_POST["id"]);
        
        if(mysqli_stmt_execute($stmt)){
            echo json_encode(["success" => true]);
        } else{
            echo json_encode(["success" => false, "error" => "Error deleting device"]);
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