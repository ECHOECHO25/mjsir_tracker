<?php
$conn = new mysqli("localhost", "root", "", "mjsir_db");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$sql = "ALTER TABLE manuscripts 
        ADD COLUMN revised_file_2 VARCHAR(255) DEFAULT NULL,
        ADD COLUMN revised_link_2 TEXT DEFAULT NULL,
        ADD COLUMN revised_file_3 VARCHAR(255) DEFAULT NULL,
        ADD COLUMN revised_link_3 TEXT DEFAULT NULL";

if ($conn->query($sql) === TRUE) {
    echo "Columns added successfully\n";
} else {
    echo "Error adding columns: " . $conn->error . "\n";
}
$conn->close();
