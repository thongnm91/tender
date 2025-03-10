<?php
session_start();
require 'db_connection.php'; // Database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query to select the user from the database
    $stmt = $conn->prepare("SELECT * FROM User WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists and password is correct
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify password
		echo $user['email'];
		echo $user['password'];
        if ($password == $user['password']) {
            // Store user info in session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['address'] = $user['address'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['lock_status'] = $user['lock_status'];
            $_SESSION['email'] = $user['email'];

            // Redirect to the profile page after successful login
            header("Location: profile.php");
            exit;
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No user found with that email.";
    }
}
?>
