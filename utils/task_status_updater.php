<?php

function updateMissedTasks($db, $user_id = null) {
    $today = date('Y-m-d');
    $updated = 0;
    
    try {
        $query = "UPDATE tasks 
                  SET status = 'missed' 
                  WHERE status = 'upcoming' 
                  AND date < :today";
        
        if ($user_id !== null) {
            $query .= " AND user_id = :user_id";
        }
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':today', $today);
        
        if ($user_id !== null) {
            $stmt->bindParam(':user_id', $user_id);
        }
        
        $stmt->execute();
        $updated = $stmt->rowCount();
        
        return [
            'success' => true,
            'updated_count' => $updated
        ];
        
    } catch (Exception $e) {
        error_log("Error updating missed tasks: " . $e->getMessage());
        return [
            'success' => false,
            'updated_count' => 0,
            'error' => $e->getMessage()
        ];
    }
}