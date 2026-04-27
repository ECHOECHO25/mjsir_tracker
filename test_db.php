<?php
$mysqli = new mysqli("localhost", "root", "", "mjsir_db");
$res = $mysqli->query("DESCRIBE manuscripts");
$cols = [];
while ($row = $res->fetch_assoc()) {
    $cols[] = $row['Field'];
}
echo implode(", ", $cols);
?>
