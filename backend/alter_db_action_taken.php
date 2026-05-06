<?php
$conn = new mysqli("localhost", "root", "", "mjsir_db");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$sql = "ALTER TABLE manuscripts 
        ADD COLUMN action_taken_file VARCHAR(255) DEFAULT NULL,
        ADD COLUMN action_taken_link TEXT DEFAULT NULL";

if ($conn->query($sql) === TRUE) {
    echo "Columns added successfully\n";
} else {
    echo "Error adding columns: " . $conn->error . "\n";
}
$conn->close();
