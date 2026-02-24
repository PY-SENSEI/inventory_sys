<?php
require_once '../config/db.php';
require_once '../config/functions.php';
include '../includes/header.php';
include '../includes/sidebar.php';

// Get all issued items that haven't been fully returned
$stmt = $pdo->query("
    SELECT 
        ir.id as request_id,
        ir.requester_name,
        ir.request_date,
        ir.status as request_status,
        ird.id as detail_id,
        i.id as item_id,
        i.item_name,
        i.unit,
        i.current_stock as available_stock,
        ird.qty_requested,
        ird.qty_issued,
        ird.qty_returned,
        (ird.qty_issued - ird.qty_returned) as pending_return
    FROM issue_requests ir
    INNER JOIN issue_request_details ird ON ir.id = ird.request_id
    INNER JOIN items i ON ird.item_id = i.id
    WHERE ir.status IN ('issued', 'returned') 
    AND (ird.qty_issued - ird.qty_returned) > 0
    ORDER BY ir.request_date DESC, i.item_name ASC
");

$issued_items = $stmt->fetchAll();

// Group items by request for better display
$grouped_items = [];
foreach ($issued_items as $item) {
    $request_id = $item['request_id'];
    if (!isset($grouped_items[$request_id])) {
        $grouped_items[$request_id] = [
            'requester_name' => $item['requester_name'],
            'request_date' => $item['request_date'],
            'items' => []
        ];
    }
    $grouped_items[$request_id]['items'][] = $item;
}
?>

<div class="form-container" style="max-width: 1200px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Return Items</h2>
        <div>
            <span class="badge badge-info">Items Pending Return: <?php echo count($issued_items); ?></span>
        </div>
    </div>
    
    <?php if (count($issued_items) > 0): ?>
        <form action="process_return.php" method="POST" id="returnForm" onsubmit="return validateReturnForm()">
            
            <?php foreach ($grouped_items as $request_id => $group): ?>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 2px solid #dee2e6;">
                        <div>
                            <strong>Request #<?php echo $request_id; ?></strong> - 
                            <?php echo htmlspecialchars($group['requester_name']); ?> - 
                            <?php echo formatDate($group['request_date']); ?>
                        </div>
                        <div>
                            <span class="badge badge-warning"><?php echo count($group['items']); ?> items pending</span>
                        </div>
                    </div>
                    
                    <table style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Unit</th>
                                <th>Issued Qty</th>
                                <th>Returned Qty</th>
                                <th>Pending Return</th>
                                <th>Current Stock</th>
                                <th>Return Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($group['items'] as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                    <td><?php echo $item['unit']; ?></td>
                                    <td><?php echo $item['qty_issued']; ?></td>
                                    <td><?php echo $item['qty_returned']; ?></td>
                                    <td class="<?php echo $item['pending_return'] > 0 ? 'stock-low' : ''; ?>">
                                        <strong><?php echo $item['pending_return']; ?></strong>
                                    </td>
                                    <td><?php echo $item['available_stock']; ?></td>
                                    <td>
                                        <input type="hidden" name="detail_ids[]" value="<?php echo $item['detail_id']; ?>">
                                        <input type="hidden" name="item_ids[]" value="<?php echo $item['item_id']; ?>">
                                        <input type="hidden" name="request_ids[]" value="<?php echo $request_id; ?>">
                                        <input type="hidden" name="pending_return_<?php echo $item['detail_id']; ?>" value="<?php echo $item['pending_return']; ?>">
                                        
                                        <div style="display: flex; align-items: center; gap: 5px;">
                                            <input type="number" 
                                                   name="return_qty_<?php echo $item['detail_id']; ?>" 
                                                   id="return_qty_<?php echo $item['detail_id']; ?>"
                                                   class="form-control return-qty" 
                                                   data-detail-id="<?php echo $item['detail_id']; ?>"
                                                   data-pending="<?php echo $item['pending_return']; ?>"
                                                   data-item="<?php echo htmlspecialchars($item['item_name']); ?>"
                                                   min="0" 
                                                   max="<?php echo $item['pending_return']; ?>" 
                                                   value="0"
                                                   style="width: 80px;"
                                                   onchange="validateReturnQty(this)"
                                                   onkeyup="updateReturnSummary()">
                                            <button type="button" 
                                                    class="btn" 
                                                    style="padding: 5px 10px; background: #3498db; color: white; font-size: 12px;"
                                                    onclick="setMaxReturn(<?php echo $item['detail_id']; ?>, <?php echo $item['pending_return']; ?>)">
                                                Max
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
            
            <!-- Return Summary -->
            <div style="background: #e8f5e8; padding: 15px; border-radius: 8px; margin: 20px 0;">
                <h4>Return Summary</h4>
                <div id="returnSummary">
                    <p>No items selected for return</p>
                </div>
            </div>
            
          
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn" onclick="selectAllMax()" style="background: #f39c12; color: white;">
                    Return All Max
                </button>
                <button type="button" class="btn" onclick="clearAll()" style="background: #95a5a6; color: white;">
                    Clear All
                </button>
                <button type="submit" class="btn btn-success">
                    Process Returns
                </button>
                <a href="../dashboard.php" class="btn" style="background: #7f8c8d; color: white;">Cancel</a>
            </div>
        </form>
    <?php else: ?>
        <div style="text-align: center; padding: 60px; background: white; border-radius: 8px;">
            <img src="../assets/images/empty-box.png" alt="No returns" style="width: 100px; margin-bottom: 20px; opacity: 0.5;">
            <h3 style="color: #7f8c8d; margin-bottom: 15px;">No Items Available for Return</h3>
            <p style="color: #95a5a6; margin-bottom: 20px;">All issued items have been returned.</p>
            <a href="../reports/stock_report.php" class="btn btn-primary">View Stock Report</a>
        </div>
    <?php endif; ?>
</div>

<script>

let selectedReturns = {};

function validateReturnQty(input) {
    const detailId = input.getAttribute('data-detail-id');
    const pending = parseInt(input.getAttribute('data-pending'));
    const itemName = input.getAttribute('data-item');
    const value = parseInt(input.value) || 0;
    
    
    if (value > pending) {
        alert(`Cannot return more than pending quantity (${pending}) for ${itemName}`);
        input.value = pending;
        updateReturnSummary();
        return false;
    }
    
    // Cannot be negative
    if (value < 0) {
        input.value = 0;
        updateReturnSummary();
        return false;
    }
    
    // Must be integer
    if (value !== Math.floor(value)) {
        input.value = Math.floor(value);
        updateReturnSummary();
        return false;
    }
    
    updateReturnSummary();
    return true;
}

function setMaxReturn(detailId, maxValue) {
    const input = document.getElementById(`return_qty_${detailId}`);
    if (input) {
        input.value = maxValue;
        validateReturnQty(input);
    }
}

function updateReturnSummary() {
    const inputs = document.querySelectorAll('.return-qty');
    let totalItems = 0;
    let totalQty = 0;
    let summaryHtml = '<ul style="list-style: none; padding: 0;">';
    
    inputs.forEach(input => {
        const value = parseInt(input.value) || 0;
        if (value > 0) {
            totalItems++;
            totalQty += value;
            
            const detailId = input.getAttribute('data-detail-id');
            const itemName = input.getAttribute('data-item');
            const pending = input.getAttribute('data-pending');
            
            summaryHtml += `<li style="margin-bottom: 5px;">
                <span style="color: #27ae60;">✓</span> 
                ${itemName}: <strong>${value}</strong> of ${pending} units
            </li>`;
        }
    });
    
    if (totalItems > 0) {
        summaryHtml += `<li style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #ddd;">
            <strong>Total: ${totalItems} items, ${totalQty} units to return</strong>
        </li>`;
        summaryHtml += '</ul>';
        document.getElementById('returnSummary').innerHTML = summaryHtml;
    } else {
        document.getElementById('returnSummary').innerHTML = '<p>No items selected for return</p>';
    }
}

function selectAllMax() {
    const inputs = document.querySelectorAll('.return-qty');
    inputs.forEach(input => {
        const maxValue = input.getAttribute('max');
        input.value = maxValue;
    });
    updateReturnSummary();
}

function clearAll() {
    const inputs = document.querySelectorAll('.return-qty');
    inputs.forEach(input => {
        input.value = 0;
    });
    updateReturnSummary();
}

function validateReturnForm() {
    const inputs = document.querySelectorAll('.return-qty');
    let hasValidReturn = false;
    
    for (let input of inputs) {
        const value = parseInt(input.value) || 0;
        if (value > 0) {
            hasValidReturn = true;
            break;
        }
    }
    
    if (!hasValidReturn) {
        alert('Please enter at least one item quantity to return');
        return false;
    }
    
    // Show confirmation with summary
    let confirmMessage = 'Are you sure you want to process these returns?\n\n';
    inputs.forEach(input => {
        const value = parseInt(input.value) || 0;
        if (value > 0) {
            const itemName = input.getAttribute('data-item');
            confirmMessage += `- ${itemName}: ${value} units\n`;
        }
    });
    confirmMessage += '\nStock will be increased accordingly.';
    
    return confirm(confirmMessage);
}

// Initialize summary on page load 
document.addEventListener('DOMContentLoaded', function() {
    updateReturnSummary();
    
    
    const inputs = document.querySelectorAll('.return-qty');
    inputs.forEach(input => {
        const pending = input.getAttribute('data-pending');
        input.title = `Maximum return: ${pending} units`;
    });
});
</script>

<style>
.return-qty:focus {
    border-color: #27ae60;
    box-shadow: 0 0 5px rgba(39, 174, 96, 0.3);
}

.return-summary-item {
    padding: 5px;
    border-bottom: 1px dashed #ddd;
}

.return-summary-item:last-child {
    border-bottom: none;
}
</style>

<?php
include '../includes/footer.php';
?>