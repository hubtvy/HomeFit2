<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login page
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "homefit"; // Make sure this matches your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$exercise_id = filter_input(INPUT_POST, 'exercise_id', FILTER_VALIDATE_INT);

if (!$exercise_id) {
    echo "Invalid exercise ID.";
    exit();
}

$sql = "DELETE FROM user_exercise WHERE exercise_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo "Error preparing statement: " . $conn->error;
    exit();
}

$stmt->bind_param("ii", $exercise_id, $user_id);

if ($stmt->execute()) {
    echo "Record deleted successfully";
} else {
    echo "Error deleting record: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
