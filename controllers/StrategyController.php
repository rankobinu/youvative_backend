// controllers/StrategyController.php
<?php
require_once __DIR__ . '/../models/Strategy.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Subscription.php';

class StrategyController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    private function isUserActive($user_id) {
        $subscription = new Subscription($this->db);
        return $subscription->isActive($user_id);
    }

    public function getAllStrategies() {
        $strategy = new Strategy($this->db);
        $stmt = $strategy->getAllStrategies();

        $strategies = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $strategies[] = [
                'strategy_type' => $row['strategy_type'],
                'goal' => $row['goal'],
                'description' => $row['description']
            ];
        }

        return [
            'status' => true,
            'strategies' => $strategies
        ];
    }

    // ✅ FIXED: Correct argument order
    public function createStrategy($data, $user_id) {
        if (!$this->isUserActive($user_id)) {
            return ['status' => false, 'error' => 'User does not have an active subscription'];
        }

        $strategy = new Strategy($this->db);
        $strategy->user_id = $user_id;
        $strategy->strategy_type = $data['strategy_type'];
        $strategy->goal = $data['goal'];
        $strategy->description = $data['description'];

        $strategy_id = $strategy->create();

        if ($strategy_id) {
            return ['status' => true, 'message' => 'Strategy created successfully', 'strategy_id' => $strategy_id];
        }

        return ['status' => false, 'error' => 'Failed to create strategy'];
    }

    public function updateStrategy($strategy_id, $user_id, $data) {
        $strategy = new Strategy($this->db);
        $strategy->id = $strategy_id;
        $strategy->user_id = $user_id;
        $strategy->strategy_type = $data['strategy_type'];
        $strategy->goal = $data['goal'];
        $strategy->description = $data['description'];

        if ($strategy->update()) {
            return ['status' => true, 'message' => 'Strategy updated successfully'];
        }

        return ['status' => false, 'error' => 'Failed to update strategy'];
    }

    public function getUserStrategies($user_id) {
        $strategy = new Strategy($this->db);
        $strategy->user_id = $user_id;
        $stmt = $strategy->findByUserId();

        $strategies = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $strategies[] = [
                'strategy_type' => $row['strategy_type'],
                'goal' => $row['goal'],
                'description' => $row['description'],
                'created_at' => $row['created_at']
            ];
        }

        return [
            'status' => true,
            'strategies' => $strategies
        ];
    }

    public function verifyStrategyOwnership($strategy_id, $user_id) {
        $strategy = new Strategy($this->db);
        $strategy->id = $strategy_id;
        $strategy->user_id = $user_id;
        return $strategy->findById();
    }

    public function deleteStrategy($strategy_id) {
        $strategy = new Strategy($this->db);
        $strategy->id = $strategy_id;
        if ($strategy->delete()) {
            return ['status' => true, 'message' => 'Strategy deleted successfully'];
        }

        return ['status' => false, 'error' => 'Failed to delete strategy'];
    }
}




