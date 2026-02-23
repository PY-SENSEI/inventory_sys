<?php
require_once '../config/db.php';
require_once '../config/functions.php';
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="form-container">
    <h2>Add New Item</h2>
    
    <form action="save_item.php" method="POST" onsubmit="return validateForm('addItemForm')" id="addItemForm">
        <div class="form-group">
            <label for="item_name">Item Name *</label>
            <input type="text" class="form-control" id="item_name" name="item_name" required 
                   placeholder="Enter item name">
        </div>
        
        <div class="form-group">
            <label for="unit">Unit *</label>
            <select class="form-control" id="unit" name="unit" required>
                <option value="">Select Unit</option>
                <option value="pcs">Pieces (pcs)</option>
                <option value="kg">Kilogram (kg)</option>
                <option value="liter">Liter (L)</option>
                <option value="meter">Meter (m)</option>
                <option value="box">Box</option>
                <option value="pack">Pack</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="current_stock">Initial Stock *</label>
            <input type="number" class="form-control" id="current_stock" name="current_stock" required 
                   min="0" value="0" placeholder="Enter initial stock quantity">
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Save Item</button>
            <a href="view_items.php" class="btn" style="background: #95a5a6; color: white;">Cancel</a>
        </div>
    </form>
</div>

<script>
document.getElementById('addItemForm').onsubmit = function() {
    const itemName = document.getElementById('item_name').value.trim();
    const unit = document.getElementById('unit').value;
    const stock = parseInt(document.getElementById('current_stock').value);
    
    if (itemName.length < 2) {
        alert('Item name must be at least 2 characters long');
        return false;
    }
    
    if (stock < 0) {
        alert('Stock cannot be negative');
        return false;
    }
    
    return true;
};
</script>

<?php
include '../includes/footer.php';
?>