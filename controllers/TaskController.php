<?php
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/User.php';

class TaskController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
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
        $validStatuses = ['done', 'missed', 'upcoming'];
        if (!in_array($data['status'], $validStatuses)) {
            return ['status' => false, 'error' => 'Invalid status value'];
        }

        $task = new Task($this->db);
        $task->task_id = $task_id;
        $task->user_id = $user_id;
        $task->type = $data['type'];
        $task->headline = $data['headline'];
        $task->purpose = $data['purpose'];
        $task->date = $data['date'];
        $task->status = $data['status'];

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

