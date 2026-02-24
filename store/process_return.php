<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: return_form.php");
    exit();
}

$detail_ids = $_POST['detail_ids'];
$item_ids = $_POST['item_ids'];
$request_ids = $_POST['request_ids'];

try {
    
    $pdo->beginTransaction();
    
    $any_returned = false;
    $return_summary = [];
    $updated_requests = [];
    
    // each id returns
    for ($i = 0; $i < count($detail_ids); $i++) {
        $detail_id = $detail_ids[$i];
        $item_id = $item_ids[$i];
        $request_id = $request_ids[$i];
        $return_qty = (int)$_POST['return_qty_' . $detail_id];
        
        if ($return_qty > 0) {
            $any_returned = true;
            
            //get the data from db and not coming from POST req.
            $stmt = $pdo->prepare("
                SELECT 
                    ird.qty_issued, 
                    ird.qty_returned,
                    i.item_name,
                    i.current_stock
                FROM issue_request_details ird
                JOIN items i ON ird.item_id = i.id
                WHERE ird.id = ?
            ");
            $stmt->execute([$detail_id]);
            $current = $stmt->fetch();
            
            if (!$current) {
                throw new Exception("Invalid return detail ID: $detail_id");
            }
            
            $qty_issued = $current['qty_issued'];
            $qty_returned = $current['qty_returned'];
            $item_name = $current['item_name'];
            $current_stock = $current['current_stock'];
            
            $pending_return = $qty_issued - $qty_returned;
            
            if ($return_qty > $pending_return) {
                throw new Exception(
                    "Cannot return $return_qty of $item_name. Only $pending_return units pending return."
                );
            }
            
            if ($return_qty < 0) {
                throw new Exception("Return quantity cannot be negative");
            }
            
            $new_total_returned = $qty_returned + $return_qty;
            if ($new_total_returned > $qty_issued) {
                throw new Exception(
                    "Total returned ($new_total_returned) cannot exceed issued quantity ($qty_issued)"
                );
            }
            
            $stmt = $pdo->prepare("
                UPDATE issue_request_details 
                SET qty_returned = qty_returned + ? 
                WHERE id = ?
            ");
            $stmt->execute([$return_qty, $detail_id]);
            
            
            $stmt = $pdo->prepare("
                UPDATE items 
                SET current_stock = current_stock + ? 
                WHERE id = ?
            ");
            $stmt->execute([$return_qty, $item_id]);
            
           
            $return_summary[] = [
                'item_name' => $item_name,
                'qty' => $return_qty
            ];
            
            $updated_requests[$request_id] = true;
        }
    }
    
    if ($any_returned) {
      
        foreach (array_keys($updated_requests) as $req_id) {
            
            $stmt = $pdo->prepare("
                SELECT 
                    SUM(qty_issued) as total_issued,
                    SUM(qty_returned) as total_returned
                FROM issue_request_details 
                WHERE request_id = ?
            ");
            $stmt->execute([$req_id]);
            $result = $stmt->fetch();
            
            if ($result['total_issued'] == $result['total_returned']) {
               
                $new_status = 'returned';
            } else {
                // Partially returned
                $new_status = 'issued';
            }
            
            $stmt = $pdo->prepare("UPDATE issue_requests SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $req_id]);
        }
        
       
        $pdo->commit();
        
        // Create success message with summary
        $summary_text = "Items returned successfully:\n";
        foreach ($return_summary as $return) {
            $summary_text .= "- {$return['item_name']}: {$return['qty']} units\n";
        }
        
        $_SESSION['success'] = nl2br($summary_text);
        error_log("RETURN PROCESSED: " . date('Y-m-d H:i:s') . " - " . json_encode($return_summary));
        
    } else {
        $pdo->rollBack();
        $_SESSION['error'] = "No items were returned";
    }
    
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Failed to process returns: " . $e->getMessage();
    
   
    error_log("RETURN ERROR: " . $e->getMessage());
}

header("Location: return_form.php");
exit();