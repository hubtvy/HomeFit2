<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html"); // Redirect to your login page
    exit();
}

$user_id = $_SESSION['user_id'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "homefit";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare and execute SQL statement
$sql = "SELECT * FROM user_exercise WHERE user_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param('i', $user_id);

if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}

$result = $stmt->get_result();
$exercises = [];

while ($row = $result->fetch_assoc()) {
    $exercises[] = [
        'exercise_id' => $row['exercise_id'],
        'workout_name' => $row['workout_name'],
        'workout_page_link' => $row['workout_page_link'],
        'category' => $row['category'],
        'description' => $row['description']
    ];
}

$stmt->close();
$conn->close();

// Output JSON
header('Content-Type: application/json');
echo json_encode($exercises);
?>

