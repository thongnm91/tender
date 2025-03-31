<?php
include 'db_connection.php';
session_start();

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);// Ensure associative array
//system.log($input);

switch ($method) {
    case 'GET':
	// if (!isset($_SESSION['loggedin'])) {
            // echo json_encode(["error" => "unauthorized access"]);
            // exit;
        // }
        // Check if this is a bid fetching request
        if (isset($_GET['action']) && $_GET['action'] === 'get_bids' && isset($_GET['tender_id'])) {
            $tender_id = $_GET['tender_id'];
            $stmt = $conn->prepare("SELECT * FROM bids WHERE tender_id = ?");
            if (!$stmt) {
                echo json_encode(["error" => "Prepare failed: " . $conn->error]);
                exit;
            }
            
            $stmt->bind_param("i", $tender_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $bids = [];
            while ($row = $result->fetch_assoc()) {
                $bids[] = $row;
            }
            
            echo json_encode($bids);
            $stmt->close();
            exit;
        }
        
        // Handle regular tender fetching
        if (isset($_GET['id'])) {
            $tender_id = $_GET['id'];
            $result = $conn->query("SELECT * FROM tender_1 WHERE id=$tender_id");
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
		$stmt->close();
        break;
//----------------------------------------------
    case 'POST':
        if (!isset($_SESSION['loggedin'])) {
            http_response_code(401);
            echo json_encode(["error" => "Unauthorized access"]);
            exit;
        }

        // Get JSON data from request body
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        // Check if this is a bid submission
        if (isset($data['action']) && $data['action'] === 'submit_bid') {
            // Validate required bid fields
            if (!isset($data['tender_id']) || !isset($data['bid_amount']) || !isset($data['notes'])) {
                error_log("Missing required fields");
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }

            try {
                // Check if tender exists and is open (based on dates)
                $tender_query = "SELECT * FROM tender_1 WHERE id = ?";
                error_log("Checking tender query: " . $tender_query);
                error_log("Tender ID: " . $data['tender_id']);
                
                $tender_stmt = $conn->prepare($tender_query);
                if (!$tender_stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                
                $tender_stmt->bind_param("i", $data['tender_id']);
                $tender_stmt->execute();
                $tender_result = $tender_stmt->get_result();
                
                if ($tender_result->num_rows === 0) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Tender not found']);
                    exit;
                }

                $tender = $tender_result->fetch_assoc();
                
                if ($data['submission_date'] < $tender['tender_start_date']) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Tender has not started yet. Start date: ' . $tender['tender_start_date']]);
                    exit;
                }
                
                if ($data['submission_date'] > $tender['tender_close_date']) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Tender has already closed. Close date: ' . $tender['tender_close_date']]);
                    exit;
                }
                
                // Check if company has already bid on this tender
                $check_query = "SELECT * FROM bids WHERE tender_id = ? AND company_id = ?";
                $check_stmt = $conn->prepare($check_query);
                if (!$check_stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                
                $check_stmt->bind_param("ii", $data['tender_id'], $_SESSION['user_id']);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    http_response_code(400);
                    echo json_encode(['error' => 'You have already submitted a bid for this tender']);
                    exit;
                }
                
                // Insert the bid
                $sql = "INSERT INTO bids (tender_id, company_id, bid_amount, notes, submission_date) 
                        VALUES (?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }

                $stmt->bind_param("iidss", 
                    $data['tender_id'],
                    $_SESSION['user_id'],
                    $data['bid_amount'],
                    $data['notes'],
                    $data['submission_date']
                );

                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Bid submitted successfully']);
                } else {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
            } catch (Exception $e) {
                error_log("Error in bid submission: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Failed to submit bid: ' . $e->getMessage()]);
            }
        } 
        // Handle tender creation
        else if (isset($data['action']) && $data['action'] === 'create_tender') {
            // Validate required fields
            if (!isset($data['tender_name']) || !isset($data['tender_description']) || 
                !isset($data['estimated_price']) || !isset($data['tender_start_date']) || 
                !isset($data['tender_close_date'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }

            try {
                // Insert the tender
                $sql = "INSERT INTO tender_1 (tender_name, tender_date, business_category, tender_description, 
                        construction_term, estimated_price, tender_start_date, tender_close_date, 
                        winner_disclosure_date) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }

                $stmt->bind_param("ssssdssss", 
                    $data['tender_name'],
                    $data['tender_date'],
                    $data['business_category'],
                    $data['tender_description'],
                    $data['construction_term'],
                    $data['estimated_price'],
                    $data['tender_start_date'],
                    $data['tender_close_date'],
                    $data['winner_disclosure_date']
                );

                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Tender created successfully']);
                } else {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
            } catch (Exception $e) {
                error_log("Error in tender creation: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Failed to create tender: ' . $e->getMessage()]);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
        }
        break;
//---------------------------------
	case 'DELETE':
    if (!isset($_SESSION['loggedin'])) {
        echo json_encode(["error" => "Unauthorized access"]);
        exit;
    }

    if (!isset($_GET['id'])) {
        echo json_encode(["error" => "Missing tender ID"]);
        exit;
    }

    $tender_id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM tender_1 WHERE id = ?");
    $stmt->bind_param("s", $tender_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(["message" => "Tender deleted successfully"]);
    } else {
        echo json_encode(["error" => "Delete failed: Tender ID not found"]);
    }
	
    $stmt->close();
    break;
	
//-----------------------------
	case 'PUT':
    if (!isset($_SESSION['loggedin'])) {
        echo json_encode(["error" => "Unauthorized access"]);
        exit;
    }

    // Get tender_id from input
    if (!isset($input['id'])) {
        echo json_encode(["error" => "Tender ID is required"]);
        exit;
    }

    $tender_id = $input['id'];
    
    // Fields in the database
    $fields = [
        'tender_name', 'tender_date', 'business_category',
        'tender_description', 'construction_term', 'estimated_price',
        'tender_start_date', 'tender_close_date', 'winner_disclosure_date',
        'winner_name', 'register_company_number', 'tender_price'
    ];

    // Create an array to store values and placeholders
    $values = [];
    $placeholders = [];
    $types = '';

    // Prepare placeholders and values dynamically
    foreach ($fields as $field) {
        if (isset($input[$field])) {
            $values[] = $input[$field];
            $placeholders[] = "$field = ?";

            // Determine the type for bind_param
            if (in_array($field, ['estimated_price', 'tender_price'])) {
                $types .= 'd'; // Decimal
            } else {
                $types .= 's'; // String
            }
        }
    }

    // If no fields to update
    if (empty($placeholders)) {
        echo json_encode(["error" => "No fields to update"]);
        exit;
    }

    // Add tender_id to values array for WHERE clause
    $values[] = $tender_id;
    $types .= 's'; // Add type for tender_id

    // Construct the query
    $sql = "UPDATE tender_1 SET " . implode(", ", $placeholders) . " WHERE id = ?";
    
    // Debug log
    error_log("SQL Query: " . $sql);
    error_log("Types: " . $types);
    error_log("Values: " . print_r($values, true));
    
    // Prepare statement
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        echo json_encode(["error" => "Prepare failed: " . $conn->error]);
        exit;
    }

    // Bind parameters
    if (!empty($values)) {
        $stmt->bind_param($types, ...$values);
    }

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["message" => "Tender updated successfully"]);
        } else {
            // Check if the tender exists
            $check_stmt = $conn->prepare("SELECT id FROM tender_1 WHERE id = ?");
            $check_stmt->bind_param("s", $tender_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows === 0) {
                echo json_encode(["error" => "Update failed: Tender not found"]);
            } else {
                echo json_encode(["error" => "Update failed: No changes made"]);
            }
            $check_stmt->close();
        }
    } else {
        echo json_encode(["error" => "Execute failed: " . $stmt->error]);
    }

    $stmt->close();
    break;
}
$conn->close();
?>