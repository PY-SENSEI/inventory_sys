<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $item_name = trim($_POST['item_name']);
    $unit = $_POST['unit'];
    $current_stock = (int)$_POST['current_stock'];
    
    
    $errors = [];
    
    if (empty($item_name)) {
        $errors[] = "Item name is required";
    }
    
    if (empty($unit)) {
        $errors[] = "Unit is required";
    }
    
    if ($current_stock < 0) {
        $errors[] = "Stock cannot be negative";
    }
    
    
    $stmt = $pdo->prepare("SELECT id FROM items WHERE item_name = ? AND id != ?");
    $stmt->execute([$item_name, $id]);
    if ($stmt->fetch()) {
        $errors[] = "Another item with this name already exists";
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE items SET item_name = ?, unit = ?, current_stock = ? WHERE id = ?");
            $stmt->execute([$item_name, $unit, $current_stock, $id]);
            
            $_SESSION['success'] = "Item updated successfully!";
            header("Location: view_items.php");
            exit();
            
        } catch (PDOException $e) {
            $_SESSION['error'] = "Failed to update item: " . $e->getMessage();
            header("Location: edit_item.php?id=" . $id);
            exit();
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
        header("Location: edit_item.php?id=" . $id);
        exit();
    }
} else {
    header("Location: view_items.php");
    exit();
}
?>