@extends('layouts.hr')

@section('title', 'Database Integration Test - HR System')

@section('content')
<!-- Page Header -->
<div class="page-header-container mb-4">
  <div class="d-flex justify-content-between align-items-center page-header">
    <div class="d-flex align-items-center">
      <div class="dashboard-logo me-3">
        <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
      </div>
      <div>
        <h2 class="fw-bold mb-1">Database Integration Test</h2>
        <p class="text-muted mb-0">Verify complete database functionality</p>
      </div>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Database Test</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Alert Messages -->
<div id="alert-container"></div>

<!-- Test Controls -->
<div class="row g-4 mb-4">
  <div class="col-md-4">
    <div class="card">
      <div class="card-body text-center">
        <i class="fas fa-database fa-3x text-primary mb-3"></i>
        <h5>Connection Test</h5>
        <p class="text-muted">Test database connectivity and table existence</p>
        <button class="btn btn-primary" onclick="testConnection()">
          <i class="fas fa-play me-2"></i>Run Test
        </button>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card">
      <div class="card-body text-center">
        <i class="fas fa-cogs fa-3x text-success mb-3"></i>
        <h5>CRUD Operations</h5>
        <p class="text-muted">Test Create, Read, Update, Delete operations</p>
        <button class="btn btn-success" onclick="testCrudOperations()">
          <i class="fas fa-play me-2"></i>Run Test
        </button>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card">
      <div class="card-body text-center">
        <i class="fas fa-chart-bar fa-3x text-info mb-3"></i>
        <h5>Statistics</h5>
        <p class="text-muted">Get comprehensive database statistics</p>
        <button class="btn btn-info" onclick="getStats()">
          <i class="fas fa-play me-2"></i>Get Stats
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Test Results -->
<div class="card">
  <div class="card-header">
    <h5 class="mb-0">
      <i class="fas fa-clipboard-list me-2"></i>Test Results
    </h5>
  </div>
  <div class="card-body">
    <div id="test-results">
      <div class="text-center text-muted py-5">
        <i class="fas fa-info-circle fa-3x mb-3"></i>
        <p>Click any test button above to see results here</p>
      </div>
    </div>
  </div>
</div>

<script>
async function testConnection() {
  const resultsDiv = document.getElementById('test-results');
  resultsDiv.innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Testing connection...</div>';
  
  try {
    const response = await fetch('/api/database/test-connection');
    const result = await response.json();
    
    if (result.success) {
      let html = '<h6 class="text-success"><i class="fas fa-check-circle me-2"></i>Connection Test Results</h6>';
      
      // Connection status
      html += `<div class="alert alert-success"><strong>Database Connection:</strong> ${result.data.connection}</div>`;
      
      // Tables status
      html += '<h6 class="mt-4">Table Status:</h6>';
      html += '<div class="table-responsive"><table class="table table-sm">';
      html += '<thead><tr><th>Table</th><th>Status</th><th>Records</th></tr></thead><tbody>';
      
      for (const [table, info] of Object.entries(result.data.tables)) {
        const statusBadge = info.exists ? 
          `<span class="badge bg-success">OK</span>` : 
          `<span class="badge bg-danger">ERROR</span>`;
        html += `<tr><td>${table}</td><td>${statusBadge}</td><td>${info.count || 'N/A'}</td></tr>`;
      }
      
      html += '</tbody></table></div>';
      
      // Sample data
      if (result.data.sample_data) {
        html += '<h6 class="mt-4">Sample Data:</h6>';
        html += '<div class="row g-3">';
        html += `<div class="col-md-3"><div class="card bg-light"><div class="card-body text-center"><h5>${result.data.sample_data.active_employees}</h5><small>Active Employees</small></div></div></div>`;
        html += `<div class="col-md-3"><div class="card bg-light"><div class="card-body text-center"><h5>${result.data.sample_data.total_timesheets}</h5><small>Timesheets</small></div></div></div>`;
        html += `<div class="col-md-3"><div class="card bg-light"><div class="card-body text-center"><h5>${result.data.sample_data.leave_requests}</h5><small>Leave Requests</small></div></div></div>`;
        html += `<div class="col-md-3"><div class="card bg-light"><div class="card-body text-center"><h5>${result.data.sample_data.claims}</h5><small>Claims</small></div></div></div>`;
        html += '</div>';
      }
      
      resultsDiv.innerHTML = html;
      HRNotifications.success('Connection test completed successfully');
    } else {
      resultsDiv.innerHTML = `<div class="alert alert-danger"><strong>Error:</strong> ${result.message}</div>`;
      HRNotifications.error('Connection test failed');
    }
  } catch (error) {
    resultsDiv.innerHTML = `<div class="alert alert-danger"><strong>Error:</strong> ${error.message}</div>`;
    HRNotifications.error('Connection test failed');
  }
}

async function testCrudOperations() {
  const resultsDiv = document.getElementById('test-results');
  resultsDiv.innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Testing CRUD operations...</div>';
  
  try {
    const response = await fetch('/api/database/test-crud');
    const result = await response.json();
    
    if (result.success) {
      let html = '<h6 class="text-success"><i class="fas fa-check-circle me-2"></i>CRUD Operations Test Results</h6>';
      
      for (const [module, operations] of Object.entries(result.data)) {
        html += `<div class="card mt-3"><div class="card-header"><strong>${module.replace('_', ' ').toUpperCase()}</strong></div><div class="card-body">`;
        
        if (operations.error) {
          html += `<div class="alert alert-danger">Error: ${operations.error}</div>`;
        } else {
          html += '<div class="row g-2">';
          for (const [operation, status] of Object.entries(operations)) {
            const badgeClass = status === 'SUCCESS' ? 'bg-success' : 'bg-danger';
            html += `<div class="col-md-3"><span class="badge ${badgeClass} w-100">${operation.toUpperCase()}: ${status}</span></div>`;
          }
          html += '</div>';
        }
        
        html += '</div></div>';
      }
      
      resultsDiv.innerHTML = html;
      HRNotifications.success('CRUD operations test completed');
    } else {
      resultsDiv.innerHTML = `<div class="alert alert-danger"><strong>Error:</strong> ${result.message}</div>`;
      HRNotifications.error('CRUD test failed');
    }
  } catch (error) {
    resultsDiv.innerHTML = `<div class="alert alert-danger"><strong>Error:</strong> ${error.message}</div>`;
    HRNotifications.error('CRUD test failed');
  }
}

async function getStats() {
  const resultsDiv = document.getElementById('test-results');
  resultsDiv.innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Loading statistics...</div>';
  
  try {
    const response = await fetch('/api/database/stats');
    const result = await response.json();
    
    if (result.success) {
      let html = '<h6 class="text-info"><i class="fas fa-chart-bar me-2"></i>Database Statistics</h6>';
      
      const stats = result.data;
      
      // Create statistics cards
      html += '<div class="row g-3">';
      
      // Employees
      html += '<div class="col-md-6"><div class="card"><div class="card-header bg-primary text-white"><i class="fas fa-users me-2"></i>Employees</div><div class="card-body">';
      html += `<div class="row text-center"><div class="col-3"><h5>${stats.employees.total}</h5><small>Total</small></div>`;
      html += `<div class="col-3"><h5 class="text-success">${stats.employees.active}</h5><small>Active</small></div>`;
      html += `<div class="col-3"><h5 class="text-warning">${stats.employees.inactive}</h5><small>Inactive</small></div>`;
      html += `<div class="col-3"><h5 class="text-danger">${stats.employees.terminated}</h5><small>Terminated</small></div></div>`;
      html += '</div></div></div>';
      
      // Timesheets
      html += '<div class="col-md-6"><div class="card"><div class="card-header bg-success text-white"><i class="fas fa-clock me-2"></i>Timesheets</div><div class="card-body">';
      html += `<div class="row text-center"><div class="col-3"><h5>${stats.timesheets.total}</h5><small>Total</small></div>`;
      html += `<div class="col-3"><h5 class="text-warning">${stats.timesheets.pending}</h5><small>Pending</small></div>`;
      html += `<div class="col-3"><h5 class="text-success">${stats.timesheets.approved}</h5><small>Approved</small></div>`;
      html += `<div class="col-3"><h5 class="text-danger">${stats.timesheets.rejected}</h5><small>Rejected</small></div></div>`;
      html += `<hr><div class="text-center"><h4 class="text-info">${stats.timesheets.total_hours}</h4><small>Total Hours Worked</small></div>`;
      html += '</div></div></div>';
      
      // Leave Requests
      html += '<div class="col-md-6"><div class="card"><div class="card-header bg-info text-white"><i class="fas fa-calendar-alt me-2"></i>Leave Requests</div><div class="card-body">';
      html += `<div class="row text-center"><div class="col-3"><h5>${stats.leaves.total}</h5><small>Total</small></div>`;
      html += `<div class="col-3"><h5 class="text-warning">${stats.leaves.pending}</h5><small>Pending</small></div>`;
      html += `<div class="col-3"><h5 class="text-success">${stats.leaves.approved}</h5><small>Approved</small></div>`;
      html += `<div class="col-3"><h5 class="text-danger">${stats.leaves.rejected}</h5><small>Rejected</small></div></div>`;
      html += '</div></div></div>';
      
      // Claims
      html += '<div class="col-md-6"><div class="card"><div class="card-header bg-warning text-white"><i class="fas fa-receipt me-2"></i>Claims</div><div class="card-body">';
      html += `<div class="row text-center"><div class="col"><h5>${stats.claims.total}</h5><small>Total</small></div>`;
      html += `<div class="col"><h5 class="text-warning">${stats.claims.pending}</h5><small>Pending</small></div>`;
      html += `<div class="col"><h5 class="text-success">${stats.claims.approved}</h5><small>Approved</small></div>`;
      html += `<div class="col"><h5 class="text-info">${stats.claims.paid}</h5><small>Paid</small></div></div>`;
      html += `<hr><div class="text-center"><h4 class="text-success">$${stats.claims.total_amount}</h4><small>Total Amount</small></div>`;
      html += '</div></div></div>';
      
      html += '</div>';
      
      resultsDiv.innerHTML = html;
      HRNotifications.success('Statistics loaded successfully');
    } else {
      resultsDiv.innerHTML = `<div class="alert alert-danger"><strong>Error:</strong> ${result.message}</div>`;
      HRNotifications.error('Failed to load statistics');
    }
  } catch (error) {
    resultsDiv.innerHTML = `<div class="alert alert-danger"><strong>Error:</strong> ${error.message}</div>`;
    HRNotifications.error('Failed to load statistics');
  }
}
</script>
@endsection
