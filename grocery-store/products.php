<?php
// Set JSON header and prevent caching
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Include database configuration
    include 'config.php';
    
    // Check if connection exists
    if (!isset($conn)) {
        throw new Exception('Database connection not established');
    }
    
    // Query all products from database with error handling
    $sql = "SELECT id, name, price, image FROM products ORDER BY id ASC";
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception('Query failed: ' . $conn->error);
    }
    
    $products = array();
    
    // Fetch associative array
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Ensure data types are correct
            $products[] = array(
                'id' => (int)$row['id'],
                'name' => $row['name'],
                'price' => number_format((float)$row['price'], 2, '.', ''),
                'image' => $row['image']
            );
        }
    }
    
    // Encode and output JSON
    echo json_encode($products, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    // Log error and return empty array
    error_log("Error in products.php: " . $e->getMessage());
    
    // Return empty array on error
    http_response_code(500);
    echo json_encode(['error' => 'Unable to fetch products'], JSON_PRETTY_PRINT);
    
} finally {
    // Close connection if it exists
    if (isset($conn)) {
        $conn->close();
    }
}
?>