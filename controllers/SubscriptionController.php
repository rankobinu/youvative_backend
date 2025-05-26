// controllers/SubscriptionController.php
<?php
require_once __DIR__ . '/../models/Subscription.php';
require_once __DIR__ . '/../utils/tokenization.php'; // for tokenizeCardInfo()

class SubscriptionController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function createSubscription($user_id, $data) {
        $subscription = new Subscription($this->db);
        $subscription->user_id = $user_id;
        $subscription->customer_id = uniqid('cus_');
        $subscription->start_date = date('Y-m-d');
        $subscription->end_date = date('Y-m-d', strtotime('+30 days'));
        $subscription->plan = $data['plan'];
        $subscription->card_token = tokenizeCardInfo($data['card_number']);
        $subscription->expiry_date = $data['expiry_date'];
        $subscription->cvv = $data['cvv'];

        $subscription_id = $subscription->create();
        if ($subscription_id) {
            return [
                'status' => true,
                'message' => 'Subscription created',
                'subscription_id' => $subscription_id
            ];
        }
        return ['status' => false, 'message' => 'Subscription creation failed'];
    }

    public function renewSubscription($user_id, $data) {
        $subscription = new Subscription($this->db);
        $subscription->user_id = $user_id;

        if (!$subscription->findByUserId()) {
            return ['status' => false, 'message' => 'No existing subscription found'];
        }

        $subscription->end_date = date('Y-m-d', strtotime('+30 days'));
        $subscription->plan = $data['plan'];
        $subscription->card_token = tokenizeCardInfo($data['card_number']);
        $subscription->expiry_date = $data['expiry_date'];
        $subscription->cvv = $data['cvv'];

        if ($subscription->update()) {
            return ['status' => true, 'message' => 'Subscription renewed'];
        }
        return ['status' => false, 'message' => 'Renewal failed'];
    }

    public function getSubscription($user_id) {
        $subscription = new Subscription($this->db);
        $subscription->user_id = $user_id;

        if ($subscription->findByUserId()) {
            return [
                'status' => true,
                'data' => [
                    'plan' => $subscription->plan,
                    'start_date' => $subscription->start_date,
                    'end_date' => $subscription->end_date,
                    'days_left' => $subscription->getDaysLeft()
                ]
            ];
        }

        return ['status' => false, 'message' => 'No active subscription'];
    }
}




