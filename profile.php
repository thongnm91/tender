<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login page if not logged in
    exit();
}

// Get user details from session
$name = $_SESSION['name'];
$address = $_SESSION['address'];
$user_type = $_SESSION['user_type'];
$lock_status = $_SESSION['lock_status'];
$email = $_SESSION['email'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .menu { display: flex; background-color: #333; padding: 10px; }
        .menu a { color: white; padding: 14px 20px; text-decoration: none; text-align: center; }
        .menu a:hover { background-color: #ddd; color: black; }
        .container { padding: 20px; }
        .profile-info { margin-top: 20px; }
        .profile-info table { width: 100%; border-collapse: collapse; }
        .profile-info th, .profile-info td { padding: 10px; border: 1px solid #ddd; text-align: left; }
    </style>
</head>
<body>

<div class="menu">
    <a href="index.html">Home</a>
    <a href="tenders.html">Browse Tenders</a>
    <a href="logout.php">Logout</a>
</div>

<div class="container">
    <h2>Welcome, <?php echo htmlspecialchars($name); ?></h2>

    <div class="profile-info">
        <h3>Your Profile Information</h3>
        <table>
            <tr>
                <th>Name</th>
                <td><?php echo htmlspecialchars($name); ?></td>
            </tr>
            <tr>
                <th>Address</th>
                <td><?php echo htmlspecialchars($address); ?></td>
            </tr>
            <tr>
                <th>User Type</th>
                <td><?php echo htmlspecialchars($user_type); ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?php echo htmlspecialchars($email); ?></td>
            </tr>
            <tr>
                <th>Lock Status</th>
                <td><?php echo $lock_status == 0 ? 'Unlocked' : 'Locked'; ?></td>
            </tr>
        </table>
    </div>
</div>

</body>
</html>
