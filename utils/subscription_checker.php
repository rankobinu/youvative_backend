<?php
/**
 * Helper function to check and update user subscription status
 * Call this function at the beginning of authenticated API endpoints
 */
function checkAndUpdateSubscriptionStatus($user_id, $db) {
    // Check if user has an active subscription
    $query = "SELECT s.end_date, u.status 
              FROM subscriptions s 
              JOIN users u ON s.user_id = u.id 
              WHERE s.user_id = :user_id 
              ORDER BY s.end_date DESC 
              LIMIT 1";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $end_date = $row['end_date'];
        $current_status = $row['status'];
        
        // If subscription has expired and user is not already inactive
        if ($end_date < date('Y-m-d') && $current_status != 'inactive') {
            // Update user status to inactive
            $updateQuery = "UPDATE users SET status = 'inactive' WHERE id = :user_id";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->bindParam(':user_id', $user_id);
            $updateStmt->execute();
            
            // Log the status change
            error_log("User ID $user_id status changed to inactive due to expired subscription");
            
            return true; // Status was updated
        }
    }
    
    return false; // No update needed
}

/**
 * Batch update subscription statuses for all users
 * Call this before fetching lists of users to ensure statuses are current
 * 
 * @param PDO $db Database connection
 * @return array Statistics about the update operation
 */
function updateAllUserSubscriptionStatuses($db) {
    $today = date('Y-m-d');
    $updated = 0;
    
    try {
        // Find users with expired subscriptions who are still active
        $query = "SELECT u.id 
                  FROM users u 
                  JOIN subscriptions s ON u.id = s.user_id 
                  WHERE u.status IN ('active', 'new subscriber', 'resubscribed') 
                  AND s.end_date < :today 
                  AND NOT EXISTS (
                      SELECT 1 FROM subscriptions s2 
                      WHERE s2.user_id = u.id 
                      AND s2.end_date >= :today
                  )";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':today', $today);
        $stmt->execute();
        
        $expiredUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($expiredUsers) > 0) {
            // Update user statuses to inactive
            $updateQuery = "UPDATE users SET status = 'inactive' WHERE id = :user_id";
            $updateStmt = $db->prepare($updateQuery);
            
            foreach ($expiredUsers as $user) {
                $updateStmt->bindParam(':user_id', $user['id']);
                $updateStmt->execute();
                $updated++;
            }
        }
        
        // Also check for users who have renewed their subscription but are still marked as inactive
        $renewedQuery = "SELECT u.id 
                        FROM users u 
                        JOIN subscriptions s ON u.id = s.user_id 
                        WHERE u.status = 'inactive' 
                        AND s.end_date >= :today 
                        ORDER BY s.end_date DESC";
        
        $renewedStmt = $db->prepare($renewedQuery);
        $renewedStmt->bindParam(':today', $today);
        $renewedStmt->execute();
        
        $renewedUsers = $renewedStmt->fetchAll(PDO::FETCH_ASSOC);
        $renewedCount = 0;
        
        if (count($renewedUsers) > 0) {
            // Update user statuses to resubscribed
            $updateRenewedQuery = "UPDATE users SET status = 'resubscribed' WHERE id = :user_id";
            $updateRenewedStmt = $db->prepare($updateRenewedQuery);
            
            foreach ($renewedUsers as $user) {
                $updateRenewedStmt->bindParam(':user_id', $user['id']);
                $updateRenewedStmt->execute();
                $renewedCount++;
            }
        }
        
        return [
            'expired_updated' => $updated,
            'renewed_updated' => $renewedCount,
            'total_updated' => $updated + $renewedCount
        ];
        
    } catch (Exception $e) {
        error_log("Error updating subscription statuses: " . $e->getMessage());
        return [
            'expired_updated' => 0,
            'renewed_updated' => 0,
            'total_updated' => 0,
            'error' => $e->getMessage()
        ];
    }
}