<?php
require_once '../config/db.php';
require_once '../config/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
   
    if (isItemUsed($id, $pdo)) {
        $_SESSION['error'] = "Cannot delete this item because it is used in requests";
        header("Location: view_items.php");
        exit();
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['success'] = "Item deleted successfully!";
        
    } catch (PDOException $e) {
        $_SESSION['error'] = "Failed to delete item: " . $e->getMessage();
    }
}

header("Location: view_items.php");
exit();
?>