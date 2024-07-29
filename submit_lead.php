<?php
// Database configuration
$db_server = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "leads_db";

// Enable exceptions for MySQLi
mysqli_report(MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($db_server, $db_user, $db_pass, $db_name);
} catch (mysqli_sql_exception $e) {
    echo "Could not connect: " . $e->getMessage() . "<br>";
    exit;
}

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
