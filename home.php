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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tender Portal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Header Navigation -->
    <header class="main-header">
        <nav class="navbar">
            <div class="brand">Tender Portal</div>
            <ul class="nav-links">
                <li> <a class="active" href="#">Home</a></li>
               <?php if($user_type == 'city'): ?>
                <li> <a href="https://www.cc.puv.fi/~e2400569/tender_form.html" class="btn-create">Create Tender</a></li>
				 <li><a href="https://www.cc.puv.fi/~e2400569/update_tender.html" class="btn-create">Update Tender</a></li>
				 <li><a href="https://www.cc.puv.fi/~e2400569/delete_tender.php" class="btn-create">Delete Tender</a></li>
                <?php endif; ?>       
            </ul>
                <div class="login-box">
                    <a href="logout.php" class="btn-primary">Logout</a>
                </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="container">
    <main class="container">
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
                <input type="text" id="tenderName" required>
                
                <label for="noticeDate">Notice Date:</label>
                <input type="date" id="noticeDate" required>
                
                <label for="closingDate">Closing Date:</label>
                <input type="date" id="closingDate" required>
                
                <label for="winnerDisclosure">Winner Disclosure:</label>
                <input type="text" id="winnerDisclosure">
                
                <label for="status">Status:</label>
                <select id="status">
                    <option value="Open">Open</option>
                    <option value="Closed">Closed</option>
                    <option value="Awarded">Awarded</option>
                </select>
                
                <button type="submit" class="create-btn">Create</button>
                <button type="button" class="cancel-btn" onclick="closeModal('createTenderModal')">Cancel</button>
            </form>
        </div>
    </div>

    <!-- Bid Modal -->
    <div id="bidModal" class="modal">
        <div class="modal-content">
            <h2>Submit Bid</h2>
            <form id="bidForm">
                <input type="hidden" id="bidTenderId">
                
                <label for="bidAmount">Bid Amount:</label>
                <input type="number" id="bidAmount" step="0.01" required>
                
                <label for="bidProposal">Proposal Details:</label>
                <textarea id="bidProposal" rows="5" required></textarea>
                
                <button type="submit" class="bid-btn">Submit Bid</button>
                <button type="button" class="cancel-btn" onclick="closeModal('bidModal')">Cancel</button>
            </form>
        </div>
    </div>
    </main>

    <footer class="main-footer">
        <p>Thong-e2301482 - Tender Project - Agile Software Development</p>
    </footer>

    <script>
        // API endpoint
        const API_URL = 'api_tender.php';

        // Function to format price with commas
        function formatPrice(price) {
            if (!price) return '<span class="empty-value">Not set</span>';
            return '$' + parseFloat(price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        // Function to format date
        function formatDate(dateString) {
            if (!dateString) return '<span class="empty-value">Not set</span>';
            const options = { year: 'numeric', month: 'short', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('en-US', options);
        }

        // Function to determine status
        function getStatus(tender) {
            const today = new Date();
            const closeDate = tender.tender_close_date ? new Date(tender.tender_close_date) : null;
            
            if (!closeDate) return '<span class="status-badge status-pending">Pending</span>';
            if (today > closeDate) return '<span class="status-badge status-closed">Closed</span>';
            return '<span class="status-badge status-open">Open</span>';
        }

        // Function to populate the table
        function populateTenderTable(tenders) {
            const tableBody = document.getElementById('tender-data');
            tableBody.innerHTML = ''; // Clear any existing data
            
            tenders.forEach(tender => {
                const row = document.createElement('tr');
                
                row.innerHTML = `
                    <td id="${tender.tender_id}">${tender.tender_id}</td>
                    <td>
                        <strong>${tender.tender_name}</strong><br>
                        <small>${tender.business_category}</small>
                    </td>
                    <td>${tender.business_category}</td>
                    <td>${getStatus(tender)}</td>
                    <td class="price">${formatPrice(tender.estimated_price)}</td>
                    <td>${formatDate(tender.tender_close_date)}</td>
                    <td class="action-buttons">
						<?php if($user_type == 'city'): ?>
						<button class="edit-btn" onclick="openModal('')">Edit</button>
						<button class="delete-btn" onclick="">Delete</button>
						<?php endif; ?>
						<?php if($user_type == 'company'): ?>
						<button class="bid-btn" onclick="openModal('bidModal')">Bid</button>
						<?php endif; ?>
						<button hidden class="detail-btn" onclick="">Detail</button>
					</td>
                `;
                
                // Add click event to show details (could be a modal in real implementation)
                row.addEventListener('', () => {
                    alert(`Tender Details:\n\nName: ${tender.tender_name}\nDescription: ${tender.tender_description}\nCategory: ${tender.business_category}\nStatus: ${getStatus(tender).replace(/<[^>]*>/g, '')}\nClosing: ${formatDate(tender.tender_close_date).replace(/<[^>]*>/g, '')}\nPrice: ${formatPrice(tender.estimated_price).replace(/<[^>]*>/g, '')}`);
                });
                
                tableBody.appendChild(row);
            });
            
            // Show the table and hide loading message
            document.querySelector('.tender-table').style.display = 'table';
            document.getElementById('loading').style.display = 'none';
        }

        // Function to handle errors
        function handleError(error) {
            //console.error('Error fetching tender data:', error);
            //document.getElementById('loading').style.display = 'none';
            const errorDiv = document.getElementById('error');
            //errorDiv.textContent = `Failed to load tender data: ${error.message}`;
            //errorDiv.style.display = 'block';
        }

        // Fetch tender data from API
        async function fetchTenderData() {
            try {
                const response = await fetch(API_URL);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (!Array.isArray(data)) {
                    throw new Error('Invalid data format received from API');
                }
                
                populateTenderTable(data);
            } catch (error) {
                handleError(error);
            }
        }

        // Initialize the table when page loads
        document.addEventListener('DOMContentLoaded', fetchTenderData);
		
		// Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
		
		// Show error message
        function showError(message) {
            console.log('POST Request Data:', message);
        }
		
		        // Prepare bid form
        function prepareBid(tenderId) {
            document.getElementById('bidTenderId').value = tenderId;
            document.getElementById('bidAmount').value = '';
            document.getElementById('bidProposal').value = '';
            openModal('bidModal');
        }

        // Submit bid
        function submitBid() {
            const bidData = {
                tender_id: document.getElementById('bidTenderId').value,
                amount: document.getElementById('bidAmount').value,
                proposal: document.getElementById('bidProposal').value,
                company_id: 'demo-company' // Simulated company ID
            };
            
            // In a real app, this would send to your API
            console.log('Bid Submission:', bidData);
            alert(`Bid submitted successfully for Tender ${bidData.tender_id} (simulated)`);
            
            closeModal('bidModal');
        }
		
		        // Edit tender - make row editable
        function editTender(tenderId) {
            // const row = document.querySelector(`td[id="${tenderId}"]`);
            // const cells = row.querySelectorAll('td');
            
            // // Skip first (ID) and last (Action) cells
            // for (let i = 1; i < cells.length - 1; i++) {
                // const cell = cells[i];
                // const originalValue = cell.textContent;
                
                // cell.classList.add('editable');
                
                // if (i === 5) { // Status is a dropdown
                    // cell.innerHTML = `
                        // <select class="edit-input">
                            // <option value="Open" ${originalValue === 'Open' ? 'selected' : ''}>Open</option>
                            // <option value="Closed" ${originalValue === 'Closed' ? 'selected' : ''}>Closed</option>
                            // <option value="Awarded" ${originalValue === 'Awarded' ? 'selected' : ''}>Awarded</option>
                        // </select>
                    // `;
                // } else if (i === 2 || i === 3 || i === 4) { // Dates
                    // const dateValue = originalValue === 'N/A' ? '' : originalValue;
                    // cell.innerHTML = `<input type="date" class="edit-input" value="${dateValue}">`;
                // } else { // Text fields
                    // cell.innerHTML = `<input type="text" class="edit-input" value="${originalValue}">`;
                // }
            // //
            
            // // Replace action buttons with confirm/cancel
            // const actionCell = cells[cells.length - 1];
            // actionCell.innerHTML = '';
            
            // const confirmBtn = document.createElement('button');
            // confirmBtn.className = 'confirm-btn';
            // confirmBtn.textContent = 'Confirm';
            // confirmBtn.onclick = () => confirmEdit(tenderId);
            // actionCell.appendChild(confirmBtn);
            
            // const cancelBtn = document.createElement('button');
            // cancelBtn.className = 'cancel-btn';
            // cancelBtn.textContent = 'Cancel';
            // cancelBtn.onclick = () => cancelEdit(tenderId);
            // actionCell.appendChild(cancelBtn);
        }

        // Confirm edit and send PUT request
        function confirmEdit(tenderId) {
            const row = document.querySelector(`tr[data-id="${tenderId}"]`);
            const cells = row.querySelectorAll('td');
            
            const updatedData = {
                id: tenderId,
                tender_name: cells[1].querySelector('.edit-input').value,
                notice_date: cells[2].querySelector('.edit-input').value,
                closing_date: cells[3].querySelector('.edit-input').value,
                winner_disclosure: cells[4].querySelector('.edit-input').value || null,
                status: cells[5].querySelector('.edit-input').value
            };
		}
		
    </script>
    
</body>
</html>