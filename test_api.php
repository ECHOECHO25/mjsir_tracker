<?php
$url = 'http://localhost/mjsir_tracker/backend/public/index.php/api/manuscripts';
$data = array('code' => 'TEST01', 'title' => 'Test Title');

$options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data)
    )
);
$context  = stream_context_create($options);
$result = @file_get_contents($url, false, $context);
if ($result === FALSE) {
    echo "Error:\n";
    print_r(error_get_last());
} else {
    echo "Success:\n";
    echo $result;
}
?>
