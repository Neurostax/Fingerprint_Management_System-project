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
        // Return user settings
        echo json_encode([
            'language' => $user['language'],
            'theme' => $user['theme'],
            'notifications' => (bool)$user['notifications'],
            'email_notifications' => (bool)$user['email_notifications'],
            'two_factor_enabled' => !empty($user['two_factor_secret'])
        ]);
        break;
        
    case 'POST':
        // Update settings
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON data']);
            exit;
        }
        
        $language = $data['language'] ?? 'en';
        $theme = $data['theme'] ?? 'light';
        $notifications = isset($data['notifications']) ? (int)$data['notifications'] : 0;
        $email_notifications = isset($data['email_notifications']) ? (int)$data['email_notifications'] : 0;
        
        // Validate language
        $valid_languages = ['en', 'fr', 'ar', 'sw'];
        if (!in_array($language, $valid_languages)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid language']);
            exit;
        }
        
        // Validate theme
        $valid_themes = ['light', 'dark'];
        if (!in_array($theme, $valid_themes)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid theme']);
            exit;
        }
        
        // Update settings
        $stmt = $pdo->prepare("
            UPDATE users 
            SET language = ?, theme = ?, notifications = ?, email_notifications = ? 
            WHERE id = ?
        ");
        
        if ($stmt->execute([$language, $theme, $notifications, $email_notifications, $user['id']])) {
            // Update session
            $_SESSION['language'] = $language;
            $_SESSION['theme'] = $theme;
            
            echo json_encode([
                'message' => lang('settings_updated'),
                'settings' => [
                    'language' => $language,
                    'theme' => $theme,
                    'notifications' => (bool)$notifications,
                    'email_notifications' => (bool)$email_notifications
                ]
            ]);
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