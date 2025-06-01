<?php
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/task_status_updater.php';

class TaskController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Updates the status of overdue tasks before performing any task operations
     * 
     * @param int|null $user_id Optional user ID to update tasks for a specific user only
     */
    private function updateOverdueTasks($user_id = null) {
        updateMissedTasks($this->db, $user_id);
    }

    public function createTask($user_id, $data) {
        $validStatuses = ['done', 'missed', 'upcoming'];
        $status = $data['status'] ?? 'upcoming';

        if (!in_array($status, $validStatuses)) {
            return ['status' => false, 'error' => 'Invalid status value'];
        }

        $task = new Task($this->db);
        $task->user_id = $user_id;
        $task->type = $data['type'];
        $task->headline = $data['headline'];
        $task->purpose = $data['purpose'];
        $task->date = $data['date'];
        $task->status = $status;

        if ($task->create()) {
            return ['status' => true, 'message' => 'Task created'];
        }

        return ['status' => false, 'error' => 'Failed to create task'];
    }

    public function getUserTasks($user_id) {
        // Update overdue tasks before fetching
        $this->updateOverdueTasks($user_id);
        
        $task = new Task($this->db);
        $task->user_id = $user_id;
        $stmt = $task->findByUserId();

        $tasks = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tasks[] = $row;
        }

        return ['status' => true, 'tasks' => $tasks];
    }

    public function updateTask($user_id, $task_id, $data) {
        // Update overdue tasks before updating a specific task
        $this->updateOverdueTasks($user_id);
        
        // First, get the current task data
        $currentTask = new Task($this->db);
        $currentTask->task_id = $task_id;
        $currentTask->user_id = $user_id;
        
        $stmt = $this->db->prepare("SELECT * FROM tasks WHERE task_id = ? AND user_id = ?");
        $stmt->execute([$task_id, $user_id]);
        $taskData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$taskData) {
            return ['status' => false, 'error' => 'Task not found'];
        }
        
        // Validate status if it's being updated
        if (isset($data['status'])) {
            $validStatuses = ['done', 'missed', 'upcoming'];
            if (!in_array($data['status'], $validStatuses)) {
                return ['status' => false, 'error' => 'Invalid status value'];
            }
        }

        // Create a new task object with the current values
        $task = new Task($this->db);
        $task->task_id = $task_id;
        $task->user_id = $user_id;
        
        // Only update fields that are provided in the request
        $task->type = $data['type'] ?? $taskData['type'];
        $task->headline = $data['headline'] ?? $taskData['headline'];
        $task->purpose = $data['purpose'] ?? $taskData['purpose'];
        $task->date = $data['date'] ?? $taskData['date'];
        $task->status = $data['status'] ?? $taskData['status'];

        if ($task->update()) {
            return ['status' => true, 'message' => 'Task updated'];
        }

        return ['status' => false, 'error' => 'Failed to update task'];
    }

    public function deleteTask($user_id, $task_id) {
        $task = new Task($this->db);
        $task->task_id = $task_id;
        $task->user_id = $user_id;

        if ($task->delete()) {
            return ['status' => true, 'message' => 'Task deleted'];
        }

        return ['status' => false, 'error' => 'Failed to delete task'];
    }
}

