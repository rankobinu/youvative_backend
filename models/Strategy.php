// models/Strategy.php
<?php
class Strategy {
    private $conn;
    private $table_name = "strategies";

    // Strategy properties
    public $id;
    public $user_id;
    public $strategy_type;
    public $goal;
    public $description;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create strategy
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, strategy_type, goal, description, created_at) 
                  VALUES 
                  (:user_id, :strategy_type, :goal, :description, NOW())";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->strategy_type = htmlspecialchars(strip_tags($this->strategy_type));
        $this->goal = htmlspecialchars(strip_tags($this->goal));
        $this->description = htmlspecialchars(strip_tags($this->description));

        // Bind parameters
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":strategy_type", $this->strategy_type);
        $stmt->bindParam(":goal", $this->goal);
        $stmt->bindParam(":description", $this->description);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Read strategy by ID
    public function findById() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->user_id = $row['user_id'];
            $this->strategy_type = $row['strategy_type'];
            $this->goal = $row['goal'];
            $this->description = $row['description'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

    // Read strategies by user ID
    public function findByUserId() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->execute();

        return $stmt;
    }

    // Update strategy
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET 
                    strategy_type = :strategy_type,
                    goal = :goal,
                    description = :description
                WHERE 
                    id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->strategy_type = htmlspecialchars(strip_tags($this->strategy_type));
        $this->goal = htmlspecialchars(strip_tags($this->goal));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind parameters
        $stmt->bindParam(":strategy_type", $this->strategy_type);
        $stmt->bindParam(":goal", $this->goal);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get all available strategies
    public function getAllStrategies() {
        $query = "SELECT DISTINCT strategy_type, goal, description FROM " . $this->table_name . " 
                  WHERE strategy_type = 'general' ORDER BY goal";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Delete strategy
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }
}
