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
$language = $data['language'] ?? '';

// Validate language
$validLanguages = ['en', 'sw', 'fr', 'ar'];
if (!in_array($language, $validLanguages)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid language']);
    exit();
}

try {
    // Update language in database
    $stmt = $pdo->prepare("UPDATE users SET language = ? WHERE id = ?");
    $stmt->execute([$language, $_SESSION['user_id']]);
    
    // Update session
    $_SESSION['lang'] = $language;
    
    echo json_encode(['success' => true, 'message' => 'Language updated successfully']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    error_log("Language update error: " . $e->getMessage());
}
?> 