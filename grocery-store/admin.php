<?php
// Include database configuration
include 'config.php';

$message = '';
$message_type = '';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Validate and sanitize input data
        $name = trim(htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8'));
        $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
        $image = trim(htmlspecialchars($_POST['image'], ENT_QUOTES, 'UTF-8'));
        
        // Validation
        if (empty($name)) {
            throw new Exception('Product name is required');
        }
        
        if ($price === false || $price < 0) {
            throw new Exception('Valid price is required');
        }
        
        if (empty($image)) {
            throw new Exception('Image filename is required');
        }
        
        // Insert into products table using prepared statement
        $sql = "INSERT INTO products (name, price, image) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        $stmt->bind_param("sds", $name, $price, $image);
        
        if ($stmt->execute()) {
            $message = 'Product added successfully!';
            $message_type = 'success';
        } else {
            throw new Exception('Execute failed: ' . $stmt->error);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - FreshMart Grocery</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .message {
            max-width: 600px;
            margin: 1rem auto;
            padding: 1rem;
            border-radius: 4px;
            text-align: center;
            font-weight: bold;
        }
        .success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>
    <header>
        <h1 class="fade-in">Admin Panel - FreshMart Grocery</h1>
        <nav>
            <a href="index.html">← Back to Store</a>
        </nav>
    </header>
    
    <main class="admin-container">
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <!-- Product Addition Form -->
        <section>
            <h2>Add New Product</h2>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="admin-form">
                <input type="text" 
                       name="name" 
                       placeholder="Product Name (e.g., Red Apples)" 
                       required 
                       maxlength="255">
                
                <input type="number" 
                       name="price" 
                       step="0.01" 
                       min="0" 
                       placeholder="Price (e.g., 2.99)" 
                       required>
                
                <input type="text" 
                       name="image" 
                       placeholder="Image filename (e.g., apple.jpg)" 
                       required 
                       maxlength="255">
                
                <button type="submit">Add Product</button>
            </form>
        </section>
        
        <!-- Products Table -->
        <section>
            <h2>Current Products</h2>
            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Image</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            // Query all products with error handling
                            $sql = "SELECT id, name, price, image, created_at FROM products ORDER BY id DESC";
                            $result = $conn->query($sql);
                            
                            if (!$result) {
                                throw new Exception('Query failed: ' . $conn->error);
                            }
                            
                            // Display products in table rows
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
                                    echo "<td>$" . number_format($row["price"], 2) . "</td>";
                                    echo "<td>";
                                    echo "<img src='images/" . htmlspecialchars($row["image"]) . "' ";
                                    echo "width='50' height='50' ";
                                    echo "alt='" . htmlspecialchars($row["name"]) . "' ";
                                    echo "onerror=\"this.src='images/placeholder.jpg'\" />";
                                    echo "<br><small>" . htmlspecialchars($row["image"]) . "</small>";
                                    echo "</td>";
                                    echo "<td>" . date('M j, Y', strtotime($row["created_at"])) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>No products found</td></tr>";
                            }
                        } catch (Exception $e) {
                            echo "<tr><td colspan='5'>Error loading products: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
        
        <!-- Contact Messages -->
        <section>
            <h2>Recent Contact Messages</h2>
            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Message</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            // Query recent contact messages
                            $sql = "SELECT id, name, email, message, created_at FROM contacts ORDER BY id DESC LIMIT 10";
                            $result = $conn->query($sql);
                            
                            if ($result && $result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                                    echo "<td>" . htmlspecialchars(substr($row["message"], 0, 100)) . 
                                         (strlen($row["message"]) > 100 ? "..." : "") . "</td>";
                                    echo "<td>" . date('M j, Y g:i A', strtotime($row["created_at"])) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>No contact messages yet</td></tr>";
                            }
                        } catch (Exception $e) {
                            echo "<tr><td colspan='5'>Error loading messages: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
    
    <footer>
        <p>&copy; 2025 FreshMart Grocery Admin Panel. All rights reserved.</p>
    </footer>
</body>
</html>
<?php
// Close connection
if (isset($conn)) {
    $conn->close();
}
?>