<?php
require_once '../config/db.php';
require_once '../config/functions.php';
include '../includes/header.php';
include '../includes/sidebar.php';

$stmt = $pdo->query("SELECT * FROM items ORDER BY item_name");
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<div class="table-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Items List</h2>
        <a href="add_item.php" class="btn btn-primary">Add New Item</a>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Item Name</th>
                <th>Unit</th>
                <th>Current Stock</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($items) > 0): ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td>#<?php echo $item['id']; ?></td>
                        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                        <td><?php echo htmlspecialchars($item['unit']); ?></td>
                        <td class="<?php echo $item['current_stock'] < 5 ? 'stock-low' : ''; ?>">
                            <?php echo $item['current_stock']; ?>
                        </td>
                        <td><?php echo formatDate($item['created_at']); ?></td>
                        <td>
                            <a href="edit_item.php?id=<?php echo $item['id']; ?>" class="btn btn-primary" style="padding: 5px 10px; font-size: 12px;">Edit</a>
                            
                            <?php if (!isItemUsed($item['id'], $pdo)): ?>
                                <a href="delete_item.php?id=<?php echo $item['id']; ?>" 
                                   class="btn btn-danger" 
                                   style="padding: 5px 10px; font-size: 12px;"
                                   onclick="return confirmDelete('Are you sure you want to delete this item?');">
                                    Delete
                                </a>
                            <?php else: ?>
                                <button class="btn" style="padding: 5px 10px; font-size: 12px; background: #95a5a6; color: white;" disabled title="Cannot delete - item is used in requests">In Use</button>
                             <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">No items found. <a href="add_item.php">Add your first item</a></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>

<?php
include '../includes/footer.php';
?>