<?php
// login_process.php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Create SQLite connection
    $db = new SQLite3('tender_portal.sqlite3');

    // Prepare the query to check user credentials
    $stmt = $db->prepare('SELECT * FROM users WHERE username = :username');
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $result = $stmt->execute();
    
    $user = $result->fetchArray(SQLITE3_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Successful login
        $_SESSION['user'] = $username;
        header('Location: tenders.html');  // Redirect to tenders page
    } else {
        // Invalid login
        echo "Invalid username or password.";
    }
}
?>
