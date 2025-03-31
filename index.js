// API endpoint
const API_URL = 'api_tender.php';

// Function to format price with commas
function formatPrice(price) {
    if (!price) return '<span class="empty-value">Not set</span>';
    return '$' + parseFloat(price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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
						<button class="detail-btn" onclick="">Detail</button>
					</td>
                `;

        // Add click event to show details (could be a modal in real implementation)
        row.addEventListener('click', () => {
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