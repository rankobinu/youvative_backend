<?php
class MonthlyGoal {
    private $conn;
    public $goal_id, $user_id, $month, $year, $description, $target_tasks;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO monthly_goals (user_id, month, year, description, target_tasks) 
                  VALUES (:user_id, :month, :year, :description, :target_tasks)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':month', $this->month);
        $stmt->bindParam(':year', $this->year);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':target_tasks', $this->target_tasks);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function getCurrentMonthGoal($user_id) {
        $month = date('m');
        $year = date('Y');
        
        $query = "SELECT * FROM monthly_goals 
                  WHERE user_id = :user_id AND month = :month AND year = :year";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':month', $month);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}