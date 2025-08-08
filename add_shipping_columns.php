<?php
// Script to add shipping-related columns to orders table
require_once 'php/config.php';

echo "Altering orders table to add shipping columns...\n";

// Array of columns to add
$columns = [
    'shipping_name' => 'VARCHAR(100)',
    'shipping_email' => 'VARCHAR(100)',
    'shipping_phone' => 'VARCHAR(20)',
    'shipping_city' => 'VARCHAR(50)',
    'shipping_state' => 'VARCHAR(50)',
    'shipping_zip' => 'VARCHAR(20)'
];

// Check and add each column
foreach ($columns as $column => $type) {
    // Check if the column already exists
    $check_column = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE '$column'");
    
    if (mysqli_num_rows($check_column) > 0) {
        echo "Column '$column' already exists in the orders table.\n";
    } else {
        // Add the column
        $alter_query = "ALTER TABLE orders ADD COLUMN $column $type NULL";
        if (mysqli_query($conn, $alter_query)) {
            echo "Successfully added '$column' column to orders table.\n";
        } else {
            echo "Error adding '$column' column: " . mysqli_error($conn) . "\n";
        }
    }
}

echo "Done!\n";
?> 