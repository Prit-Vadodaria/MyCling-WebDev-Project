<?php
// Script to check the structure of the orders table
require_once 'php/config.php';

// Check if the orders table exists
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'orders'");
if (mysqli_num_rows($check_table) == 0) {
    echo "Orders table does not exist.\n";
    exit();
}

// Show the structure of the orders table
echo "ORDERS TABLE STRUCTURE:\n";
echo "=======================\n\n";

$describe_query = mysqli_query($conn, "DESCRIBE orders");
if (!$describe_query) {
    echo "Error describing orders table: " . mysqli_error($conn) . "\n";
    exit();
}

// Collect all fields
$fields = [];
while ($field = mysqli_fetch_assoc($describe_query)) {
    $fields[] = $field;
}

// Print the field details (simpler approach)
var_dump($fields);
?> 