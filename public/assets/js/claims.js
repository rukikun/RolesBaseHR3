// Claims Management JavaScript
class Claims {
    constructor() {
        this.claims = [];
        this.employees = [];
        this.init();
    }

    init() {
        this.loadEmployees();
        this.loadClaims();
        this.setupEventListeners();
        this.updateStats();
    }

    async loadEmployees() {
        try {
            const response = await APIService.get('employees/list.php');
            this.employees = response.data;
            this.populateEmployeeDropdowns();
        } catch (error) {
            console.error('Error loading employees:', error);
        }
    }

    async loadClaims() {
        try {
            const response = await APIService.get('claims/list.php');
            this.claims = response.data;
            this.renderClaimsTable();
            this.renderClaimsSummary();
            this.updateStats();
        } catch (error) {
            console.error('Error loading claims:', error);
        }
    }

    populateEmployeeDropdowns() {
        const select = document.getElementById('claim-employee');
        if (select) {
            select.innerHTML = '<option value="">Select Employee</option>';
            this.employees.forEach(employee => {
                const option = document.createElement('option');
                option.value = employee.id;
                option.textContent = employee.full_name;
                select.appendChild(option);
            });
        }
    }

    renderClaimsSummary() {
        const pendingContainer = document.getElementById('pending-claims-list');
        const approvedContainer = document.getElementById('approved-claims-list');
        
        const pendingClaims = this.claims.filter(claim => claim.status === 'pending');
        const approvedClaims = this.claims.filter(claim => claim.status === 'approved');
        
        // Render pending claims
        if (pendingClaims.length === 0) {
            pendingContainer.innerHTML = '<p class="text-muted">No pending claims</p>';
        } else {
            let html = '';
            pendingClaims.slice(0, 5).forEach(claim => {
                html += `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <strong>${claim.employee_name}</strong><br>
                            <small class="text-muted">${claim.claim_type} - ${HRUtils.formatCurrency(claim.amount)}</small>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-success me-1" onclick="claims.approveClaim(${claim.id})">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="claims.rejectClaim(${claim.id})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                `;
            });
            pendingContainer.innerHTML = html;
        }
        
        // Render approved claims
        if (approvedClaims.length === 0) {
            approvedContainer.innerHTML = '<p class="text-muted">No approved claims</p>';
        } else {
            let html = '';
            approvedClaims.slice(0, 5).forEach(claim => {
                html += `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <strong>${claim.employee_name}</strong><br>
                            <small class="text-muted">${claim.claim_type} - ${HRUtils.formatCurrency(claim.amount)}</small>
                        </div>
                        <span class="badge bg-success">Approved</span>
                    </div>
                `;
            });
            approvedContainer.innerHTML = html;
        }
    }

    renderClaimsTable() {
        const tableBody = document.querySelector('#claims-history-table tbody');
        if (!tableBody) return;

        tableBody.innerHTML = '';

        if (this.claims.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="10" class="text-center text-muted">No claims found</td></tr>';
            return;
        }

        this.claims.forEach(claim => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <input type="checkbox" class="claim-checkbox" value="${claim.id}">
                </td>
                <td>#${claim.id}</td>
                <td>${claim.employee_name}</td>
                <td>${HRUtils.formatDate(claim.expense_date)}</td>
                <td><span class="badge bg-secondary">${claim.claim_type}</span></td>
                <td>${HRUtils.formatCurrency(claim.amount)}</td>
                <td>${claim.description}</td>
                <td>
                    ${claim.receipt_path ? `
                        <button class="btn btn-sm btn-outline-primary" onclick="claims.viewReceipt('${claim.receipt_path}')">
                            <i class="fas fa-file"></i>
                        </button>
                    ` : 'No receipt'}
                </td>
                <td>${HRUtils.getStatusBadge(claim.status)}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="claims.viewClaim(${claim.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    ${claim.status === 'pending' ? `
                        <button class="btn btn-sm btn-success" onclick="claims.approveClaim(${claim.id})">
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="claims.rejectClaim(${claim.id})">
                            <i class="fas fa-times"></i>
                        </button>
                    ` : ''}
                </td>
            `;
            tableBody.appendChild(row);
        });
    }

    updateStats() {
        const totalClaims = this.claims.length;
        const totalAmount = this.claims.reduce((sum, claim) => sum + parseFloat(claim.amount), 0);
        const pendingAmount = this.claims
            .filter(claim => claim.status === 'pending')
            .reduce((sum, claim) => sum + parseFloat(claim.amount), 0);
        const monthlyClaimsCount = this.claims.filter(claim => {
            const claimDate = new Date(claim.expense_date);
            const now = new Date();
            return claimDate.getMonth() === now.getMonth() && claimDate.getFullYear() === now.getFullYear();
        }).length;

        document.getElementById('total-claims').textContent = totalClaims;
        document.getElementById('total-amount').textContent = HRUtils.formatCurrency(totalAmount);
        document.getElementById('pending-amount').textContent = HRUtils.formatCurrency(pendingAmount);
        document.getElementById('monthly-claims').textContent = monthlyClaimsCount;
    }

    setupEventListeners() {
        // New claim button
        const newClaimBtn = document.getElementById('new-claim-btn');
        if (newClaimBtn) {
            newClaimBtn.addEventListener('click', () => this.showClaimModal());
        }

        // Claim form
        const claimForm = document.getElementById('claim-form');
        if (claimForm) {
            claimForm.addEventListener('submit', (e) => this.handleClaimSubmit(e));
        }

        // Receipt upload
        const receiptInput = document.getElementById('claim-receipt');
        if (receiptInput) {
            receiptInput.addEventListener('change', (e) => this.handleReceiptUpload(e));
        }

        // Filter dropdowns
        const claimsFilter = document.getElementById('claims-filter');
        const statusFilter = document.getElementById('status-filter');
        
        if (claimsFilter) {
            claimsFilter.addEventListener('change', () => this.applyFilters());
        }
        
        if (statusFilter) {
            statusFilter.addEventListener('change', () => this.applyFilters());
        }

        // Select all checkbox
        const selectAllCheckbox = document.getElementById('select-all-claims');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', (e) => this.toggleSelectAll(e.target.checked));
        }

        // Bulk approve button
        const bulkApproveBtn = document.getElementById('bulk-approve-btn');
        if (bulkApproveBtn) {
            bulkApproveBtn.addEventListener('click', () => this.bulkApproveClaims());
        }

        // Export button
        const exportBtn = document.getElementById('export-claims-btn');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => this.exportClaims());
        }
    }

    showClaimModal(claimId = null) {
        const modal = new bootstrap.Modal(document.getElementById('claim-modal'));
        const form = document.getElementById('claim-form');
        
        if (claimId) {
            // View/Edit mode
            const claim = this.claims.find(c => c.id === claimId);
            if (claim) {
                document.getElementById('claim-id').value = claim.id;
                document.getElementById('claim-employee').value = claim.employee_id;
                document.getElementById('claim-type').value = claim.claim_type;
                document.getElementById('claim-date').value = claim.expense_date;
                document.getElementById('claim-amount').value = claim.amount;
                document.getElementById('claim-description').value = claim.description;
                document.getElementById('claim-modal-title').textContent = 'View Claim';
                
                // Show approve/reject buttons for pending claims
                if (claim.status === 'pending') {
                    document.getElementById('approve-claim-btn').style.display = 'inline-block';
                    document.getElementById('reject-claim-btn').style.display = 'inline-block';
                }
            }
        } else {
            // Create mode
            form.reset();
            document.getElementById('claim-id').value = '';
            document.getElementById('claim-date').value = new Date().toISOString().split('T')[0];
            document.getElementById('claim-modal-title').textContent = 'New Claim';
            document.getElementById('approve-claim-btn').style.display = 'none';
            document.getElementById('reject-claim-btn').style.display = 'none';
        }
        
        modal.show();
    }

    handleReceiptUpload(e) {
        const file = e.target.files[0];
        if (!file) return;

        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        if (!allowedTypes.includes(file.type)) {
            HRUtils.showNotification('Please upload a valid image or PDF file', 'danger');
            e.target.value = '';
            return;
        }

        // Validate file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            HRUtils.showNotification('File size must be less than 5MB', 'danger');
            e.target.value = '';
            return;
        }

        // Show preview
        const previewContainer = document.getElementById('receipt-preview');
        const previewContent = document.getElementById('receipt-preview-content');
        
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewContent.innerHTML = `<img src="${e.target.result}" class="receipt-preview-img">`;
                previewContainer.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            previewContent.innerHTML = `<p><i class="fas fa-file-pdf"></i> ${file.name}</p>`;
            previewContainer.style.display = 'block';
        }
    }

    async handleClaimSubmit(e) {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append('employee_id', document.getElementById('claim-employee').value);
        formData.append('claim_type', document.getElementById('claim-type').value);
        formData.append('expense_date', document.getElementById('claim-date').value);
        formData.append('amount', document.getElementById('claim-amount').value);
        formData.append('description', document.getElementById('claim-description').value);
        
        const receiptFile = document.getElementById('claim-receipt').files[0];
        if (receiptFile) {
            formData.append('receipt', receiptFile);
        }

        const errors = FormValidator.validateRequired([
            { id: 'claim-employee', name: 'Employee' },
            { id: 'claim-type', name: 'Claim Type' },
            { id: 'claim-date', name: 'Expense Date' },
            { id: 'claim-amount', name: 'Amount' },
            { id: 'claim-description', name: 'Description' }
        ]);

        if (!FormValidator.showErrors(errors)) return;

        try {
            const claimId = document.getElementById('claim-id').value;
            
            if (claimId) {
                await APIService.uploadFile(`claims/update.php?id=${claimId}`, formData);
                HRUtils.showNotification('Claim updated successfully!', 'success');
            } else {
                await APIService.uploadFile('claims/create.php', formData);
                HRUtils.showNotification('Claim submitted successfully!', 'success');
            }

            bootstrap.Modal.getInstance(document.getElementById('claim-modal')).hide();
            this.loadClaims();
        } catch (error) {
            HRUtils.showNotification('Error saving claim', 'danger');
        }
    }

    async approveClaim(id) {
        try {
            await APIService.put(`claims/approve.php?id=${id}`, { status: 'approved' });
            HRUtils.showNotification('Claim approved!', 'success');
            this.loadClaims();
        } catch (error) {
            HRUtils.showNotification('Error approving claim', 'danger');
        }
    }

    async rejectClaim(id) {
        try {
            await APIService.put(`claims/reject.php?id=${id}`, { status: 'rejected' });
            HRUtils.showNotification('Claim rejected!', 'success');
            this.loadClaims();
        } catch (error) {
            HRUtils.showNotification('Error rejecting claim', 'danger');
        }
    }

    viewClaim(id) {
        this.showClaimModal(id);
    }

    viewReceipt(receiptPath) {
        const modal = new bootstrap.Modal(document.getElementById('receipt-modal'));
        const content = document.getElementById('receipt-content');
        
        if (receiptPath.endsWith('.pdf')) {
            content.innerHTML = `<embed src="${receiptPath}" width="100%" height="500px" type="application/pdf">`;
        } else {
            content.innerHTML = `<img src="${receiptPath}" class="img-fluid">`;
        }
        
        modal.show();
    }

    toggleSelectAll(checked) {
        const checkboxes = document.querySelectorAll('.claim-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = checked;
        });
    }

    async bulkApproveClaims() {
        const selectedClaims = Array.from(document.querySelectorAll('.claim-checkbox:checked'))
            .map(checkbox => checkbox.value);
        
        if (selectedClaims.length === 0) {
            HRUtils.showNotification('Please select claims to approve', 'warning');
            return;
        }

        if (!confirm(`Are you sure you want to approve ${selectedClaims.length} claims?`)) return;

        try {
            await APIService.post('claims/bulk-approve.php', { claim_ids: selectedClaims });
            HRUtils.showNotification(`${selectedClaims.length} claims approved successfully!`, 'success');
            this.loadClaims();
        } catch (error) {
            HRUtils.showNotification('Error approving claims', 'danger');
        }
    }

    async exportClaims() {
        try {
            const response = await APIService.get('claims/export.php');
            // Handle file download
            const blob = new Blob([response], { type: 'application/vnd.ms-excel' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `claims_${new Date().toISOString().split('T')[0]}.xlsx`;
            a.click();
            window.URL.revokeObjectURL(url);
        } catch (error) {
            HRUtils.showNotification('Error exporting claims', 'danger');
        }
    }

    applyFilters() {
        const typeFilter = document.getElementById('claims-filter').value;
        const statusFilter = document.getElementById('status-filter').value;
        
        // Filter implementation
        this.renderClaimsTable();
    }
}

// Initialize claims when DOM is loaded
let claims;
document.addEventListener('DOMContentLoaded', function() {
    claims = new Claims();
});
