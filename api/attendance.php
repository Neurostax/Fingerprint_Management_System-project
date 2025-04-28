<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get class ID from URL
$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;

// Verify user has access to this class
if ($class_id > 0) {
    if ($_SESSION['role'] === 'student') {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM class_enrollments 
            WHERE class_id = ? AND student_id = ?
        ");
        $stmt->execute([$class_id, $_SESSION['user_id']]);
        if ($stmt->fetchColumn() == 0) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit;
        }
    } elseif ($_SESSION['role'] === 'lecturer') {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM classes 
            WHERE id = ? AND lecturer_id = ?
        ");
        $stmt->execute([$class_id, $_SESSION['user_id']]);
        if ($stmt->fetchColumn() == 0) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit;
        }
    }
}

switch ($method) {
    case 'GET':
        // Get attendance records
        $params = [];
        $sql = "
            SELECT a.*, u.name as student_name, u.reg_number 
            FROM attendance a 
            JOIN users u ON a.student_id = u.id 
            WHERE 1=1
        ";
        
        if ($class_id > 0) {
            $sql .= " AND a.class_id = ?";
            $params[] = $class_id;
        }
        
        if ($_SESSION['role'] === 'student') {
            $sql .= " AND a.student_id = ?";
            $params[] = $_SESSION['user_id'];
        }
        
        $sql .= " ORDER BY a.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $attendance = $stmt->fetchAll();
        
        echo json_encode($attendance);
        break;
        
    case 'POST':
        // Mark attendance
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['class_id']) || !isset($data['status'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }
        
        // Verify student is enrolled in this class
        if ($_SESSION['role'] === 'student') {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM class_enrollments 
                WHERE class_id = ? AND student_id = ?
            ");
            $stmt->execute([$data['class_id'], $_SESSION['user_id']]);
            if ($stmt->fetchColumn() == 0) {
                http_response_code(403);
                echo json_encode(['error' => 'Forbidden']);
                exit;
            }
        }
        
        // Check if already marked attendance today
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM attendance 
            WHERE class_id = ? AND student_id = ? AND DATE(created_at) = CURDATE()
        ");
        $stmt->execute([$data['class_id'], $_SESSION['user_id']]);
        
        if ($stmt->fetchColumn() > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Attendance already marked for today']);
            exit;
        }
        
        // Mark attendance
        $stmt = $pdo->prepare("
            INSERT INTO attendance (class_id, student_id, status, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$data['class_id'], $_SESSION['user_id'], $data['status']]);
        
        // Send email notification
        $student = getUserById($_SESSION['user_id']);
        $class = getClassById($data['class_id']);
        $message = "Attendance marked for {$class['name']} on " . date('Y-m-d H:i');
        sendEmail($student['email'], 'Attendance Marked', $message);
        
        echo json_encode(['success' => true]);
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
} 