<?php
// Script to check the structure of the users table
require_once 'php/config.php';

// Check if the users table exists
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
if (mysqli_num_rows($check_table) == 0) {
    echo "Users table does not exist.\n";
    exit();
}

// Show the structure of the users table
echo "USERS TABLE STRUCTURE:\n";
echo "=====================\n";
$describe_query = mysqli_query($conn, "DESCRIBE users");
if (!$describe_query) {
    echo "Error describing users table: " . mysqli_error($conn) . "\n";
    exit();
}

echo "Field\tType\tNull\tKey\tDefault\tExtra\n";
echo "-----\t----\t----\t---\t-------\t-----\n";
while ($field = mysqli_fetch_assoc($describe_query)) {
    echo "{$field['Field']}\t{$field['Type']}\t{$field['Null']}\t{$field['Key']}\t{$field['Default']}\t{$field['Extra']}\n";
}

// Show a few sample users
echo "\n\nSAMPLE USERS:\n";
echo "=============\n";
$users_query = mysqli_query($conn, "SELECT * FROM users LIMIT 5");
if (!$users_query) {
    echo "Error retrieving users: " . mysqli_error($conn) . "\n";
    exit();
}

if (mysqli_num_rows($users_query) == 0) {
    echo "No users found in the database.\n";
} else {
    $first_row = true;
    while ($user = mysqli_fetch_assoc($users_query)) {
        if ($first_row) {
            // Print column headers
            echo implode("\t", array_keys($user)) . "\n";
            echo str_repeat("-", 80) . "\n";
            $first_row = false;
        }
        echo implode("\t", array_values($user)) . "\n";
    }
}
?> 