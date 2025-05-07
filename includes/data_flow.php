<?php
// Data Flow Handler Class
class DataFlow {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // Activity Logging
    public function logActivity($user_id, $action, $description = '') {
        $sql = "INSERT INTO activity_log (user_id, action, description) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "iss", $user_id, $action, $description);
        return mysqli_stmt_execute($stmt);
    }
    
    // Device Status Updates
    public function updateDeviceStatus($device_id, $status, $technician_id = null) {
        $sql = "UPDATE devices SET status = ?, technician_id = ?, updated_at = CURRENT_TIMESTAMP";
        if ($status == 'Completed') {
            $sql .= ", completed_date = CURRENT_TIMESTAMP";
        }
        $sql .= " WHERE id = ?";
        
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "sii", $status, $technician_id, $device_id);
        return mysqli_stmt_execute($stmt);
    }
    
    // Get User Statistics
    public function getUserStats($user_id) {
        $stats = array();
        
        // Get total devices handled
        $sql = "SELECT COUNT(*) as total FROM devices WHERE technician_id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $stats['total_devices'] = mysqli_fetch_assoc($result)['total'];
        
        // Get completed repairs
        $sql = "SELECT COUNT(*) as completed FROM repairs WHERE technician_id = ? AND status = 'Completed'";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $stats['completed_repairs'] = mysqli_fetch_assoc($result)['completed'];
        
        return $stats;
    }
    
    // Get Recent Activity
    public function getRecentActivity($limit = 5) {
        $sql = "SELECT al.*, u.username 
                FROM activity_log al 
                JOIN users u ON al.user_id = u.id 
                ORDER BY al.created_at DESC 
                LIMIT ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $limit);
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_get_result($stmt);
    }
    
    // Get Device History
    public function getDeviceHistory($device_id) {
        $sql = "SELECT d.*, c.name as customer_name, u.username as technician_name,
                r.repair_description, r.status as repair_status, r.created_at as repair_date
                FROM devices d
                LEFT JOIN customers c ON d.customer_id = c.id
                LEFT JOIN users u ON d.technician_id = u.id
                LEFT JOIN repairs r ON d.id = r.device_id
                WHERE d.id = ?
                ORDER BY r.created_at DESC";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $device_id);
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_get_result($stmt);
    }
    
    // Get Customer Devices
    public function getCustomerDevices($customer_id) {
        $sql = "SELECT d.*, u.username as technician_name
                FROM devices d
                LEFT JOIN users u ON d.technician_id = u.id
                WHERE d.customer_id = ?
                ORDER BY d.received_date DESC";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $customer_id);
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_get_result($stmt);
    }
    
    // Get Technician Workload
    public function getTechnicianWorkload($technician_id) {
        $sql = "SELECT 
                COUNT(CASE WHEN status = 'Received' THEN 1 END) as received,
                COUNT(CASE WHEN status = 'In Progress' THEN 1 END) as in_progress,
                COUNT(CASE WHEN status = 'Completed' THEN 1 END) as completed
                FROM devices 
                WHERE technician_id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $technician_id);
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_get_result($stmt);
    }
    
    // Get System Statistics
    public function getSystemStats() {
        $stats = array();
        
        // Total devices
        $sql = "SELECT COUNT(*) as total FROM devices";
        $result = mysqli_query($this->conn, $sql);
        $stats['total_devices'] = mysqli_fetch_assoc($result)['total'];
        
        // Devices by status
        $sql = "SELECT status, COUNT(*) as count FROM devices GROUP BY status";
        $result = mysqli_query($this->conn, $sql);
        $stats['status_counts'] = array();
        while($row = mysqli_fetch_assoc($result)) {
            $stats['status_counts'][$row['status']] = $row['count'];
        }
        
        // Total customers
        $sql = "SELECT COUNT(*) as total FROM customers";
        $result = mysqli_query($this->conn, $sql);
        $stats['total_customers'] = mysqli_fetch_assoc($result)['total'];
        
        // Total technicians
        $sql = "SELECT COUNT(*) as total FROM users WHERE role = 'technician'";
        $result = mysqli_query($this->conn, $sql);
        $stats['total_technicians'] = mysqli_fetch_assoc($result)['total'];
        
        return $stats;
    }
}

// Initialize DataFlow
$dataFlow = new DataFlow($conn);
?> 