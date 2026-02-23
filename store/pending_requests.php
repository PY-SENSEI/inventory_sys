<?php
require_once '../config/db.php';
require_once '../config/functions.php';

// TO ADD THEM :-

// include '../includes/header.php';
// include '../includes/sidebar.php';

// Get all pending requests

$stmt = $pdo->query("
    SELECT ir.*, 
           COUNT(ird.id) as total_items,
           SUM(ird.qty_requested) as total_qty
    FROM issue_requests ir
    LEFT JOIN issue_request_details ird ON ir.id = ird.request_id
    WHERE ir.status = 'pending'
    GROUP BY ir.id
    ORDER BY ir.request_date DESC
");

$pending_requests = $stmt->fetchAll();
?>

<div class="table-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Pending Requests for Issue</h2>
    </div>
    
    <?php if (count($pending_requests) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Request #</th>
                    <th>Requester Name</th>
                    <th>Request Date</th>
                    <th>Total Items</th>
                    <th>Total Quantity</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending_requests as $request): ?>
                    <tr>
                        <td>#<?php echo $request['id']; ?></td>
                        <td><?php echo htmlspecialchars($request['requester_name']); ?></td>
                        <td><?php echo formatDate($request['request_date']); ?></td>
                        <td><?php echo $request['total_items']; ?></td>
                        <td><?php echo $request['total_qty']; ?></td>
                        <td><?php echo getRequestStatus($request['status']); ?></td>
                        <td>
                            <a href="issue_form.php?request_id=<?php echo $request['id']; ?>" 
                               class="btn btn-primary" 
                               style="padding: 5px 10px; font-size: 12px;">
                                Process Issue
                            </a>
                            <a href="../requests/view_request.php?id=<?php echo $request['id']; ?>" 
                               class="btn" 
                               style="padding: 5px 10px; font-size: 12px; background: #95a5a6; color: white;">
                                View Details
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; background: white; border-radius: 8px;">
            <p style="color: #7f8c8d; margin-bottom: 15px;">No pending requests found.</p>
            <a href="../requests/create_request.php" class="btn btn-primary">Create New Request</a>
        </div>
    <?php endif; ?>
</div>

<?php
include '../includes/footer.php';
?>