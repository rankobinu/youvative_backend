// controllers/UserController.php
<?php
require_once 'models/User.php';
require_once 'models/Subscription.php';
require_once 'models/Strategy.php';

class UserController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getProfile($user_id) {
        $user = new User($this->db);
        $user->id = $user_id;

        if ($user->findById()) {
            // Get subscription information
            $subscription = new Subscription($this->db);
            $subscription->user_id = $user_id;

            $subscription_info = [];
            if ($subscription->findByUserId()) {
                $subscription_info = [
                    'id' => $subscription->id,
                    'plan' => $subscription->plan,
                    'start_date' => $subscription->start_date,
                    'end_date' => $subscription->end_date,
                    'days_left' => $subscription->getDaysLeft(),
                    'is_active' => $subscription->isActive()
                ];
            }

            // Get strategies
            $strategy = new Strategy($this->db);
            $strategy->user_id = $user_id;
            $stmt = $strategy->findByUserId();

            $strategies = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $strategies[] = [
                    'id' => $row['id'],
                    'strategy_type' => $row['strategy_type'],
                    'goal' => $row['goal'],
                    'description' => $row['description']
                ];
            }

            return [
                'status' => true,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'instagram' => $user->instagram,
                    'location' => $user->location,
                    'avatar_image' => $user->avatar_image,
                    'goal' => $user->goal,
                    'occupation' => $user->occupation,
                    'comment' => $user->comment,
                    'strategy_type' => $user->strategy_type,
                    'status' => $user->status
                ],
                'subscription' => $subscription_info,
                'strategies' => $strategies
            ];
        } else {
            return [
                'status' => false,
                'message' => 'User not found'
            ];
        }
    }

    public function updateProfile($user_id, $userData) {
        $user = new User($this->db);
        $user->id = $user_id;

        if ($user->findById()) {
            // Update user data
            $user->username = $userData['username'] ?? $user->username;
            $user->instagram = $userData['instagram'] ?? $user->instagram;
            $user->location = $userData['location'] ?? $user->location;
            $user->goal = $userData['goal'] ?? $user->goal;
            $user->occupation = $userData['occupation'] ?? $user->occupation;
            $user->comment = $userData['comment'] ?? $user->comment;
            $user->strategy_type = $userData['strategy_type'] ?? $user->strategy_type;

            if ($user->update()) {
                return [
                    'status' => true,
                    'message' => 'Profile updated successfully',
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'instagram' => $user->instagram,
                        'location' => $user->location,
                        'avatar_image' => $user->avatar_image,
                        'goal' => $user->goal,
                        'occupation' => $user->occupation,
                        'comment' => $user->comment,
                        'strategy_type' => $user->strategy_type
                    ]
                ];
            } else {
                return [
                    'status' => false,
                    'message' => 'Unable to update profile'
                ];
            }
        } else {
            return [
                'status' => false,
                'message' => 'User not found'
            ];
        }
    }

    public function completeForm($user_id, $formData) {
        $user = new User($this->db);
        $user->id = $user_id;

        if ($user->findById()) {
            // Update user form data
            $user->instagram = $formData['instagram'];
            $user->location = $formData['location'];
            $user->goal = $formData['goal'];
            $user->occupation = $formData['occupation'];
            $user->comment = $formData['comment'];

            if ($user->update()) {
                return [
                    'status' => true,
                    'message' => 'Form submitted successfully',
                    'user' => [
                        'id' => $user->id,
                        'instagram' => $user->instagram,
                        'location' => $user->location,
                        'goal' => $user->goal,
                        'occupation' => $user->occupation,
                        'comment' => $user->comment
                    ]
                ];
            } else {
                return [
                    'status' => false,
                    'message' => 'Unable to submit form'
                ];
            }
        } else {
            return [
                'status' => false,
                'message' => 'User not found'
            ];
        }
    }
}
?>

