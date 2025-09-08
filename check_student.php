<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rfid_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "DB connection failed"]));
}

$rfid = $_GET['rfid'] ?? '';

$response = ["status" => "not_found"];

if ($rfid) {
    $sql = "SELECT * FROM students WHERE rfid = '$rfid'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $response["status"] = "found";
    }
}

$conn->close();
echo json_encode($response);
?>
