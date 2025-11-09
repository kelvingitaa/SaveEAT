<?php
use App\Core\CSRF;
use App\Core\Session;

$token = CSRF::token();
$success = Session::flash('success');
$error = Session::flash('error');
ob_start();
?>
<style>
.reports-container {
    background: #f8fafc;
    min-height: calc(100vh - 200px);
    padding: 2rem;
}
.report-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    padding: 2rem;
    margin-bottom: 2rem;
    transition: transform 0.2s ease;
}
.report-card:hover {
    transform: translateY(-2px);
}
.report-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e2e8f0;
}
.date-filters {
    background: #f1f5f9;
    padding: 1.5rem;
    border-radius: 0.75rem;
    margin-bottom: 2rem;
}
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}
.stat-card {
    background: #f8fafc;
    border-radius: 0.75rem;
    padding: 1.5rem;
    border-left: 4px solid #2563eb;
    text-align: center;
    transition: all 0.3s ease;
}
.stat-card:hover {
    background: #e2e8f0;
    transform: scale(1.02);
}
.stat-card.success { border-left-color: #10b981; }
.stat-card.warning { border-left-color: #f59e0b; }
.stat-card.danger { border-left-color: #ef4444; }
.stat-card.info { border-left-color: #3b82f6; }
.stat-card.purple { border-left-color: #8b5cf6; }
.stat-value {
    font-size: 2rem;
    font-weight: bold;
    color: #1e293b;
    margin-bottom: 0.5rem;
}
.stat-label {
    color: #64748b;
    font-weight: 500;
    font-size: 0.9rem;
}
.metric-badge {
    font-size: 0.8rem;
    padding: 0.25em 0.6em;
}
.realtime-badge {
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}
.refresh-btn {
    cursor: pointer;
    transition: transform 0.3s ease;
}
.refresh-btn:hover {
    transform: rotate(180deg);
}
</style>

<div class="reports-container">
    <!-- Header and Filters -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2"><i class="bi bi-graph-up"></i> SaveEAT Analytics Dashboard</h1>
        <div class="btn-group">
            <button class="btn btn-outline-primary refresh-btn" onclick="refreshData()" title="Refresh Data">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
            <button class="btn btn-outline-primary" onclick="window.print()">
                <i class="bi bi-printer"></i> Print Report
            </button>
            <button class="btn btn-primary" onclick="exportToExcel()">
                <i class="bi bi-download"></i> Export Excel
            </button>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Real-time Stats -->
    <div class="report-card">
        <div class="report-header">
            <h2><i class="bi bi-lightning-charge text-warning"></i> Real-Time Dashboard</h2>
            <span class="badge bg-warning realtime-badge">LIVE</span>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card info">
                <div class="stat-value"><?= number_format($realtimeStats['today_orders'] ?? 0) ?></div>
                <div class="stat-label">Orders Today</div>
                <small class="text-muted"><?= number_format($realtimeStats['today_completed'] ?? 0) ?> completed</small>
            </div>
            <div class="stat-card success">
                <div class="stat-value"><?= number_format($realtimeStats['today_donations'] ?? 0) ?></div>
                <div class="stat-label">Donations Today</div>
                <small class="text-muted">Making a difference</small>
            </div>
            <div class="stat-card purple">
                <div class="stat-value"><?= number_format($realtimeStats['active_food_items'] ?? 0) ?></div>
                <div class="stat-label">Active Food Items</div>
                <small class="text-muted">Available for purchase</small>
            </div>
            <div class="stat-card warning">
                <div class="stat-value"><?= number_format($realtimeStats['active_vendors'] ?? 0) ?></div>
                <div class="stat-label">Active Vendors</div>
                <small class="text-muted">Partner restaurants</small>
            </div>
        </div>
    </div>

    <!-- Date Filters -->
    <div class="date-filters">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>">
            </div>
            <div class="col-md-4">
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="/admin/reports" class="btn btn-outline-secondary">Reset</a>
                    <button type="button" class="btn btn-outline-info" onclick="setDateRange(7)">Last 7 Days</button>
                    <button type="button" class="btn btn-outline-info" onclick="setDateRange(30)">Last 30 Days</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Overview Stats -->
    <div class="report-card">
        <div class="report-header">
            <h2><i class="bi bi-speedometer2"></i> Executive Summary</h2>
            <small class="text-muted">Period: <?= date('M j, Y', strtotime($startDate)) ?> - <?= date('M j, Y', strtotime($endDate)) ?></small>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card success">
                <div class="stat-value"><?= number_format($foodSaved['total_items_saved'] ?? 0) ?></div>
                <div class="stat-label">Food Items Saved from Waste</div>
                <small class="text-muted">KSh <?= number_format($foodSaved['total_value_saved'] ?? 0, 2) ?> value</small>
            </div>
            <div class="stat-card info">
                <div class="stat-value"><?= number_format($donations['items_donated'] ?? 0) ?></div>
                <div class="stat-label">Items Donated to Shelters</div>
                <small class="text-muted"><?= number_format($donations['total_donations'] ?? 0) ?> donations</small>
            </div>
            <div class="stat-card warning">
                <div class="stat-value">KSh <?= number_format(array_sum(array_column($vendorIncome ?? [], 'total_income')) ?? 0, 2) ?></div>
                <div class="stat-label">Vendor Income Generated</div>
                <small class="text-muted"><?= count($vendorIncome ?? []) ?> active vendors</small>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= number_format($foodSaved['customers_served'] ?? 0) ?></div>
                <div class="stat-label">Customers Served</div>
                <small class="text-muted">Community impact</small>
            </div>
        </div>
    </div>

    <!-- Food Waste Prevention Metrics -->
    <div class="report-card">
        <div class="report-header">
            <h2><i class="bi bi-recycle text-success"></i> Food Waste Prevention</h2>
            <span class="badge bg-success">Environmental Impact</span>
        </div>
        
        <div class="row">
            <div class="col-md-3 text-center mb-3">
                <div class="stat-card success">
                    <div class="stat-value"><?= number_format($wastePrevention['last_minute_saves'] ?? 0) ?></div>
                    <div class="stat-label">Last-Minute Saves</div>
                    <small>Items sold before expiry</small>
                </div>
            </div>
            <div class="col-md-3 text-center mb-3">
                <div class="stat-card danger">
                    <div class="stat-value"><?= number_format($wastePrevention['items_expired'] ?? 0) ?></div>
                    <div class="stat-label">Items Expired</div>
                    <small>Minimized waste</small>
                </div>
            </div>
            <div class="col-md-3 text-center mb-3">
                <div class="stat-card warning">
                    <div class="stat-value"><?= number_format($wastePrevention['expiring_soon'] ?? 0) ?></div>
                    <div class="stat-label">Expiring Soon</div>
                    <small>Need attention</small>
                </div>
            </div>
            <div class="col-md-3 text-center mb-3">
                <div class="stat-card info">
                    <div class="stat-value"><?= number_format($wastePrevention['active_listings'] ?? 0) ?></div>
                    <div class="stat-label">Active Listings</div>
                    <small>Food in circulation</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Vendor Performance -->
    <div class="report-card">
        <div class="report-header">
            <h2><i class="bi bi-trophy text-warning"></i> Top Performing Vendors</h2>
            <span class="badge bg-warning">Revenue & Impact</span>
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Vendor</th>
                        <th>Location</th>
                        <th>Orders</th>
                        <th>Income</th>
                        <th>Avg Order</th>
                        <th>Donations</th>
                        <th>Impact Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($vendorIncome ?? []) as $vendor): 
                        $ordersCompleted = $vendor['orders_completed'] ?? 0;
                        $donationsMade = $vendor['donations_made'] ?? 0;
                        $itemsDonated = $vendor['items_donated'] ?? 0;
                        $impactScore = ($ordersCompleted * 0.3) + ($donationsMade * 0.4) + ($itemsDonated * 0.3);
                    ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($vendor['business_name'] ?? 'Unknown') ?></strong></td>
                            <td><?= htmlspecialchars($vendor['location'] ?? 'Unknown') ?></td>
                            <td><?= number_format($ordersCompleted) ?></td>
                            <td class="text-success fw-bold">KSh <?= number_format($vendor['total_income'] ?? 0, 2) ?></td>
                            <td>KSh <?= number_format($vendor['avg_order_value'] ?? 0, 2) ?></td>
                            <td>
                                <span class="badge bg-info">
                                    <?= number_format($donationsMade) ?> donations
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $impactScore > 5 ? 'success' : ($impactScore > 2 ? 'warning' : 'secondary') ?>">
                                    <?= number_format($impactScore, 1) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($vendorIncome)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No vendor data available for the selected period.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Shelter Impact -->
    <div class="report-card">
        <div class="report-header">
            <h2><i class="bi bi-house-heart text-danger"></i> Shelter Impact Report</h2>
            <span class="badge bg-danger">Community Support</span>
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Shelter</th>
                        <th>Location</th>
                        <th>Capacity</th>
                        <th>Donations Received</th>
                        <th>Total Items</th>
                        <th>First Donation</th>
                        <th>Last Donation</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($shelterImpact ?? []) as $shelter): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($shelter['shelter_name'] ?? 'Unknown') ?></strong></td>
                            <td><?= htmlspecialchars($shelter['location'] ?? 'Unknown') ?></td>
                            <td><?= number_format($shelter['capacity'] ?? 0) ?> people</td>
                            <td><?= number_format($shelter['donations_received'] ?? 0) ?></td>
                            <td class="text-success fw-bold"><?= number_format($shelter['total_items_received'] ?? 0) ?></td>
                            <td><?= $shelter['first_donation'] ? date('M j, Y', strtotime($shelter['first_donation'])) : 'Never' ?></td>
                            <td><?= $shelter['last_donation'] ? date('M j, Y', strtotime($shelter['last_donation'])) : 'Never' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($shelterImpact)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No shelter data available for the selected period.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Monthly Trends -->
    <div class="report-card">
        <div class="report-header">
            <h2><i class="bi bi-calendar-range text-primary"></i> Monthly Performance Trends</h2>
            <span class="badge bg-primary">6-Month Overview</span>
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Orders</th>
                        <th>Revenue</th>
                        <th>Donations</th>
                        <th>Items Donated</th>
                        <th>Active Customers</th>
                        <th>Active Vendors</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($monthlyTrends ?? []) as $month): ?>
                        <tr>
                            <td><strong><?= date('F Y', strtotime($month['month'] . '-01')) ?></strong></td>
                            <td><?= number_format($month['order_count'] ?? 0) ?></td>
                            <td class="text-success fw-bold">KSh <?= number_format($month['revenue'] ?? 0, 2) ?></td>
                            <td><?= number_format($month['donation_count'] ?? 0) ?></td>
                            <td><?= number_format($month['items_donated'] ?? 0) ?></td>
                            <td><?= number_format($month['active_customers'] ?? 0) ?></td>
                            <td><?= number_format($month['active_vendors'] ?? 0) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($monthlyTrends)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No monthly trend data available.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Delivery Performance -->
    <div class="report-card">
        <div class="report-header">
            <h2><i class="bi bi-truck text-info"></i> Delivery Performance</h2>
            <span class="badge bg-info">Logistics</span>
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Driver</th>
                        <th>Vehicle</th>
                        <th>Deliveries</th>
                        <th>Avg Time</th>
                        <th>Success Rate</th>
                        <th>Cancelled</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($deliveryPerformance ?? []) as $driver): 
                        $totalDeliveries = $driver['deliveries_completed'] ?? 0;
                        $successful = $driver['successful_deliveries'] ?? 0;
                        $cancelled = $driver['cancelled_deliveries'] ?? 0;
                        $successRate = $totalDeliveries > 0 ? ($successful / $totalDeliveries) * 100 : 0;
                    ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($driver['driver_name'] ?? 'Unknown') ?></strong></td>
                            <td><?= htmlspecialchars($driver['vehicle_type'] ?? 'Unknown') ?></td>
                            <td><?= number_format($totalDeliveries) ?></td>
                            <td><?= number_format($driver['avg_delivery_time_minutes'] ?? 0) ?> min</td>
                            <td>
                                <span class="badge bg-<?= $successRate >= 90 ? 'success' : ($successRate >= 80 ? 'warning' : 'danger') ?>">
                                    <?= number_format($successRate, 1) ?>%
                                </span>
                            </td>
                            <td><?= number_format($cancelled) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($deliveryPerformance)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No delivery data available for the selected period.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function setDateRange(days) {
    const endDate = new Date().toISOString().split('T')[0];
    const startDate = new Date(Date.now() - days * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
    
    document.querySelector('input[name="start_date"]').value = startDate;
    document.querySelector('input[name="end_date"]').value = endDate;
    document.querySelector('form').submit();
}

function refreshData() {
    location.reload();
}

function exportToExcel() {
    // Simple table export (you can enhance this with SheetJS)
    const tables = document.querySelectorAll('.table');
    let csvContent = "data:text/csv;charset=utf-8,";
    
    tables.forEach((table, index) => {
        const title = table.closest('.report-card').querySelector('h2').textContent;
        csvContent += title + "\\r\\n\\r\\n";
        
        const rows = table.querySelectorAll('tr');
        rows.forEach(row => {
            const cols = row.querySelectorAll('th, td');
            const rowArray = [];
            cols.forEach(col => {
                rowArray.push('"' + col.textContent.replace(/"/g, '""') + '"');
            });
            csvContent += rowArray.join(',') + "\\r\\n";
        });
        csvContent += "\\r\\n\\r\\n";
    });
    
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "saveeat-reports-<?= date('Y-m-d') ?>.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Auto-refresh every 5 minutes
setTimeout(refreshData, 300000);

// Add some interactivity
document.addEventListener('DOMContentLoaded', function() {
    // Add click handlers to stat cards for detailed views
    document.querySelectorAll('.stat-card').forEach(card => {
        card.style.cursor = 'pointer';
        card.addEventListener('click', function() {
            const label = this.querySelector('.stat-label').textContent;
            alert('Detailed view for: ' + label + '\\nThis would show a detailed breakdown.');
        });
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';