<?php
// Include database configuration
include 'config.php';

// Set content type
header('Content-Type: text/plain');

try {
    // Check if POST request
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        http_response_code(405);
        throw new Exception('Method not allowed');
    }
    
    // Validate and sanitize input data
    $name = trim(htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8'));
    $email = trim(htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'));
    $message = trim(htmlspecialchars($_POST['message'] ?? '', ENT_QUOTES, 'UTF-8'));
    
    // Validation
    if (empty($name)) {
        throw new Exception('Name is required');
    }
    
    if (empty($email)) {
        throw new Exception('Email is required');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Valid email address is required');
    }
    
    if (empty($message)) {
        throw new Exception('Message is required');
    }
    
    // Length validation
    if (strlen($name) > 255) {
        throw new Exception('Name is too long (max 255 characters)');
    }
    
    if (strlen($email) > 255) {
        throw new Exception('Email is too long (max 255 characters)');
    }
    
    if (strlen($message) > 5000) {
        throw new Exception('Message is too long (max 5000 characters)');
    }
    
    // Insert into contacts table
    $sql = "INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Database prepare failed');
    }
    
    $stmt->bind_param("sss", $name, $email, $message);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to save message');
    }
    
    $stmt->close();
    
    // Success response
    echo 'Thank you for your message! We will get back to you soon.';
    
} catch (Exception $e) {
    // Error response
    http_response_code(400);
    echo 'Error: ' . $e->getMessage();
    
    // Log error for debugging
    error_log("Contact form error: " . $e->getMessage());
    
} finally {
    // Close connection if it exists
    if (isset($conn)) {
        $conn->close();
    }
}
?>