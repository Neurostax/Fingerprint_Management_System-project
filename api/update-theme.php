<?php
require_once '../config/config.php';
require_once '../includes/auth.php';

// Check if user is logged in
if (!checkAuth()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$theme = $data['theme'] ?? '';

// Validate theme
if (!in_array($theme, ['light', 'dark'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid theme']);
    exit();
}

try {
    // Update theme in database
    $stmt = $pdo->prepare("UPDATE users SET theme = ? WHERE id = ?");
    $stmt->execute([$theme, $_SESSION['user_id']]);
    
    // Update session
    $_SESSION['theme'] = $theme;
    
    echo json_encode(['success' => true, 'message' => 'Theme updated successfully']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    error_log("Theme update error: " . $e->getMessage());
}
?> 