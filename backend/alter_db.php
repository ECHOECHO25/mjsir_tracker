<?php
$conn = new mysqli("localhost", "root", "", "mjsir_db");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$conn->query("ALTER TABLE manuscripts ADD COLUMN revised_file VARCHAR(255) NULL");
echo "Done";
