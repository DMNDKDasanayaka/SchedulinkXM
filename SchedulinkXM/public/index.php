<?php
session_start();

// Load configuration and database connection
require_once '../app/config/database.php';

// Load the Router
require_once '../app/core/Router.php';

$router = new Router();

// Authentication routes
$router->add('GET', '/login', 'AuthController', 'login');
$router->add('POST', '/login', 'AuthController', 'authenticate');
$router->add('GET', '/logout', 'AuthController', 'logout');

// Exam routes
$router->add('GET', '/exams', 'ExamController', 'index');
$router->add('GET', '/exams/create', 'ExamController', 'create');
$router->add('POST', '/exams/store', 'ExamController', 'store');
$router->add('GET', '/exams/edit/{id}', 'ExamController', 'edit');
$router->add('POST', '/exams/update/{id}', 'ExamController', 'update');
$router->add('POST', '/exams/delete/{id}', 'ExamController', 'delete');

// Lecturer routes
$router->add('GET', '/lecturers', 'LecturerController', 'index');
$router->add('GET', '/lecturers/create', 'LecturerController', 'create');
$router->add('POST', '/lecturers/store', 'LecturerController', 'store');

// Hall routes
$router->add('GET', '/halls', 'HallController', 'index');
$router->add('GET', '/halls/create', 'HallController', 'create');
$router->add('POST', '/halls/store', 'HallController', 'store');

// Report routes
$router->add('GET', '/reports/allocations', 'ReportController', 'allocations');
$router->add('POST', '/reports/generate', 'ReportController', 'generate');

// Dashboard
$router->add('GET', '/dashboard', 'DashboardController', 'index');
$router->add('GET', '/', 'DashboardController', 'index');

// Get the requested URI and method
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Route the request
$router->route($uri, $method);
?>