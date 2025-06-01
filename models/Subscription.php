<?php
class Subscription {
    private $conn;
    private $table_name = "subscriptions";

    // Subscription properties
    public $id;
    public $user_id;
    public $start_date;
    public $end_date;
    public $plan_type;
    public $card_number;
    public $expiry_date;
    public $cvv;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create subscription
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, start_date, end_date, plan_type, card_number, expiry_date, cvv) 
                  VALUES 
                  (:user_id, :start_date, :end_date, :plan_type, :card_number, :expiry_date, :cvv)";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->start_date = htmlspecialchars(strip_tags($this->start_date));
        $this->end_date = htmlspecialchars(strip_tags($this->end_date));
        $this->plan_type = htmlspecialchars(strip_tags($this->plan_type));
        $this->card_number = htmlspecialchars(strip_tags($this->card_number));
        $this->expiry_date = htmlspecialchars(strip_tags($this->expiry_date));
        $this->cvv = htmlspecialchars(strip_tags($this->cvv));

        // Bind parameters
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":start_date", $this->start_date);
        $stmt->bindParam(":end_date", $this->end_date);
        $stmt->bindParam(":plan_type", $this->plan_type);
        $stmt->bindParam(":card_number", $this->card_number);
        $stmt->bindParam(":expiry_date", $this->expiry_date);
        $stmt->bindParam(":cvv", $this->cvv);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Read subscription by user ID
    public function findByUserId() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :user_id ORDER BY end_date DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->start_date = $row['start_date'];
            $this->end_date = $row['end_date'];
            $this->plan_type = $row['plan_type'];
            $this->card_number = $row['card_number'];
            $this->expiry_date = $row['expiry_date'];
            $this->cvv = $row['cvv'];
            return true;
        }
        return false;
    }

    // Update subscription
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET 
                    end_date = :end_date,
                    plan_type = :plan_type,
                    card_number = :card_number,
                    expiry_date = :expiry_date,
                    cvv = :cvv
                WHERE 
                    user_id = :user_id
                ORDER BY id DESC
                LIMIT 1";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->end_date = htmlspecialchars(strip_tags($this->end_date));
        $this->plan_type = htmlspecialchars(strip_tags($this->plan_type));
        $this->card_number = htmlspecialchars(strip_tags($this->card_number));
        $this->expiry_date = htmlspecialchars(strip_tags($this->expiry_date));
        $this->cvv = htmlspecialchars(strip_tags($this->cvv));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));

        // Bind parameters
        $stmt->bindParam(":end_date", $this->end_date);
        $stmt->bindParam(":plan_type", $this->plan_type);
        $stmt->bindParam(":card_number", $this->card_number);
        $stmt->bindParam(":expiry_date", $this->expiry_date);
        $stmt->bindParam(":cvv", $this->cvv);
        $stmt->bindParam(":user_id", $this->user_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Check if user has an active subscription
    public function isActive($user_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE user_id = :user_id 
                  AND end_date >= CURDATE()
                  ORDER BY end_date DESC 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Get days left in subscription
    public function getDaysLeft() {
        $today = new DateTime();
        $end = new DateTime($this->end_date);
        $interval = $today->diff($end);

        return max(0, $interval->days);
    }

    public function getCurrentSubscription() {
        if (!$this->findByUserId()) {
            return null;
        }
        
        return [
            'id' => $this->id,
            'plan_type' => $this->plan_type,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'days_left' => $this->getDaysLeft(),
            'is_active' => $this->isActive($this->user_id),
            'card_last_four' => substr($this->card_number, -4)
        ];
    }
}
?>
