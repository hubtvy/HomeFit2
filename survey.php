<?php
session_start(); // Start the session

$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "homefit";

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user_id is set in session
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. User ID is not set in the session.']);
    exit();
}

$user_id = $_SESSION['user_id']; // Get user_id from session

// Get survey form data
$age = $_POST['age'] ?? '';
$weight = $_POST['weight'] ?? '';
$height = $_POST['height'] ?? '';
$fitness_goal = $_POST['fitness_goal'] ?? '';
$health = $_POST['health'] ?? '';
$gym = $_POST['gym'] ?? '';

// Validate inputs
if (empty($age) || empty($weight) || empty($height) || empty($fitness_goal) || empty($health) || empty($gym)) {
    echo json_encode(['status' => 'error', 'message' => 'Please fill in all survey fields.']);
    exit();
}

// Insert survey data into survey table
$stmt = $conn->prepare("INSERT INTO survey (user_id, age, weight, height, fitness_goal, health, gym) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iiissss", $user_id, $age, $weight, $height, $fitness_goal, $health, $gym);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Survey submitted successfully!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to submit survey: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
