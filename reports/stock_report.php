
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

// Get all items with basic info
$stmt = $pdo->query("
    SELECT 
        i.id,
        i.item_name,
        i.unit,
        i.current_stock,
        i.created_at,
        COALESCE(SUM(ird.qty_issued), 0) as total_issued,
        COALESCE(SUM(ird.qty_returned), 0) as total_returned
    FROM items i
    LEFT JOIN issue_request_details ird ON i.id = ird.item_id
    GROUP BY i.id
    ORDER BY i.item_name ASC
");
$items = $stmt->fetchAll();

// Simple calculations
$total_items = count($items);
$total_stock_value = 0;
$low_stock_count = 0;
$out_of_stock_count = 0;

foreach ($items as $item) {
    $total_stock_value += $item['current_stock'];
    if ($item['current_stock'] == 0) {
        $out_of_stock_count++;
    } elseif ($item['current_stock'] < 5) {
        $low_stock_count++;
    }
}
?>


<!-- Simple Header -->
<div style="margin-bottom: 20px;">
    <h1 style="color: #2c3e50; margin-bottom: 10px;">📊 Stock Report</h1>
    <p style="color: #7f8c8d;">View all items and their current stock levels</p>
</div>

<!-- Simple Stats Boxes -->
<div style="display: flex; gap: 15px; margin-bottom: 25px; flex-wrap: wrap;">
    <div style="background: #3498db; color: white; padding: 20px; border-radius: 10px; min-width: 150px; flex: 1;">
        <div style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">Total Items</div>
        <div style="font-size: 32px; font-weight: bold;"><?php echo $total_items; ?></div>
    </div>
    
    <div style="background: #2ecc71; color: white; padding: 20px; border-radius: 10px; min-width: 150px; flex: 1;">
        <div style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">Total Stock</div>
        <div style="font-size: 32px; font-weight: bold;"><?php echo $total_stock_value; ?></div>
    </div>
    
    <div style="background: #f39c12; color: white; padding: 20px; border-radius: 10px; min-width: 150px; flex: 1;">
        <div style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">Low Stock Items</div>
        <div style="font-size: 32px; font-weight: bold; color: <?php echo $low_stock_count > 0 ? '#fff' : '#fff'; ?>">
            <?php echo $low_stock_count; ?>
        </div>
        <div style="font-size: 12px;">(Less than 5 items)</div>
    </div>
    
    <div style="background: #e74c3c; color: white; padding: 20px; border-radius: 10px; min-width: 150px; flex: 1;">
        <div style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">Out of Stock</div>
        <div style="font-size: 32px; font-weight: bold;"><?php echo $out_of_stock_count; ?></div>
    </div>
</div>


<!-- Simple Search Box -->
<div style="margin-bottom: 15px;">
    <input type="text" id="searchInput" placeholder="🔍 Search for an item..." 
           style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 16px;">
</div>

<!-- Items Table -->
<div style="background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    <table style="width: 100%; border-collapse: collapse;">
        <!-- Table Header -->
        <thead style="background: #34495e; color: white;">
            <tr>
                <th style="padding: 15px; text-align: left;">Item Name</th>
                <th style="padding: 15px; text-align: left;">Unit</th>
                <th style="padding: 15px; text-align: center;">Current Stock</th>
                <th style="padding: 15px; text-align: center;">Status</th>
                <th style="padding: 15px; text-align: center;">Total Issued</th>
                <th style="padding: 15px; text-align: center;">Total Returned</th>
                <th style="padding: 15px; text-align: center;">Net Used</th>
                <th style="padding: 15px; text-align: center;">Action</th>
            </tr>
        </thead>
        
        <!-- Table Body -->
        <tbody id="itemTableBody">
            <?php if (count($items) > 0): ?>
                <?php foreach ($items as $item): 
                    $net_used = $item['total_issued'] - $item['total_returned'];
                    
                    // Determine stock status and colors
                    if ($item['current_stock'] == 0) {
                        $status = "Out of Stock";
                        $status_color = "#e74c3c";
                        $row_style = "background-color: #fdeded;";
                    } elseif ($item['current_stock'] < 5) {
                        $status = "Low Stock";
                        $status_color = "#e74c3c";
                        $row_style = "background-color: #fff3e0;";
                    } else {
                        $status = "Good";
                        $status_color = "#27ae60";
                        $row_style = "";
                    }
                ?>
                    <tr style="<?php echo $row_style; ?> border-bottom: 1px solid #eee;" class="item-row">
                        <td style="padding: 12px 15px; font-weight: bold;">
                            <?php echo htmlspecialchars($item['item_name']); ?>
                        </td>
                        <td style="padding: 12px 15px;"><?php echo $item['unit']; ?></td>
                        
                        <!-- Current Stock - Highlighted -->
                        <td style="padding: 12px 15px; text-align: center; font-weight: bold; font-size: 18px;">
                            <?php if ($item['current_stock'] < 5): ?>
                                <span style="background: #e74c3c; color: white; padding: 5px 10px; border-radius: 20px;">
                                    <?php echo $item['current_stock']; ?>
                                </span>
                            <?php else: ?>
                                <span style="background: #27ae60; color: white; padding: 5px 10px; border-radius: 20px;">
                                    <?php echo $item['current_stock']; ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        
                        <!-- Status Badge -->
                        <td style="padding: 12px 15px; text-align: center;">
                            <span style="background: <?php echo $status_color; ?>; color: white; padding: 5px 10px; border-radius: 20px; font-size: 12px;">
                                <?php echo $status; ?>
                            </span>
                        </td>
                        
                        <td style="padding: 12px 15px; text-align: center;"><?php echo $item['total_issued']; ?></td>
                        <td style="padding: 12px 15px; text-align: center;"><?php echo $item['total_returned']; ?></td>
                        <td style="padding: 12px 15px; text-align: center;"><?php echo $net_used; ?></td>
                        
                        <!-- Action Button -->
                        <td style="padding: 12px 15px; text-align: center;">
                            <a href="../requests/create_request.php?item_id=<?php echo $item['id']; ?>" 
                               style="background: #3498db; color: white; padding: 5px 15px; border-radius: 20px; text-decoration: none; font-size: 12px; display: inline-block;">
                                Request
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="padding: 40px; text-align: center; color: #7f8c8d;">
                        <p style="font-size: 18px; margin-bottom: 10px;">📦 No items found in inventory</p>
                        <p>Click the button below to add your first item</p>
                        <a href="../items/add_item.php" style="background: #3498db; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; display: inline-block; margin-top: 10px;">
                            + Add New Item
                        </a>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Quick Actions -->
<div style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
    <button onclick="printReport()" style="background: #95a5a6; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
        🖨️ Print Report
    </button>

</div>

<!-- Simple JavaScript for Search -->
<script>
// Simple search function
document.getElementById('searchInput').addEventListener('keyup', function() {
    let searchText = this.value.toLowerCase();
    let rows = document.getElementsByClassName('item-row');
    
    for (let row of rows) {
        let itemName = row.cells[0].textContent.toLowerCase();
        if (itemName.includes(searchText)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
});

// Simple print function
function printReport() {
    window.print();
}


// Simple explanation popup
function showHelp() {
    alert(
        'How to read this report:\n\n' +
        '• Red numbers = Low stock (less than 5) - Order more!\n' +
        '• Green numbers = Good stock level\n' +
        '• Issued = Items given out\n' +
        '• Returned = Items came back\n' +
        '• Net Used = Items still out (Issued - Returned)'
    );
}
</script>

<!-- Simple Print Styles -->
<style media="print">
    .sidebar, .btn, #searchInput, button, .alert {
        display: none !important;
    }
    .main-content {
        margin-left: 0 !important;
        padding: 20px !important;
    }
    table {
        border: 1px solid #000;
    }
    th {
        background: #ddd !important;
        color: #000 !important;
    }
</style>

<!-- Add a Help Button -->
<div style="position: fixed; bottom: 20px; right: 20px;">
    <button onclick="showHelp()" style="background: #3498db; color: white; width: 50px; height: 50px; border: none; border-radius: 50%; font-size: 24px; cursor: pointer; box-shadow: 0 2px 10px rgba(0,0,0,0.2);">
        ?
    </button>
</div>

<?php
include '../includes/footer.php';
?>