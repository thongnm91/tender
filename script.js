const API_BASE_URL = 'api_tender.php';

/*Interface*/
// GET All Users
//document.getElementById('fetchUsers').addEventListener('click', fetchUsers); //button click then call fetchUsers()
populateTenderTable();


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
function populateTenderTable() {
	const tableBody = document.getElementById('tender-data');
	fetch(API_BASE_URL)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data > 0) {
               data.forEach(tender => {
		const row = document.createElement('tr');
		
		row.innerHTML = `
			<td>${tender.tender_id}</td>
			<td>
				<strong>${tender.tender_name}</strong><br>
				<small>${tender.business_category}</small>
			</td>
			<td>${tender.business_category}</td>
			<td>${getStatus(tender)}</td>
			<td class="price">${formatPrice(tender.estimated_price)}</td>
			<td>${formatDate(tender.tender_close_date)}</td>
			<td>
				<button class="action-btn view-btn">View</button>
				<button class="action-btn bid-btn">Bid</button>
			</td>
		`;
		
		// Add click event to show details (could be a modal in real implementation)
		row.addEventListener('click', () => {
			alert(`Tender Details:\n\nName: ${tender.tender_name}\nDescription: ${tender.tender_description}\nCategory: ${tender.business_category}\nStatus: ${getStatus(tender).replace(/<[^>]*>/g, '')}\nClosing: ${formatDate(tender.tender_close_date).replace(/<[^>]*>/g, '')}\nPrice: ${formatPrice(tender.estimated_price).replace(/<[^>]*>/g, '')}`);
		});
		
		tableBody.appendChild(row);
	});
            } else {
                userDataDiv.innerHTML = '<p>No users found in the database.</p>';
            }
        })
        .catch(error => {
            
        });
	
	
}

// Initialize the table when page loads
document.addEventListener('DOMContentLoaded', populateTenderTable);