<?php
$mysqli = new mysqli("localhost", "root", "", "mjsir_db");

$columns = [
    'final_decision' => 'VARCHAR(255) NULL',
    'date_accepted' => 'VARCHAR(50) NULL',
    'date_revised' => 'VARCHAR(50) NULL'
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
