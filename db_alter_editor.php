<?php
$mysqli = new mysqli("localhost", "root", "", "mjsir_db");

$columns = [
    'editor_comments' => 'TEXT NULL',
    'editor_remarks' => 'VARCHAR(255) NULL',
    'prescreen_status' => 'VARCHAR(50) NULL',
    'action_taken' => 'VARCHAR(255) NULL'
];

foreach ($columns as $col => $type) {
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
