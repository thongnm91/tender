<?php
session_start();


// Now we check if the data from the login form was submitted, isset() will check if the data exists
if (!isset($_POST['email'], $_POST['password'])) {
    // Could not get the data that should have been sent
    exit('Please fill both the username and password fields!');
}
else {
	//echo "abc";
	require 'db_connection.php'; // Database connection file
	
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query to select the user from the database
    $stmt = $conn->prepare("SELECT * FROM User WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
	
	// // Check if user exists and password is correct
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if ($_POST['password'] === $password) {
            $_SESSION['name'] = $user['name'];
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['password'] = $user['password'];
            $_SESSION['loggedin'] = true;
            
            header("Location: home.php");
            exit;
        }
		else {
			echo "Can't not fetch";
		}
	}
	else {
		header("Location: index.html");
		exit;
	}
}
?>