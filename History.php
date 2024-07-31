<?php
session_start();

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

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html"); // Redirect to your login page
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch records for the logged-in user
$sql = "SELECT workout_name, description, category, workout_page_link, date_added 
        FROM user_exercise 
        WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Output data of each row
    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['workout_name']}</td>
                <td>{$row['description']}</td>
                <td>{$row['category']}</td>
                <td><a href='{$row['workout_page_link']}' target='_blank'>Link</a></td>
                <td>{$row['date_added']}</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='5'>No records found</td></tr>";
}
$stmt->close();
$conn->close();
?>

