<?php
include 'db_connection.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
echo $input;
switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $tender_id = $_GET['id'];
            $result = $conn->query("SELECT * FROM tender_1 WHERE tender_id=$tender_id");
            $data = $result->fetch_assoc();
            echo json_encode($data);
        } else {
			
			// Execute the query
			$result = $conn->query("SELECT * FROM tender_1");
			
			$tenders = [];
			if ($result) {
				while ($row = $result->fetch_assoc()) {
					$tenders[] = $row;
				}
				echo json_encode($tenders);
			
			} else {
				echo json_encode(["error" => $conn->error]); // Output error if query fails
			}
        }
        break;

    case 'POST':
        $name = $input['name'];
        $email = $input['email'];
        $age = $input['age'];
        $conn->query("INSERT INTO users (name, email, age) VALUES ('$name', '$email', $age)");
        echo json_encode(["message" => "User added successfully"]);
        break;

    case 'PUT':
        $id = $_GET['id'];
        $name = $input['name'];
        $email = $input['email'];
        $age = $input['age'];
        $conn->query("UPDATE users SET name='$name',
                     email='$email', age=$age WHERE id=$id");
        echo json_encode(["message" => "User updated successfully"]);
        break;

    case 'DELETE':
        $id = $_GET['id'];
        $conn->query("DELETE FROM users WHERE id=$id");
        echo json_encode(["message" => "User deleted successfully"]);
        break;

    default:
        echo json_encode(["message" => "Invalid request method"]);
        break;
}

$conn->close();
?>