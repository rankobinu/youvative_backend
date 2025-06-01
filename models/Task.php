<?php
class Task {
    private $conn;
    public $task_id, $user_id, $type, $headline, $purpose, $date, $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO tasks (user_id, type, headline, purpose, date, status) 
                  VALUES (:user_id, :type, :headline, :purpose, :date, :status)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':user_id' => $this->user_id,
            ':type' => $this->type,
            ':headline' => $this->headline,
            ':purpose' => $this->purpose,
            ':date' => $this->date,
            ':status' => $this->status ?? 'pending'
        ]);
    }

    public function findByUserId() {
        $stmt = $this->conn->prepare("SELECT * FROM tasks WHERE user_id = ?");
        $stmt->execute([$this->user_id]);
        return $stmt;
    }

    public function delete() {
        $stmt = $this->conn->prepare("DELETE FROM tasks WHERE id = ?");
        return $stmt->execute([$this->task_id]);
    }

    public function update() {
        $query = "UPDATE tasks SET type = :type, headline = :headline, purpose = :purpose, date = :date, status = :status 
                  WHERE id = :task_id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':type' => $this->type,
            ':headline' => $this->headline,
            ':purpose' => $this->purpose,
            ':date' => $this->date,
            ':status' => $this->status,
            ':task_id' => $this->task_id,
            ':user_id' => $this->user_id
        ]);
    }
}
