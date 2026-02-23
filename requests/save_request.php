<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: create_request.php");
    exit();
}

$requester_name = trim($_POST['requester_name']);
$item_ids = $_POST['item_id'];
$quantities = $_POST['quantity'];

// VALIDATE
$errors = [];

if (empty($requester_name)) {
    $errors[] = "Requester name is required";
}

$valid_items = [];
foreach ($item_ids as $key => $item_id) {
    if (!empty($item_id) && !empty($quantities[$key])) {
        $qty = (int)$quantities[$key];
        if ($qty > 0) {
            // Check stock availability
            $stmt = $pdo->prepare("SELECT current_stock FROM items WHERE id = ?");
            $stmt->execute([$item_id]);
            $stock = $stmt->fetchColumn();
            
            if ($qty > $stock) {
                $errors[] = "Requested quantity for item ID $item_id exceeds available stock ($stock)";
            } else {
                $valid_items[] = [
                    'item_id' => $item_id,
                    'quantity' => $qty
                ];
            }
        }
    }
}

if (count($valid_items) == 0) {
    $errors[] = "At least one valid item with quantity is required";
}

if (empty($errors)) {
    try {
        
        $pdo->beginTransaction();
        
        
        $stmt = $pdo->prepare("INSERT INTO issue_requests (requester_name) VALUES (?)");
        $stmt->execute([$requester_name]);
        $request_id = $pdo->lastInsertId();
        
      
        $stmt = $pdo->prepare("INSERT INTO issue_request_details (request_id, item_id, qty_requested) VALUES (?, ?, ?)");
        foreach ($valid_items as $item) {
            $stmt->execute([$request_id, $item['item_id'], $item['quantity']]);
        }
        
        $pdo->commit();
        
        $_SESSION['success'] = "Request created successfully! Request #: " . $request_id;
        header("Location: my_requests.php");
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Failed to create request: " . $e->getMessage();
        header("Location: create_request.php");
        exit();
    }
} else {
    $_SESSION['error'] = implode("<br>", $errors);
    header("Location: create_request.php");
    exit();
}