<?php
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

session_start();

if (!isset($_SESSION['user_id'])) {
    // Redirect to login or handle unauthorized access
    header("Location: index.html"); // Redirect to your login page
    exit();
}

$user_id = $_SESSION['user_id'];

// Prepare and execute the query to fetch data from both tables
$query = $conn->prepare("
    SELECT u.username, u.gender, u.email, u.year_of_birth, u.profile_image, 
           s.age, s.weight, s.height, s.fitness_goal, s.health, s.gym
    FROM users u
    LEFT JOIN survey s ON u.user_id = s.user_id
    WHERE u.user_id = ?
");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows > 0) {
    $userData = $result->fetch_assoc();
    echo json_encode($userData);
} else {
    echo json_encode(["error" => "User not found"]);
}

$query->close();
$conn->close();
?>
