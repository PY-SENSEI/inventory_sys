<?php
require_once '../config/db.php';
require_once '../config/functions.php';
include '../includes/header.php';
include '../includes/sidebar.php';

if (!isset($_GET['request_id']) || empty($_GET['request_id'])) {
    $_SESSION['error'] = "Invalid request ID";
    header("Location: pending_requests.php");
    exit();
}

$request_id = $_GET['request_id'];

$stmt = $pdo->prepare("
    SELECT ir.*, 
           ird.id as detail_id,
           ird.item_id,
           ird.qty_requested,
           ird.qty_issued,
           i.item_name,
           i.unit,
           i.current_stock
    FROM issue_requests ir
    JOIN issue_request_details ird ON ir.id = ird.request_id
    JOIN items i ON ird.item_id = i.id
    WHERE ir.id = ? AND ir.status = 'pending'
");
$stmt->execute([$request_id]);
$items = $stmt->fetchAll();

if (count($items) == 0) {
    $_SESSION['error'] = "Request not found or already processed";
    header("Location: pending_requests.php");
    exit();
}

$request = $items[0]; 
?>

<div class="form-container">
    <h2>Issue Items - Request #<?php echo $request_id; ?></h2>
    
    <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
        <p><strong>Requester:</strong> <?php echo htmlspecialchars($request['requester_name']); ?></p>
        <p><strong>Request Date:</strong> <?php echo formatDate($request['request_date']); ?></p>
    </div>
    
    <form action="process_issue.php" method="POST" id="issueForm">
        <input type="hidden" name="request_id" value="<?php echo $request_id; ?>">
        
        <table style="width: 100%; margin-bottom: 20px;">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Unit</th>
                    <th>Requested</th>
                    <th>Available Stock</th>
                    <th>Quantity to Issue</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                        <td><?php echo htmlspecialchars($item['unit']); ?></td>
                        <td><?php echo $item['qty_requested']; ?></td>
                        <td class="<?php echo $item['current_stock'] < $item['qty_requested'] ? 'stock-low' : ''; ?>">
                            <?php echo $item['current_stock']; ?>
                            <?php if ($item['current_stock'] < $item['qty_requested']): ?>
                                <br><small style="color: #e74c3c;">Insufficient stock!</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <input type="hidden" name="detail_ids[]" value="<?php echo $item['detail_id']; ?>">
                            <input type="hidden" name="requested_qty_<?php echo $item['detail_id']; ?>" value="<?php echo $item['qty_requested']; ?>">
                            <input type="hidden" name="available_stock_<?php echo $item['detail_id']; ?>" value="<?php echo $item['current_stock']; ?>">
                            <input type="number" 
                                   name="issue_qty_<?php echo $item['detail_id']; ?>" 
                                   class="form-control issue-qty" 
                                   data-detail-id="<?php echo $item['detail_id']; ?>"
                                   data-requested="<?php echo $item['qty_requested']; ?>"
                                   data-available="<?php echo $item['current_stock']; ?>"
                                   min="0" 
                                   max="<?php echo min($item['qty_requested'], $item['current_stock']); ?>" 
                                   value="0"
                                   style="width: 100px;"
                                   onchange="validateIssueQty(this)">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 20px;">
            <button type="submit" name="action" value="partial" class="btn btn-primary" onclick="return validateAllQtys()">Issue</button>
            <button type="submit" name="action" value="full" class="btn btn-success" onclick="return validateFullIssue()">Issue All</button>
            <a href="pending_requests.php" class="btn" style="background: #95a5a6; color: white;">Cancel</a>
        </div>
    </form>
</div>

<script>
// buggy:-
function validateIssueQty(input) {
    const detailId = input.getAttribute('data-detail-id');
    const requested = parseInt(input.getAttribute('data-requested'));
    const available = parseInt(input.getAttribute('data-available'));
    const value = parseInt(input.value) || 0;
    
    if (value > requested) {
        alert('Cannot issue more than requested quantity (' + requested + ')');
        input.value = requested;
        return false;
    }
    
    if (value > available) {
        alert('Insufficient stock! Available: ' + available);
        input.value = available;
        return false;
    }
    
    if (value < 0) {
        input.value = 0;
        return false;
    }
    
    return true;
}

function validateAllQtys() {
    const inputs = document.querySelectorAll('.issue-qty');
    let hasValidIssue = false;
    
    for (let input of inputs) {
        const value = parseInt(input.value) || 0;
        if (value > 0) {
            hasValidIssue = true;
            break;
        }
    }

    if (!hasValidIssue) {
        alert('Please enter at least one item quantity to issue');
        return false;
    }
    return true;
}

// buggy :-
function validateFullIssue() {
    const inputs = document.querySelectorAll('.issue-qty');
    let canIssueFull = true;
    
    for (let input of inputs) {
        const requested = parseInt(input.getAttribute('data-requested'));
        const available = parseInt(input.getAttribute('data-available'));
        
        if (requested > available) {
            alert('Cannot issue full request due to insufficient stock for some items');
            return false;
        }
    }
    
    for (let input of inputs) {
        const requested = parseInt(input.getAttribute('data-requested'));
        input.value = requested;
    }
    
    return true;
}

document.getElementById('issueForm').addEventListener('submit', function(e) {
    const action = e.submitter.value;
    let message = '';
    
    if (action === 'partial') {
        message = 'Are you sure you want to issue these items? Stock will be reduced accordingly.';
    } else {
        message = 'Are you sure you want to issue all requested items? Stock will be reduced accordingly.';
    }
    
    if (!confirm(message)) {
        e.preventDefault();
    }
});

</script>

<?php
include '../includes/footer.php';
?>