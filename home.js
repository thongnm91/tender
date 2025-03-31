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
    const currentDate = new Date();
    const tenderStartDate = new Date(tender.tender_start_date);
    const tenderCloseDate = new Date(tender.tender_close_date);
    const tenderDate = new Date(tender.tender_date);

    if (currentDate < tenderStartDate) {
        return '<span class="status-badge status-pending">Pending</span>';
    } else if (currentDate >= tenderStartDate && currentDate < tenderCloseDate) {
        return '<span class="status-badge status-open">Open</span>';
    } else {
        return '<span class="status-badge status-closed">Closed</span>';
    }
}

// Function to populate the table
function populateTenderTable(tenders) {
    const tableBody = document.getElementById('tender-data');
    tableBody.innerHTML = ''; // Clear any existing data
    
    tenders.forEach(tender => {
        const row = document.createElement('tr');
        
        row.innerHTML = `
            <td disabled id="${tender.id}" class="tender-id">${tender.id}</td>
            <td class="tender-name"><strong>${tender.tender_name}</strong><br></td>
            <td class="tender-category">${tender.business_category}</td>
            <td class="tender-status">${getStatus(tender)}</td>
            <td class="price">${formatPrice(tender.estimated_price)}</td>
            <td class="tender-date">${formatDate(tender.tender_close_date)}</td>
            <td class="action-buttons">
                ${userType === 'city' ? `
                    <button class="edit-btn" onclick="fetchTenderData('edit', '${tender.id}')">Edit</button>
                    <button class="delete-btn" onclick="deleteTender(${tender.id})">Delete</button>
                    <div class="confirm-cancel-buttons" style="display: none;">
                        <button style="display: none;" onclick="confirmEdit(${tender.id})">Confirm</button>
                        <button style="display: none;" onclick="cancelEdit(${tender.id})">Cancel</button>
                    </div>
                ` : ''}
                ${userType === 'company' ? `
                    <button class="bid-btn" onclick="fetchTenderData('bid', '${tender.id}')">Bid</button>
                ` : ''}
                <button class="detail-btn" onclick="viewTenderDetails(${tender.id})">
                    <i class="fas fa-eye"></i> Detail
                </button>
            </td>
        `;
        
        tableBody.appendChild(row);
    });
    
    // Show the table
    document.querySelector('.tender-table').style.display = 'table';
}

// Function to handle errors
function handleError(error) {
    console.error('Error:', error);
}

// Fetch tender data from API
async function fetchTenderData(fetch_type, tenderId = null) {
    try {
        // 1. Construct the URL based on whether tenderId is provided
        const url = tenderId ? `${API_URL}/${tenderId}` : API_URL;
        
        // 2. Make the API request and wait for response
        const response = await fetch(url);

        // 3. Check if response is successful
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        // 4. Decode JSON response into JavaScript object
        const data = await response.json();
        
        // 5. Validate data exists
        if (!data) {
            throw new Error("No data received from API");
        }

        switch(fetch_type) {
            case 'edit':
                if (tenderId) {
                    const tender = data.find((items) => items.id === tenderId);
                    if (!tender) throw new Error(`Tender with ID ${tenderId} not found`);
                    editTender(tender);
                }
                break;
            case 'create':
                populateTenderTable(data);
                break;
            case 'bid':
                if (tenderId) {
                    populateBidForm(tenderId);
                }
                break;
            case 'detail':
                if (tenderId) {
                    const tender = data.find((items) => items.id === tenderId);
                    if (!tender) throw new Error(`Tender with ID ${tenderId} not found`);
                    populateTenderDetails(tender);
                }
                break;
        }
    } catch (error) {
        handleError(error);
    }
}

// Modal functions
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
    
    // If opening create tender modal, set current date
    if (modalId === 'createTenderModal') {
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('tenderDate').value = today;
    }
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Create Tender Form Submission
document.getElementById("createTenderForm").addEventListener("submit", async function(event) {
    event.preventDefault();

    const formData = new FormData(this);
    const jsonData = {
        action: 'create_tender',
        tender_name: formData.get('tender_name'),
        tender_date: formData.get('tender_date'),
        business_category: formData.get('business_category'),
        tender_description: formData.get('tender_description'),
        construction_term: formData.get('construction_term'),
        estimated_price: formData.get('estimated_price'),
        tender_start_date: formData.get('tender_start_date'),
        tender_close_date: formData.get('tender_close_date'),
        winner_disclosure_date: formData.get('winner_disclosure_date')
    };

    try {
        const response = await fetch("api_tender.php", {
            method: "POST",
            headers: { 
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify(jsonData)
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        
        if (data.error) {
            alert("Error: " + data.error);
        } else {
            alert(data.message);
            closeModal("createTenderModal");
            fetchTenderData('create', null);
        }
    } catch (error) {
        console.error("Error:", error);
        alert("An error occurred while creating the tender. Please try again.");
    }
});

// Delete Tender Function
function deleteTender(tenderId) {
    if (!confirm("Are you sure you want to delete this tender?")) return;

    fetch(`api_tender.php?id=${tenderId}`, {
        method: "DELETE",
        headers: { "Content-Type": "application/json" }
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert("Error: " + data.error);
        } else {
            alert("Tender deleted successfully!");
            setTimeout(() => location.reload(), 500);
        }
    })
    .catch(error => console.error("Error:", error));
}

// Edit Tender Function
function editTender(tender) {
    if (!tender) {
        alert("Tender data not found!");
        return;
    }

    // Populate the edit form with API data
    document.getElementById("etenderID").value = tender.id;
    document.getElementById("etenderName").value = tender.tender_name || '';
    document.getElementById("etenderDate").value = tender.tender_date || '';
    document.getElementById("ebusinessCategory").value = tender.business_category || '';
    document.getElementById("etenderDescription").value = tender.tender_description || '';
    document.getElementById("econstructionTerm").value = tender.construction_term || '';
    document.getElementById("eestimatedPrice").value = tender.estimated_price || '';
    document.getElementById("etenderStartDate").value = tender.tender_start_date || '';
    document.getElementById("etenderCloseDate").value = tender.tender_close_date || '';
    document.getElementById("ewinnerDisclosureDate").value = tender.winner_disclosure_date || '';

    // Open the modal
    openModal("editTenderModal");
}

// Edit Form Submission
document.getElementById("editTenderForm").addEventListener("submit", function(event) {
    event.preventDefault();
    const formData = new FormData(this);
    const jsonData = {};
    formData.forEach((value, key) => {
        if (value !== '') {
            jsonData[key] = value;
            console.log(key, value);
        }
    });


    console.log('Sending data:', jsonData);

    fetch(`api_tender.php`, {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(jsonData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.error) {
            alert("Error: " + data.error);
        } else {
            alert(data.message);
            closeModal("editTenderModal");
            setTimeout(() => location.reload(), 500);
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("An error occurred while updating the tender. Please try again.");
    });
});

// Bid submission
document.getElementById('bidForm').addEventListener('submit',async function(e)  {
    e.preventDefault();
    
    const bid_amount = document.getElementById('bidAmount').value;
    const notes = document.getElementById('bidProposal').value;
    const tender_id = document.getElementById('tenderId').value;
    const submission_date = new Date().toISOString().split('T')[0];
    const company_id = `${user_id}`;

    if (!bid_amount || !notes) {
        alert('Please fill in all required fields');
        return;
    }

    try {
        const bidData = {
            action: 'submit_bid',
            tender_id: tender_id,
            company_id: company_id,
            bid_amount: bid_amount,
            notes: notes,
            submission_date: submission_date
        };
        console.log(bidData);
        
        const response = await fetch('api_tender.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(bidData)
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        
        if (data.error) {
            alert("Error: " + data.error);
        } else {
            alert(data.message);
            closeModal('bidModal');
            fetchTenderData('create', null); // Refresh tender list
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error submitting bid. Please try again.');
    }
});

// Function to populate bid form 
function populateBidForm(tenderId) {
    document.getElementById('tenderId').value = tenderId;
    document.getElementById('bidAmount').value = '';
    document.getElementById('bidProposal').value = '';
    openModal('bidModal');
}

// Function to view tender details
function viewTenderDetails(tenderId) {
    fetchTenderData('detail', tenderId);
    openModal('detailModal');
}

function closeDetailModal() {
    closeModal('detailModal');
}

// Function to fetch bids for a tender
async function fetchBids(tenderId) {
    try {
        const response = await fetch(`api_tender.php?action=get_bids&tender_id=${tenderId}`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        
        if (data.error) {
            console.error("Error fetching bids:", data.error);
            return;
        }
        
        // Display bids in the detail modal
        const bidsContainer = document.getElementById('bidsList');
        if (!bidsContainer) return;
        
        if (data.length === 0) {
            bidsContainer.innerHTML = '<p>No bids submitted yet.</p>';
            return;
        }
        
        let bidsHtml = '<table class="bids-table"><thead><tr>' +
            '<th>Company ID</th>' +
            '<th>Bid Amount</th>' +
            '<th>Notes</th>' +
            '<th>Submission Date</th>' +
            '</tr></thead><tbody>';
            
        data.forEach(bid => {
            bidsHtml += `<tr>
                <td>${bid.company_id}</td>
                <td>${formatPrice(bid.bid_amount)}</td>
                <td>${bid.notes}</td>
                <td>${formatDate(bid.submission_date)}</td>
            </tr>`;
        });
        
        bidsHtml += '</tbody></table>';
        bidsContainer.innerHTML = bidsHtml;
    } catch (error) {
        console.error('Error fetching bids:', error);
    }
}

// Function to populate tender details
function populateTenderDetails(tender) {
    if (!tender) {
        alert("Tender data not found!");
        return;
    }

    // Populate tender details
    document.getElementById("detailTenderName").textContent = tender.tender_name;
    document.getElementById("detailTenderCategory").textContent = tender.business_category;
    document.getElementById("detailTenderDescription").textContent = tender.tender_description;
    document.getElementById("detailEstimatedPrice").textContent = formatPrice(tender.estimated_price);
    document.getElementById("detailStartDate").textContent = formatDate(tender.tender_start_date);
    document.getElementById("detailCloseDate").textContent = formatDate(tender.tender_close_date);
    document.getElementById("detailConstructionTerm").textContent = tender.construction_term;
    document.getElementById("detailWinnerDisclosureDate").textContent = formatDate(tender.winner_disclosure_date);

    // Fetch and display bids
    fetchBids(tender.id);
}

// Initialize the table when page loads
document.addEventListener('DOMContentLoaded', () => {
    fetchTenderData('create', null);
}); 