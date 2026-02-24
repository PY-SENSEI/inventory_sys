<?php
require_once '../config/db.php';
require_once '../config/functions.php';
include '../includes/header.php';
include '../includes/sidebar.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid item ID";
    header("Location: view_items.php");
    exit();
}

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    $_SESSION['error'] = "Item not found";
    header("Location: view_items.php");
    exit();
}

$is_used = isItemUsed($id, $pdo);
?>

<div class="form-container">
    <h2>Edit Item</h2>
    
    <?php if ($is_used): ?>
        <div class="alert alert-warning">
            <strong>Note:</strong> This item is used in some requests. Be careful when modifying it.
        </div>
    <?php endif; ?>
    
    <form action="update_item.php" method="POST" onsubmit="return validateForm('editItemForm')" id="editItemForm">
        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
        
        <div class="form-group">
            <label for="item_name">Item Name *</label>
            <input type="text" class="form-control" id="item_name" name="item_name" required 
                   value="<?php echo htmlspecialchars($item['item_name']); ?>">
        </div>
        
        <div class="form-group">
            <label for="unit">Unit *</label>
            <select class="form-control" id="unit" name="unit" required>
                <option value="">Select Unit</option>
                <option value="pcs" <?php echo $item['unit'] == 'pcs' ? 'selected' : ''; ?>>Pieces (pcs)</option>
                <option value="kg" <?php echo $item['unit'] == 'kg' ? 'selected' : ''; ?>>Kilogram (kg)</option>
                <option value="liter" <?php echo $item['unit'] == 'liter' ? 'selected' : ''; ?>>Liter (L)</option>
                <option value="meter" <?php echo $item['unit'] == 'meter' ? 'selected' : ''; ?>>Meter (m)</option>
                <option value="box" <?php echo $item['unit'] == 'box' ? 'selected' : ''; ?>>Box</option>
                <option value="pack" <?php echo $item['unit'] == 'pack' ? 'selected' : ''; ?>>Pack</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="current_stock">Current Stock *</label>
            <input type="number" class="form-control" id="current_stock" name="current_stock" required 
                   min="0" value="<?php echo $item['current_stock']; ?>">
            <small style="color: #7f8c8d;">Current stock quantity (can be adjusted manually)</small>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Update Item</button>
            <a href="view_items.php" class="btn" style="background: #95a5a6; color: white;">Cancel</a>
        </div>
    </form>
</div>

<script>
document.getElementById('editItemForm').onsubmit = function() {
    const itemName = document.getElementById('item_name').value.trim();
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