<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id, $email, $username, $password, $instagram, $location, $goal,
        $occupation, $comment, $strategy_type, $status, $subscription_id, $created_at, $avatar;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO $this->table_name
        (email, username, password, instagram, location, goal, occupation, comment, strategy_type, status, avatar, created_at)
        VALUES
        (:email, :username, :password, :instagram, :location, :goal, :occupation, :comment, :strategy_type, :status, :avatar, NOW())
        ";

        $stmt = $this->conn->prepare($query);
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->username = htmlspecialchars(strip_tags($this->username));
        // Password is already hashed when passed here
        $this->instagram = htmlspecialchars(strip_tags($this->instagram));
        $this->location = htmlspecialchars(strip_tags($this->location));
        $this->goal = htmlspecialchars(strip_tags($this->goal));
        $this->occupation = htmlspecialchars(strip_tags($this->occupation));
        $this->comment = htmlspecialchars(strip_tags($this->comment));
        $this->strategy_type = htmlspecialchars(strip_tags($this->strategy_type));
        $this->status = "new subscriber"; // Changed from "active" to "new subscriber"
        $this->avatar = "https://api.dicebear.com/7.x/avataaars/svg?seed=" . urlencode($this->email);


        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":instagram", $this->instagram);
        $stmt->bindParam(":location", $this->location);
        $stmt->bindParam(":goal", $this->goal);
        $stmt->bindParam(":occupation", $this->occupation);
        $stmt->bindParam(":comment", $this->comment);
        $stmt->bindParam(":strategy_type", $this->strategy_type);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":avatar", $this->avatar);

        return $stmt->execute() ? $this->conn->lastInsertId() : false;
    }

    public function findByEmail() {
        $query = "SELECT * FROM $this->table_name WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();
        if ($stmt->rowCount()) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            foreach ($row as $key => $value) {
                $this->$key = $value;
            }
            return true;
        }
        return false;
    }

    public function findById() {
        $query = "SELECT * FROM $this->table_name WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        if ($stmt->rowCount()) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            foreach ($row as $key => $value) {
                $this->$key = $value;
            }
            return true;
        }
        return false;
    }

    public function updatePassword() {
        $query = "UPDATE $this->table_name SET password = :password WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":email", $this->email);
        return $stmt->execute();
    }

    public function update() {
        $query = "UPDATE $this->table_name SET
            email = :email,
            username = :username,
            instagram = :instagram,
            location = :location,
            goal = :goal,
            occupation = :occupation,
            comment = :comment,
            strategy_type = :strategy_type,
            status = :status
            WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->instagram = htmlspecialchars(strip_tags($this->instagram));
        $this->location = htmlspecialchars(strip_tags($this->location));
        $this->goal = htmlspecialchars(strip_tags($this->goal));
        $this->occupation = htmlspecialchars(strip_tags($this->occupation));
        $this->comment = htmlspecialchars(strip_tags($this->comment));
        $this->strategy_type = htmlspecialchars(strip_tags($this->strategy_type));
        $this->status = htmlspecialchars(strip_tags($this->status));

        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":instagram", $this->instagram);
        $stmt->bindParam(":location", $this->location);
        $stmt->bindParam(":goal", $this->goal);
        $stmt->bindParam(":occupation", $this->occupation);
        $stmt->bindParam(":comment", $this->comment);
        $stmt->bindParam(":strategy_type", $this->strategy_type);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    public function emailExists() {
        $query = "SELECT id FROM $this->table_name WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}

