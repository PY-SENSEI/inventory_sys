<?php
require_once '../config/db.php';
require_once '../config/functions.php';
include '../includes/header.php';
include '../includes/sidebar.php';


if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid request ID";
    header("Location: my_requests.php");
    exit();
}

$request_id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM issue_requests WHERE id = ?");
$stmt->execute([$request_id]);
$request = $stmt->fetch();

if (!$request) {
    $_SESSION['error'] = "Request not found";
    header("Location: my_requests.php");
    exit();
}


$stmt = $pdo->prepare("
    SELECT ird.*, i.item_name, i.unit 
    FROM issue_request_details ird
    JOIN items i ON ird.item_id = i.id
    WHERE ird.request_id = ?
");
$stmt->execute([$request_id]);
$items = $stmt->fetchAll();
?>

<div class="table-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Request Details #<?php echo $request_id; ?></h2>
        <div>
            <a href="my_requests.php" class="btn" style="background: #95a5a6; color: white;">Back to List</a>
            <?php if ($request['status'] == 'pending'): ?>
                <a href="../store/issue_form.php?request_id=<?php echo $request_id; ?>" class="btn btn-primary">Process Issue</a>
            <?php endif; ?>
        </div>
    </div>
    
    <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
        <p><strong>Requester:</strong> <?php echo htmlspecialchars($request['requester_name']); ?></p>
        <p><strong>Request Date:</strong> <?php echo formatDate($request['request_date']); ?></p>
        <p><strong>Status:</strong> <?php echo getRequestStatus($request['status']); ?></p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Unit</th>
                <th>Requested Qty</th>
                <th>Issued Qty</th>
                <th>Returned Qty</th>
                <th>Pending</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                    <td><?php echo htmlspecialchars($item['unit']); ?></td>
                    <td><?php echo $item['qty_requested']; ?></td>
                    <td><?php echo $item['qty_issued']; ?></td>
                    <td><?php echo $item['qty_returned']; ?></td>
                    <td><?php echo $item['qty_issued'] - $item['qty_returned']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
include '../includes/footer.php';
?>