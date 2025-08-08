<?php
// Script to check the structure of the products table
require_once 'php/config.php';

// Check if the products table exists
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'products'");
if (mysqli_num_rows($check_table) == 0) {
    echo "Products table does not exist.\n";
    exit();
}

// Show the structure of the products table
echo "PRODUCTS TABLE STRUCTURE:\n";
echo "========================\n";
$describe_query = mysqli_query($conn, "DESCRIBE products");
if (!$describe_query) {
    echo "Error describing products table: " . mysqli_error($conn) . "\n";
    exit();
}

echo "Field\tType\tNull\tKey\tDefault\tExtra\n";
echo "-----\t----\t----\t---\t-------\t-----\n";
while ($field = mysqli_fetch_assoc($describe_query)) {
    echo "{$field['Field']}\t{$field['Type']}\t{$field['Null']}\t{$field['Key']}\t{$field['Default']}\t{$field['Extra']}\n";
}

// Show a few sample products
echo "\n\nSAMPLE PRODUCTS:\n";
echo "===============\n";
$products_query = mysqli_query($conn, "SELECT * FROM products LIMIT 5");
if (!$products_query) {
    echo "Error retrieving products: " . mysqli_error($conn) . "\n";
    exit();
}

if (mysqli_num_rows($products_query) == 0) {
    echo "No products found in the database.\n";
} else {
    $first_row = true;
    while ($product = mysqli_fetch_assoc($products_query)) {
        if ($first_row) {
            // Print column headers
            echo implode("\t", array_keys($product)) . "\n";
            echo str_repeat("-", 80) . "\n";
            $first_row = false;
        }
        echo implode("\t", array_values($product)) . "\n";
    }
}
?> 