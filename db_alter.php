<?php
$mysqli = new mysqli("localhost", "root", "", "mjsir_db");

$columns = [
    'link' => 'VARCHAR(255) NULL',
    'aiWriting' => 'VARCHAR(50) NULL',
    'srev1_affil' => 'VARCHAR(255) NULL',
    'srev1_email' => 'VARCHAR(100) NULL',
    'srev2_name' => 'VARCHAR(255) NULL',
    'srev2_affil' => 'VARCHAR(255) NULL',
    'srev2_email' => 'VARCHAR(100) NULL',
    'srev3_name' => 'VARCHAR(255) NULL',
    'srev3_affil' => 'VARCHAR(255) NULL',
    'srev3_email' => 'VARCHAR(100) NULL'
];

foreach ($columns as $col => $type) {
    // Check if exists
    $res = $mysqli->query("SHOW COLUMNS FROM manuscripts LIKE '$col'");
    if ($res->num_rows == 0) {
        if ($mysqli->query("ALTER TABLE manuscripts ADD $col $type")) {
            echo "Added column $col\n";
        } else {
            echo "Error adding $col: " . $mysqli->error . "\n";
        }
    } else {
        echo "Column $col already exists\n";
    }
}

$mysqli->close();
?>
