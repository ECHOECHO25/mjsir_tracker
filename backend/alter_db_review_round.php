<?php
$conn = new mysqli("localhost", "root", "", "mjsir_db");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$sql = "ALTER TABLE manuscripts ADD COLUMN review_round INT DEFAULT 1";
if ($conn->query($sql) === TRUE) {
    echo "Column review_round added successfully\n";
} else {
    echo "Error adding column: " . $conn->error . "\n";
}
$conn->close();
