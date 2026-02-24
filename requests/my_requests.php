<?php
require_once '../config/db.php';
require_once '../config/functions.php';
include '../includes/header.php';
include '../includes/sidebar.php';

$stmt = $pdo->query("
    SELECT ir.*, 
           COUNT(ird.id) as total_items,
           SUM(ird.qty_requested) as total_qty,
           SUM(ird.qty_issued) as total_issued,
           SUM(ird.qty_returned) as total_returned
    FROM issue_requests ir
    LEFT JOIN issue_request_details ird ON ir.id = ird.request_id
    GROUP BY ir.id
    ORDER BY ir.request_date DESC
");
$requests = $stmt->fetchAll();
?>

<div class="table-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>My Requests</h2>
        <a href="create_request.php" class="btn btn-primary">Create New Request</a>
    </div>
    
    <?php if (count($requests) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Request #</th>
                    <th>Requester Name</th>
                    <th>Request Date</th>
                    <th>Items</th>
                    <th>Requested Qty</th>
                    <th>Issued Qty</th>
                    <th>Returned Qty</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $request): ?>
                    <tr>
                        <td>#<?php echo $request['id']; ?></td>
                        <td><?php echo htmlspecialchars($request['requester_name']); ?></td>
                        <td><?php echo formatDate($request['request_date']); ?></td>
                        <td><?php echo $request['total_items']; ?></td>
                        <td><?php echo $request['total_qty']; ?></td>
                        <td><?php echo $request['total_issued']; ?></td>
                        <td><?php echo $request['total_returned']; ?></td>
                        <td><?php echo getRequestStatus($request['status']); ?></td>
                        <td>
                            <a href="view_request.php?id=<?php echo $request['id']; ?>" 
                               class="btn btn-primary" 
                               style="padding: 5px 10px; font-size: 12px;">
                                View Details
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; background: white; border-radius: 8px;">
            <p style="color: #7f8c8d; margin-bottom: 15px;">No requests found.</p>
            <a href="create_request.php" class="btn btn-primary">Create Your First Request</a>
        </div>
    <?php endif; ?>
</div>

<?php
include '../includes/footer.php';
?>