<?php
// search_tender.php
require 'db_connection.php';

$query = $_GET['query'] ?? '';

if ($query) {
    $stmt = $conn->prepare("SELECT * FROM Tender WHERE tender_name LIKE ? OR tender_description LIKE ?");
    $searchTerm = "%" . $query . "%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($tender = $result->fetch_assoc()) {
            echo "<li><strong>{$tender['tender_name']}</strong><p>{$tender['tender_description']}</p></li>";
        }
    } else {
        echo "<li>No tenders found.</li>";
    }
}
?>
