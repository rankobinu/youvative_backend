<?php
class Subscription {
    private $conn;
    private $table_name = "subscriptions";

    // Subscription properties
    public $id;
    public $user_id;
    public $customer_id;
    public $start_date;
    public $end_date;
    public $plan;
    public $card_token;
    public $expiry_date;
    public $cvv;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create subscription
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, customer_id, start_date, end_date, plan, card_token, expiry_date, cvv) 
                  VALUES 
                  (:user_id, :customer_id, :start_date, :end_date, :plan, :card_token, :expiry_date, :cvv)";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->customer_id = htmlspecialchars(strip_tags($this->customer_id));
        $this->start_date = htmlspecialchars(strip_tags($this->start_date));
        $this->end_date = htmlspecialchars(strip_tags($this->end_date));
        $this->plan = htmlspecialchars(strip_tags($this->plan));
        $this->card_token = htmlspecialchars(strip_tags($this->card_token));
        $this->expiry_date = htmlspecialchars(strip_tags($this->expiry_date));
        $this->cvv = htmlspecialchars(strip_tags($this->cvv));

        // Bind parameters
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":customer_id", $this->customer_id);
        $stmt->bindParam(":start_date", $this->start_date);
        $stmt->bindParam(":end_date", $this->end_date);
        $stmt->bindParam(":plan", $this->plan);
        $stmt->bindParam(":card_token", $this->card_token);
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
            $this->customer_id = $row['customer_id'];
            $this->start_date = $row['start_date'];
            $this->end_date = $row['end_date'];
            $this->plan = $row['plan'];
            $this->card_token = $row['card_token'];
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
                    plan = :plan,
                    card_token = :card_token,
                    expiry_date = :expiry_date,
                    cvv = :cvv
                WHERE 
                    user_id = :user_id
                ORDER BY id DESC
                LIMIT 1";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->end_date = htmlspecialchars(strip_tags($this->end_date));
        $this->plan = htmlspecialchars(strip_tags($this->plan));
        $this->card_token = htmlspecialchars(strip_tags($this->card_token));
        $this->expiry_date = htmlspecialchars(strip_tags($this->expiry_date));
        $this->cvv = htmlspecialchars(strip_tags($this->cvv));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));

        // Bind parameters
        $stmt->bindParam(":end_date", $this->end_date);
        $stmt->bindParam(":plan", $this->plan);
        $stmt->bindParam(":card_token", $this->card_token);
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
}
?>
