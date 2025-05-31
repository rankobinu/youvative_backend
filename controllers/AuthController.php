<?php
class AuthController {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }

    public function register($data) {
        $user = new User($this->db);
        $user->email = $data['email'];
        if ($user->emailExists()) {
            return ['status' => false, 'message' => 'Email already exists'];
        }
        foreach ($data as $key => $val) {
            if ($key === 'password') {
                $user->password = password_hash($val, PASSWORD_BCRYPT); // hash ici
            } elseif ($key !== 'status' && property_exists($user, $key)) { // Skip status if provided
                $user->$key = $val;
            }
        }
        // Status will be set to "new subscriber" in the User::create() method
        
        $user_id = $user->create();
        if ($user_id) {
            $token = generateToken(['id' => $user_id, 'email' => $user->email, 'username' => $user->username]);
            return ['status' => true, 'message' => 'User registered', 'token' => $token];
        }
        return ['status' => false, 'message' => 'Registration failed'];
    }

    public function login($email, $password) {
        $user = new User($this->db);
        $user->email = $email;
        if ($user->findByEmail() && password_verify($password, $user->password)) {
            $token = generateToken([
                'id' => $user->id,
                'email' => $user->email,
                'username' => $user->username,
                'type' => $user->email === 'admin@admin.com' ? 'admin' : 'user'
            ]);
            return ['status' => true, 'message' => 'Login successful', 'token' => $token];
        }
        return ['status' => false, 'message' => 'Invalid credentials'];
    }

    public function forgotPassword($email) {
        $user = new User($this->db);
        $user->email = $email;
        if (!$user->emailExists()) {
            return ['status' => false, 'message' => 'Email not found'];
        }
        $newPassword = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
        $user->password = password_hash($newPassword, PASSWORD_BCRYPT);
        if ($user->updatePassword()) {
            return ['status' => true, 'message' => 'Password reset', 'new_password' => $newPassword];
        }
        return ['status' => false, 'message' => 'Reset failed'];
    }
}
