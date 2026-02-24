<?php
require_once 'config/db.php';
require_once 'config/functions.php';
include 'includes/header.php';
include 'includes/sidebar.php';


$stmt = $pdo->query("SELECT COUNT(*) as total_items FROM items");
$total_items = $stmt->fetch()['total_items'];

$stmt = $pdo->query("SELECT COUNT(*) as pending_requests FROM issue_requests WHERE status='pending'");
$pending_requests = $stmt->fetch()['pending_requests'];

$stmt = $pdo->query("SELECT COUNT(*) as low_stock FROM items WHERE current_stock < 5");
$low_stock = $stmt->fetch()['low_stock'];

$stmt = $pdo->query("SELECT COUNT(*) as issued_items FROM issue_requests WHERE status='issued'");
$issued_items = $stmt->fetch()['issued_items'];
?>

<div class="dashboard">
    <h1>Dashboard</h1>
    
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
        <div class="stat-card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="color: #3498db; margin-bottom: 10px;">Total Items</h3>
            <p style="font-size: 32px; font-weight: bold;"><?php echo $total_items; ?></p>
        </div>
        
        <div class="stat-card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="color: #f39c12; margin-bottom: 10px;">Pending Requests</h3>
            <p style="font-size: 32px; font-weight: bold;"><?php echo $pending_requests; ?></p>
        </div>
        
        <div class="stat-card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="color: #e74c3c; margin-bottom: 10px;">Low Stock Items</h3>
            <p style="font-size: 32px; font-weight: bold; color: <?php echo $low_stock > 0 ? '#e74c3c' : '#27ae60'; ?>"><?php echo $low_stock; ?></p>
        </div>
        
        <div class="stat-card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="color: #27ae60; margin-bottom: 10px;">Issued Items</h3>
            <p style="font-size: 32px; font-weight: bold;"><?php echo $issued_items; ?></p>
        </div>
    </div>
    
    <div style="margin-top: 30px;">
        <h2>Recent Requests</h2>
        <div class="table-container" style="margin-top: 15px;">
            <table>
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Requester</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM issue_requests ORDER BY request_date DESC LIMIT 5");
                    while($row = $stmt->fetch()) {
                        echo "<tr>";
                        echo "<td>#" . $row['id'] . "</td>";
                        echo "<td>" . htmlspecialchars($row['requester_name']) . "</td>";
                        echo "<td>" . formatDate($row['request_date']) . "</td>";
                        echo "<td>" . getRequestStatus($row['status']) . "</td>";
                        echo "<td><a href='requests/view_request.php?id=" . $row['id'] . "' class='btn btn-primary' style='padding: 5px 10px;'>View</a></td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?>