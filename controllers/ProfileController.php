// controllers/ProfileController.php
<?php

require_once __DIR__ . '/../models/User.php'; // 👈 ajoute cette ligne

class ProfileController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getProfile($userId) {
        $user = new User($this->db);
        $user->id = $userId;

        if ($user->findById()) {
            return [
                'status' => true,
                'data' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'username' => $user->username,
                    'instagram' => $user->instagram,
                    'location' => $user->location,
                    'goal' => $user->goal,
                    'occupation' => $user->occupation,
                    'comment' => $user->comment,
                    'strategy_type' => $user->strategy_type,
                    'status' => $user->status,
                ]
            ];
        }

        return ['status' => false, 'message' => 'User not found'];
    }

    public function updateProfile($userId, $data) {
        $user = new User($this->db);
        $user->id = $userId;

        if (!$user->findById()) {
            return ['status' => false, 'message' => 'User not found'];
        }

        foreach ($data as $key => $val) {
            if (property_exists($user, $key)) {
                $user->$key = htmlspecialchars(strip_tags($val));
            }
        }

        if ($user->update()) {
            return ['status' => true, 'message' => 'Profile updated successfully'];
        }

        return ['status' => false, 'message' => 'Profile update failed'];
    }
}
