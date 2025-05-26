<?php
// test_controller.php
require_once 'config/database.php';
require_once 'controllers/TaskController.php';
require_once 'models/Task.php';
require_once 'models/MonthlyGoal.php';
require_once 'controllers/StrategyController.php';

// Database connection
$database = new Database();
$db = $database->getConnection();

// Create task controller
$taskController = new TaskController($db);

// Test if the class and methods exist
echo "TaskController class exists: " . (class_exists('TaskController') ? 'Yes' : 'No') . "<br>";
echo "getDashboardData method exists: " . (method_exists($taskController, 'getDashboardData') ? 'Yes' : 'No') . "<br>";
echo "getPerformanceData method exists: " . (method_exists($taskController, 'getPerformanceData') ? 'Yes' : 'No') . "<br>";

// List all methods in the class
echo "Methods in TaskController: <pre>";
print_r(get_class_methods($taskController));
echo "</pre>";