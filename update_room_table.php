<?php
include 'config.php';

// Check and add missing columns to room table
$columns_to_add = [
    'bed' => "VARCHAR(50) DEFAULT 'Single'",
    'price' => "DECIMAL(10,2) DEFAULT 0",
    'room_no' => "VARCHAR(10) DEFAULT NULL",
    'image' => "VARCHAR(255) DEFAULT NULL"
];

// Get existing columns
$result = mysqli_query($conn, "DESCRIBE room");
$existing_columns = [];
while($row = mysqli_fetch_assoc($result)) {
    $existing_columns[] = $row['Field'];
}

// Add missing columns
foreach($columns_to_add as $column => $definition) {
    if(!in_array($column, $existing_columns)) {
        $sql = "ALTER TABLE room ADD COLUMN $column $definition";
        if(mysqli_query($conn, $sql)) {
            echo "Column '$column' added successfully.<br>";
        } else {
            echo "Error adding column '$column': " . mysqli_error($conn) . "<br>";
        }
    } else {
        echo "Column '$column' already exists.<br>";
    }
}

// Update bed column from bedding column if bedding exists
if(in_array('bedding', $existing_columns) && in_array('bed', $existing_columns)) {
    $update_sql = "UPDATE room SET bed = bedding WHERE bed IS NULL OR bed = ''";
    if(mysqli_query($conn, $update_sql)) {
        echo "Bed data copied from bedding column.<br>";
    }
}

echo "<br>Database update completed!<br>";
echo "<a href='home.php'>Go to Home Page</a>";
?>
