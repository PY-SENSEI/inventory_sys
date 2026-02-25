<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
?>

<?php
require_once '../config/db.php';
require_once '../config/functions.php';
include '../includes/header.php';
include '../includes/sidebar.php';

$stmt = $pdo->query("SELECT * FROM items ORDER BY item_name");
$items = $stmt->fetchAll();
?>



<div class="form-container" style="max-width: 900px;">
    <h2>Create New Request</h2>
    
    <form action="save_request.php" method="POST" id="requestForm">
        <div class="form-group">
            <label for="requester_name">Requester Name *</label>
            <input type="text" class="form-control" id="requester_name" name="requester_name" required 
                   placeholder="Enter your name">
        </div>
        
        <div class="form-group">
            <label>Request Items *</label>
            <div id="items-container">
        
                <div class="dynamic-row" id="row-1">
                    <select name="item_id[]" class="form-control item-select" required onchange="checkDuplicate(this)">
                        <option value="">Select Item</option>
                        <?php foreach ($items as $item): ?>
                            <option value="<?php echo $item['id']; ?>" 
                                    data-stock="<?php echo $item['current_stock']; ?>"
                                    data-unit="<?php echo $item['unit']; ?>">
                                <?php echo htmlspecialchars($item['item_name']); ?> (Stock: <?php echo $item['current_stock']; ?> <?php echo $item['unit']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="quantity[]" class="form-control" 
                           placeholder="Quantity" min="1" required 
                           onchange="validateQuantity(this)">
                    <button type="button" class="remove-row" onclick="removeRow(this)" style="display: none;">Remove</button>
                </div>
            </div>
            
            <div style="margin-top: 10px;">
                <button type="button" class="btn btn-primary" onclick="addRow()">Add More Items</button>
            </div>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-success" onclick="return validateForm()">Submit Request</button>
            <a href="my_requests.php" class="btn" style="background: #95a5a6; color: white;">Cancel</a>
        </div>
    </form>
</div>

<script>
let rowCount = 1;

function addRow() {
    rowCount++;
    const container = document.getElementById('items-container');
    const newRow = document.createElement('div');
    newRow.className = 'dynamic-row';
    newRow.id = 'row-' + rowCount;
    
    const firstSelect = document.querySelector('.item-select');
    let optionsHTML = '<option value="">Select Item</option>';
    for (let option of firstSelect.options) {
        if (option.value !== '') {
            optionsHTML += `<option value="${option.value}" 
                data-stock="${option.getAttribute('data-stock')}"
                data-unit="${option.getAttribute('data-unit')}">${option.text}</option>`;
        }
    }
    
    newRow.innerHTML = `
        <select name="item_id[]" class="form-control item-select" required onchange="checkDuplicate(this)">
            ${optionsHTML}
        </select>
        <input type="number" name="quantity[]" class="form-control" 
               placeholder="Quantity" min="1" required 
               onchange="validateQuantity(this)">
        <button type="button" class="remove-row" onclick="removeRow(this)">Remove</button>
    `;
    
    container.appendChild(newRow);
    
    if (rowCount > 1) {
        document.querySelector('#row-1 .remove-row').style.display = 'block';
    }
}

function removeRow(btn) {
    const row = btn.closest('.dynamic-row');
    row.remove();
    rowCount--;
    
    if (rowCount === 1) {
        document.querySelector('#row-1 .remove-row').style.display = 'none';
    }
}

function checkDuplicate(select) {
    const selectedValue = select.value;
    if (!selectedValue) return;
    
    const allSelects = document.querySelectorAll('.item-select');
    let duplicateFound = false;
    
    for (let sel of allSelects) {
        if (sel !== select && sel.value === selectedValue) {
            duplicateFound = true;
            break;
        }
    }
    
    if (duplicateFound) {
        alert('This item has already been selected. Please choose a different item.');
        select.value = '';
    }
}

function validateQuantity(input) {
    const row = input.closest('.dynamic-row');
    const select = row.querySelector('.item-select');
    const selectedOption = select.options[select.selectedIndex];
    
    if (select.value && input.value) {
        const availableStock = parseInt(selectedOption.getAttribute('data-stock'));
        const requestedQty = parseInt(input.value);
        
        if (requestedQty > availableStock) {
            alert('Requested quantity exceeds available stock (' + availableStock + ' ' + selectedOption.getAttribute('data-unit') + ')');
            input.value = availableStock;
        }
    }
}

function validateForm() {
    const requesterName = document.getElementById('requester_name').value.trim();
    if (!requesterName) {
        alert('Please enter requester name');
        return false;
    }
    
    const rows = document.querySelectorAll('.dynamic-row');
    let hasValidItem = false;
    
    for (let row of rows) {
        const select = row.querySelector('.item-select');
        const quantity = row.querySelector('input[name="quantity[]"]');
        
        if (select.value && quantity.value) {
            hasValidItem = true;
            break;
        }
    }
    
    if (!hasValidItem) {
        alert('Please add at least one item with quantity');
        return false;
    }
    
    return confirm('Are you sure you want to submit this request?');
}

if (rowCount === 1) {
    document.querySelector('#row-1 .remove-row').style.display = 'none';
}
</script>

<?php
include '../includes/footer.php';
?>