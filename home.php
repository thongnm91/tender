<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: index.html');
    exit;
}
else {
// Get user details from session
$userid = $_SESSION['user_id'];
$email = $_SESSION['email'];
$user_type = $_SESSION['user_type'];
$status = $_SESSION['loggedin'];
}
//echo $_SESSION['loggedin']; 

$userType = $_SESSION['user_type'] ?? '';
$user_id = $_SESSION['user_id'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tender Portal</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <!-- Header Navigation -->
    <header class="main-header">
        <nav class="navbar">
            <div class="brand">Tender Portal</div>
            <ul class="nav-links">
                <li> <a class="active" href="#">Home</a></li>
               <!--<?php if($user_type == 'city'): ?>
                <li> <a href="https://www.cc.puv.fi/~e2400569/tender_form.html" class="btn-create">Create Tender</a></li>
				 <li><a href="https://www.cc.puv.fi/~e2400569/update_tender.html" class="btn-create">Update Tender</a></li>
				 <li><a href="https://www.cc.puv.fi/~e2400569/delete_tender.php" class="btn-create">Delete Tender</a></li>
                <?php endif; ?>-->       
            </ul>
                <div class="login-box">
                    <a href="logout.php" class="btn-primary">Logout</a>
                </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="container">
        <section class="welcome-banner">
            <h1>Welcome to Tender Portal</h1>
            <p>Hello <?php echo "$email on group $user_type" ; ?></p>
        </section>

        <!-- Browse Tender Section -->
        <section class="search-section">
            <h2>Search Tenders</h2>
            <form action="browse_tender_process.php" method="POST" class="search-form">
                <input type="text" name="search" placeholder="Search by tender name or ID..." required>
                <button type="submit" class="btn-primary">Search</button>
            </form>
        </section>

        <!-- Tender List Table -->
        <section class="tender-list">
            <div class="table-responsive">
                <?php if($user_type == 'city'): ?>
                <button class="create-btn" onclick="openModal('createTenderModal')">Create New Tender</button>
                <?php endif; ?>
                
                <table class="tender-table" style="display: none;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tender Name</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Estimated Price</th>
                            <th>Closing Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tender-data">
                        <!-- Tender data will be loaded here -->
                    </tbody>
                </table>
            </div>
        </section>

<!-- Create Tender Modal -->
<div id="createTenderModal" class="modal">
    <div class="modal-content">
        <h2>Create New Tender</h2>
        <form id="createTenderForm">

            <label for="tenderName">Tender Name:</label>
            <input type="text" id="tenderName" name="tender_name" >
            
            <label for="tenderDate">Tender Date:</label>
            <input disabled type="date" id="tenderDate" name="tender_date" >

            <label for="businessCategory">Business Category:</label>
            <input type="text" id="businessCategory" name="business_category" >
            
            <label for="tenderDescription">Tender Description:</label>
            <textarea id="tenderDescription" name="tender_description" ></textarea>
            
            <label for="constructionTerm">Construction Term:</label>
            <input type="text" id="constructionTerm" name="construction_term" >
            
            <label for="estimatedPrice">Estimated Price:</label>
            <input type="number" step="0.01" id="estimatedPrice" name="estimated_price" >
            
            <label for="tenderStartDate">Tender Start Date:</label>
            <input type="date" id="tenderStartDate" name="tender_start_date" >
            
            <label for="tenderCloseDate">Tender Close Date:</label>
            <input type="date" id="tenderCloseDate" name="tender_close_date" >
            
            <label for="winnerDisclosureDate">Winner Disclosure Date:</label>
            <input type="date" id="winnerDisclosureDate" name="winner_disclosure_date" >
            
            <button type="submit" class="create-btn">Create</button>
            <button type="button" class="cancel-btn" onclick="closeModal('createTenderModal')">Cancel</button>
        </form>
    </div>
</div>

<!-- Edit Tender Modal -->
<div id="editTenderModal" class="modal">
    <div class="modal-content">
        <h2>Edit Tender</h2>
        <form id="editTenderForm">
            <label for="tenderID">Tender ID:</label>
            <input readonly type="text" id="etenderID" name="id">
            
            <label for="tenderName">Tender Name:</label>
            <input type="text" id="etenderName" name="tender_name" >
            
            <label for="tenderDate">Tender Date:</label>
            <input type="date" id="etenderDate" name="tender_date" >

            <label for="estimatedPrice">Estimated Price:</label>
            <input type="number" step="0.01" id="eestimatedPrice" name="estimated_price" >

            <label for="businessCategory">Business Category:</label>
            <input type="text" id="ebusinessCategory" name="business_category" >
            
            <label for="tenderDescription">Tender Description:</label>
            <textarea id="etenderDescription" name="tender_description" ></textarea>
            
            <label for="constructionTerm">Construction Term:</label>
            <input type="text" id="econstructionTerm" name="construction_term" >
            
            <label for="tenderStartDate">Tender Start Date:</label>
            <input type="date" id="etenderStartDate" name="tender_start_date" >
            
            <label for="tenderCloseDate">Tender Close Date:</label>
            <input type="date" id="etenderCloseDate" name="tender_close_date" >
            
            <label for="winnerDisclosureDate">Winner Disclosure Date:</label>
            <input type="date" id="ewinnerDisclosureDate" name="winner_disclosure_date" >
            
            <button type="submit" class="create-btn">Confirm</button>
            <button type="button" class="cancel-btn" onclick="closeModal('editTenderModal')">Cancel</button>
        </form>
    </div>
</div>

    </div>

    <!-- Bid Modal -->
    <div id="bidModal" class="modal">
        <div class="modal-content">
            <h2>Submit Bid</h2>
            <form id="bidForm">
                <label for="tenderId">Tender ID:</label>
                <input type="text" id="tenderId" name="tender_id" readonly>
                
                <label for="bidAmount">Bid Amount:</label>
                <input type="number" id="bidAmount" step="0.01" required>
                
                <label for="bidProposal">Proposal Details:</label>
                <textarea id="bidProposal" rows="5" required></textarea>
                
                <button type="submit" class="bid-btn">Submit Bid</button>
                <button type="button" class="cancel-btn" onclick="closeModal('bidModal')">Cancel</button>
            </form>
        </div>
    </div>

    <!-- Detail Modal -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeDetailModal()">&times;</span>
            <h2>Tender Details</h2>
            <div class="tender-details">
                <h3 id="detailTenderName"></h3>
                <p>Category: <span id="detailTenderCategory"></span></p>
                <p>Description: <span id="detailTenderDescription"></span></p>
                <p>Estimated Price: <span id="detailEstimatedPrice"></span></p>
                <p>Start Date: <span id="detailStartDate"></span></p>
                <p>Close Date: <span id="detailCloseDate"></span></p>
                <p>Construction Term: <span id="detailConstructionTerm"></span></p>
                <p>Winner Disclosure Date: <span id="detailWinnerDisclosureDate"></span></p>
            </div>
            
            <?php if($user_type == 'company'): ?>
            <div class="bids-section">
                <h3>Bids Received</h3>
                <div id="bidsList" class="bids-list">
                    <!-- Bids will be populated here -->
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    </main>

    <footer class="main-footer">
        <p>Thong-e2301482 - Tender Project - Agile Software Development</p>
    </footer>

    <script>
        // Pass PHP variables to JavaScript
        const userType = '<?php echo $userType; ?>';
        const user_id = '<?php echo $user_id; ?>';
    </script>
    <script src="home.js"></script>
</body>
</html>