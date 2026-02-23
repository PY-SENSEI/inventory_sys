<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_name = trim($_POST['item_name']);
    $unit = $_POST['unit'];
    $current_stock = (int)$_POST['current_stock'];
    
    // Validation
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
    
    // Check for duplicate item name
    $stmt = $pdo->prepare("SELECT id FROM items WHERE item_name = ?");
    $stmt->execute([$item_name]);
    if ($stmt->fetch()) {
        $errors[] = "Item with this name already exists";
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO items (item_name, unit, current_stock) VALUES (?, ?, ?)");
            $stmt->execute([$item_name, $unit, $current_stock]);
            
            $_SESSION['success'] = "Item added successfully!";
            header("Location: view_items.php");
            exit();
            
        } catch (PDOException $e) {
            $_SESSION['error'] = "Failed to add item: " . $e->getMessage();
            header("Location: add_item.php");
            exit();
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
        header("Location: add_item.php");
        exit();
    }
} else {
    header("Location: add_item.php");
    exit();
}
?>