<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: pending_requests.php");
    exit();
}

$request_id = $_POST['request_id'];
$action = $_POST['action'];
$detail_ids = $_POST['detail_ids'];

try {
    $pdo->beginTransaction();
    
    $all_issued = true;
    $any_issued = false;
    
    // to fix the buggy code
    foreach ($detail_ids as $detail_id) {
        $issue_qty = (int)$_POST['issue_qty_' . $detail_id];
        $requested_qty = (int)$_POST['requested_qty_' . $detail_id];
        
        if ($issue_qty > 0) {
            $any_issued = true;
            
            $stmt = $pdo->prepare("
                SELECT ird.item_id, i.current_stock 
                FROM issue_request_details ird
                JOIN items i ON ird.item_id = i.id
                WHERE ird.id = ?
            ");
            $stmt->execute([$detail_id]);
            $result = $stmt->fetch();
            $item_id = $result['item_id'];
            $current_stock = $result['current_stock'];

            // conditions to check before passing
            // cannot issue more than net quantity
            
            if ($issue_qty > $current_stock) {
                throw new Exception("Insufficient stock for one or more items");
            }

            // issued qty cannot be greater than req qty
            if ($issue_qty > $requested_qty) {
                throw new Exception("Cannot issue more than requested quantity");
            }
            
            //put the issue details
            $stmt = $pdo->prepare("
                UPDATE issue_request_details 
                SET qty_issued = qty_issued + ? 
                WHERE id = ?
            ");

            $stmt->execute([$issue_qty, $detail_id]);
            $stmt = $pdo->prepare("
                UPDATE items 
                SET current_stock = current_stock - ? 
                WHERE id = ?
            ");

            $stmt->execute([$issue_qty, $item_id]);
            
            $stmt = $pdo->prepare("
                SELECT qty_requested, qty_issued 
                FROM issue_request_details 
                WHERE id = ?
            ");
            $stmt->execute([$detail_id]);
            $detail = $stmt->fetch();
            
            if ($detail['qty_issued'] < $detail['qty_requested']) {
                $all_issued = false;
            }
        } else {
            $all_issued = false;
        }
    }
    
    if ($any_issued) {
        if ($all_issued || $action == 'full') {
            // issuing all items
            $new_status = 'issued';
        } else {
            $new_status = 'pending';
        }
        
        $stmt = $pdo->prepare("UPDATE issue_requests SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $request_id]);
        
        $pdo->commit();
        
        $_SESSION['success'] = "Items issued successfully!";
    } else {
        $pdo->rollBack();
        $_SESSION['error'] = "No items were issued";
    }
    
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Failed to issue items: " . $e->getMessage();
}

header("Location: pending_requests.php");
exit();
