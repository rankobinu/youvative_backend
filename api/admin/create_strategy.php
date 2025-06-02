<?php
// Include DB connection
include_once 'config/database.php'; // make sure this path is correct

header("Content-Type: application/json");

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);

// Check required fields
if (!isset($data['user_id']) || !isset($data['strategy_type'])) {
    echo json_encode(["error" => "Missing required fields."]);
    exit;
}

$user_id = htmlspecialchars(strip_tags($data['user_id']));
$strategy_type = htmlspecialchars(strip_tags($data['strategy_type']));
// Add more fields if necessary, e.g., $start_date = $data['start_date']

try {
    // Initialize DB
    $database = new Database();
    $conn = $database->getConnection();

    // Insert strategy (example query, adjust table/columns as needed)
    $insertQuery = "INSERT INTO strategies (user_id, strategy_type, created_at) 
                    VALUES (:user_id, :strategy_type, NOW())";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':strategy_type', $strategy_type);

    if ($stmt->execute()) {
        // Strategy successfully created

        // If it's a monthly strategy, activate user
        if (strtolower($strategy_type) === 'monthly') {
            $updateQuery = "UPDATE users SET status = 'active' WHERE id = :user_id";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bindParam(':user_id', $user_id);
            $updateStmt->execute();
        }

        echo json_encode(["message" => "Strategy created and user activated (if monthly)."]);
    } else {
        echo json_encode(["error" => "Failed to create strategy."]);
    }

} catch (PDOException $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
