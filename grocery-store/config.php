<?php
// Database configuration
$host = 'sql210.infinityfree.com';
$username = 'if0_39904111';
$password = 'GroceryStore457';
$database = 'if0_39904111_grocery_store';

try {
    // Create connection without database selection first
    $conn = new mysqli($host, $username, $password);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to UTF-8
    $conn->set_charset("utf8");
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS `$database` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci";
    if (!$conn->query($sql)) {
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    // Select the database
    if (!$conn->select_db($database)) {
        throw new Exception("Error selecting database: " . $conn->error);
    }
    
    // Create products table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS `products` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `price` DECIMAL(10,2) NOT NULL,
        `image` VARCHAR(255) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
    
    if (!$conn->query($sql)) {
        throw new Exception("Error creating table: " . $conn->error);
    }
    
    // Create contacts table for storing contact form submissions
    $sql = "CREATE TABLE IF NOT EXISTS `contacts` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `email` VARCHAR(255) NOT NULL,
        `message` TEXT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
    
    if (!$conn->query($sql)) {
        throw new Exception("Error creating contacts table: " . $conn->error);
    }
    
    // Insert sample products (only if table is empty)
    $count_sql = "SELECT COUNT(*) as count FROM products";
    $count_result = $conn->query($count_sql);
    $count_row = $count_result->fetch_assoc();
    
    if ($count_row['count'] == 0) {
        $sample_products = [
            ['Apple', 0.50, 'apple.jpg'],
            ['Banana', 0.30, 'banana.jpg'],
            ['Orange', 0.75, 'orange.jpg'],
            ['Milk', 2.99, 'milk.jpg'],
            ['Bread', 1.50, 'bread.jpg'],
            ['Eggs', 3.25, 'eggs.jpg']
        ];
        
        $insert_sql = "INSERT INTO products (name, price, image) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        
        foreach ($sample_products as $product) {
            $stmt->bind_param("sds", $product[0], $product[1], $product[2]);
            $stmt->execute();
        }
        
        $stmt->close();
    }
    
} catch (Exception $e) {
    // Log the error
    error_log("Database configuration error: " . $e->getMessage());
    
    // Die with a user-friendly message
    die("Database connection failed. Please check your configuration.");
}

// Connection is now ready to use in other files
// $conn variable will be available for database operations
?>
