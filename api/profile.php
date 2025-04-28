<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get current user
$user = getCurrentUser();

// Handle different HTTP methods
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Return user profile data
        echo json_encode([
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'role' => $user['role'],
            'avatar' => $user['avatar'],
            'language' => $user['language'],
            'theme' => $user['theme'],
            'notifications' => (bool)$user['notifications'],
            'email_notifications' => (bool)$user['email_notifications'],
            'two_factor_enabled' => !empty($user['two_factor_secret'])
        ]);
        break;
        
    case 'POST':
        // Update profile
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON data']);
            exit;
        }
        
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone'] ?? '');
        
        // Validate input
        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['error' => lang('name_required')]);
            exit;
        }
        
        if (!isValidEmail($email)) {
            http_response_code(400);
            echo json_encode(['error' => lang('invalid_email')]);
            exit;
        }
        
        // Check if email is already taken by another user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user['id']]);
        if ($stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['error' => lang('email_taken')]);
            exit;
        }
        
        // Update profile
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
        if ($stmt->execute([$name, $email, $phone, $user['id']])) {
            // Update session
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
            
            echo json_encode([
                'message' => lang('profile_updated'),
                'user' => [
                    'id' => $user['id'],
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => lang('update_failed')]);
        }
        break;
        
    case 'PUT':
        // Update password
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON data']);
            exit;
        }
        
        $current_password = $data['current_password'] ?? '';
        $new_password = $data['new_password'] ?? '';
        $confirm_password = $data['confirm_password'] ?? '';
        
        // Validate input
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            http_response_code(400);
            echo json_encode(['error' => lang('all_fields_required')]);
            exit;
        }
        
        if ($new_password !== $confirm_password) {
            http_response_code(400);
            echo json_encode(['error' => lang('passwords_dont_match')]);
            exit;
        }
        
        if (!password_verify($current_password, $user['password'])) {
            http_response_code(400);
            echo json_encode(['error' => lang('current_password_incorrect')]);
            exit;
        }
        
        // Validate new password strength
        $password_errors = validatePassword($new_password);
        if (!empty($password_errors)) {
            http_response_code(400);
            echo json_encode(['error' => implode(', ', $password_errors)]);
            exit;
        }
        
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt->execute([$hashed_password, $user['id']])) {
            echo json_encode(['message' => lang('password_updated')]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => lang('update_failed')]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
} 