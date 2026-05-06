<?php
$mysqli = new mysqli("localhost", "root", "", "mjsir_db");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Create users table
$tableQuery = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL,
    failed_attempts INT DEFAULT 0,
    locked_until DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";

if ($mysqli->query($tableQuery) === TRUE) {
    echo "Users table created successfully.\n";
} else {
    echo "Error creating table: " . $mysqli->error . "\n";
}

// Insert default EIC account
$passwordHash = password_hash("Admin123!", PASSWORD_DEFAULT);
$insertQuery = "INSERT IGNORE INTO users (name, email, password, role) VALUES ('Chief Editor', 'eic@mjsir.com', '$passwordHash', 'eic')";

if ($mysqli->query($insertQuery) === TRUE) {
    echo "Default EIC account created: eic@mjsir.com / Admin123!\n";
} else {
    echo "Error inserting EIC: " . $mysqli->error . "\n";
}

$mysqli->close();
?>
