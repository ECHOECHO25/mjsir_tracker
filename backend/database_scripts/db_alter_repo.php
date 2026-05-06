<?php
$mysqli = new mysqli("localhost", "root", "", "mjsir_db");

$columns = [
    'criteria_scope' => 'TINYINT(1) DEFAULT 0',
    'criteria_methodology' => 'TINYINT(1) DEFAULT 0',
    'criteria_format' => 'TINYINT(1) DEFAULT 0',
    'manuscript_type' => 'VARCHAR(100) NULL',
    'turnitin_file' => 'VARCHAR(255) NULL'
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
