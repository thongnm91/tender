<?php
// signup_process.php
require 'db_connection.php';  // Include your DB connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $user_type = $_POST['user_type'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $email = $_POST['email'];

    // Check if the email already exists
    $stmt = $conn->prepare("SELECT * FROM User WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Email is already registered!";
    } else {
        // Insert new user data into the database
        $stmt = $conn->prepare("INSERT INTO User (name, address, user_type, password, email) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $address, $user_type, $password, $email);

        if ($stmt->execute()) {
            echo "Signup successful!";
            header("Location: login.html"); // Redirect to login page
        } else {
            echo "Error: " . $stmt->error;
        }
    }
}
?>
