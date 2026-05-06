<?php
$mysqli = new mysqli("localhost", "root", "", "mjsir_db");

$res = $mysqli->query("SHOW COLUMNS FROM manuscripts LIKE 'suggested_reviewers'");
if ($res->num_rows == 0) {
    if ($mysqli->query("ALTER TABLE manuscripts ADD suggested_reviewers TEXT NULL")) {
        echo "Added column suggested_reviewers\n";
    } else {
        echo "Error adding column: " . $mysqli->error . "\n";
    }
} else {
    echo "Column already exists\n";
}

$mysqli->close();
?>
