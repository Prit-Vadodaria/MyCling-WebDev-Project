<?php
// Script to add transaction_id column to orders table
require_once 'php/config.php';

echo "Altering orders table to add transaction_id column...\n";

// Check if the column already exists
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'transaction_id'");
if (mysqli_num_rows($check_column) > 0) {
    echo "Column 'transaction_id' already exists in the orders table.\n";
    exit();
}

// Add the transaction_id column
$alter_query = "ALTER TABLE orders ADD COLUMN transaction_id VARCHAR(100) NULL AFTER payment_method";
if (mysqli_query($conn, $alter_query)) {
    echo "Successfully added 'transaction_id' column to orders table.\n";
} else {
    echo "Error adding 'transaction_id' column: " . mysqli_error($conn) . "\n";
}
?> 