<?php
function isItemUsed($item_id, $pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM issue_request_details WHERE item_id = ?");
    $stmt->execute([$item_id]);
    return $stmt->fetchColumn() > 0;
}

function getRequestStatus($status) {
    switch($status) {
        case 'pending':
            return '<span class="badge badge-warning">Pending</span>';
        case 'issued':
            return '<span class="badge badge-success">Issued</span>';
        case 'returned':
            return '<span class="badge badge-info">Returned</span>';
        default:
            return '<span class="badge badge-secondary">Unknown</span>';
    }
}

function getItemName($item_id, $pdo) {
    $stmt = $pdo->prepare("SELECT item_name FROM items WHERE id = ?");
    $stmt->execute([$item_id]);
    $result = $stmt->fetch();
    return $result ? $result['item_name'] : 'Unknown';
}

function formatDate($date){
   return date('d-m-Y H:i', strtotime($date));
}
