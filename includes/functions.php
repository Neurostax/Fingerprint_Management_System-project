<?php
/**
 * Get language string
 */
function lang($key) {
    global $lang;
    return $lang[$key] ?? $key;
}

/**
 * Generate random string
 */
function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length));
}

/**
 * Format date
 */
function formatDate($date, $format = 'Y-m-d') {
    return date($format, strtotime($date));
}

/**
 * Format time
 */
function formatTime($time, $format = 'H:i') {
    return date($format, strtotime($time));
}

/**
 * Calculate attendance percentage
 */
function calculateAttendancePercentage($totalClasses, $attendedClasses) {
    if ($totalClasses === 0) return 0;
    return round(($attendedClasses / $totalClasses) * 100, 2);
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate password
 */
function isPasswordStrong($password) {
    return strlen($password) >= PASSWORD_MIN_LENGTH;
}

/**
 * Sanitize input
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Upload file
 */
function uploadFile($file, $targetDir, $allowedTypes = ALLOWED_IMAGE_TYPES) {
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new RuntimeException('Invalid file parameters.');
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('File upload failed.');
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        throw new RuntimeException('File is too large.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, $allowedTypes)) {
        throw new RuntimeException('Invalid file type.');
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = sprintf('%s.%s', generateRandomString(), $extension);
    $targetPath = $targetDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new RuntimeException('Failed to move uploaded file.');
    }

    return $filename;
}

/**
 * Log activity
 */
function logActivity($userId, $activity) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO logs (user_id, activity) VALUES (?, ?)");
        $stmt->execute([$userId, $activity]);
    } catch (PDOException $e) {
        error_log("Activity logging error: " . $e->getMessage());
    }
}

/**
 * Get user role name
 */
function getRoleName($role) {
    switch ($role) {
        case 'admin':
            return lang('role_admin');
        case 'lecturer':
            return lang('role_lecturer');
        case 'student':
            return lang('role_student');
        default:
            return ucfirst($role);
    }
}

/**
 * Get attendance status name
 */
function getAttendanceStatusName($status) {
    switch ($status) {
        case 'present':
            return lang('present');
        case 'absent':
            return lang('absent');
        case 'late':
            return lang('late');
        default:
            return ucfirst($status);
    }
}

/**
 * Generate class code
 */
function generateClassCode() {
    return strtoupper(substr(md5(uniqid()), 0, 6));
}

/**
 * Check if class is active
 */
function isClassActive($classId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM authorization_sessions 
            WHERE class_id = ? AND status = 'active' AND end_time IS NULL
        ");
        $stmt->execute([$classId]);
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log("Class status check error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get user's classes
 */
function getUserClasses($userId, $role) {
    global $pdo;
    
    try {
        if ($role === 'lecturer') {
            $stmt = $pdo->prepare("
                SELECT * FROM classes 
                WHERE lecturer_id = ? 
                ORDER BY date DESC, start_time DESC
            ");
        } else {
            $stmt = $pdo->prepare("
                SELECT c.* FROM classes c
                JOIN attendance a ON c.id = a.class_id
                WHERE a.student_id = ?
                ORDER BY c.date DESC, c.start_time DESC
            ");
        }
        
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Get user classes error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get class attendance
 */
function getClassAttendance($classId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT a.*, u.name as student_name 
            FROM attendance a
            JOIN users u ON a.student_id = u.id
            WHERE a.class_id = ?
            ORDER BY a.timestamp DESC
        ");
        
        $stmt->execute([$classId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Get class attendance error: " . $e->getMessage());
        return [];
    }
}

/**
 * Update a system setting in the database
 * @param string $key The setting key
 * @param mixed $value The setting value
 * @return bool True on success, false on failure
 */
function updateSetting($key, $value) {
    global $pdo;
    
    try {
        // Check if setting exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $exists = $stmt->fetchColumn() > 0;
        
        if ($exists) {
            // Update existing setting
            $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
            return $stmt->execute([$value, $key]);
        } else {
            // Insert new setting
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
            return $stmt->execute([$key, $value]);
        }
    } catch (PDOException $e) {
        error_log("Update setting error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get a system setting from the database
 * @param string $key The setting key
 * @param mixed $default Default value if setting not found
 * @return mixed The setting value or default value
 */
function getSetting($key, $default = null) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetchColumn();
        return $result !== false ? $result : $default;
    } catch (PDOException $e) {
        error_log("Get setting error: " . $e->getMessage());
        return $default;
    }
}

/**
 * Get all system settings
 * @return array Array of all settings
 */
function getAllSettings() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    } catch (PDOException $e) {
        error_log("Get all settings error: " . $e->getMessage());
        return [];
    }
}

/**
 * Switch the application language
 * @param string $lang Language code (e.g., 'en', 'fr')
 * @return bool True on success, false on failure
 */
function switchLanguage($lang) {
    global $pdo;
    
    try {
        // Check if language exists
        $langFile = BASE_PATH . '/languages/' . $lang . '.php';
        if (!file_exists($langFile)) {
            return false;
        }
        
        // Update user preference if logged in
        if (isset($_SESSION['user_id'])) {
            $stmt = $pdo->prepare("UPDATE users SET language = ? WHERE id = ?");
            $stmt->execute([$lang, $_SESSION['user_id']]);
        }
        
        // Update session
        $_SESSION['lang'] = $lang;
        
        // Update global language variable
        global $lang;
        $lang = require_once $langFile;
        
        return true;
    } catch (PDOException $e) {
        error_log("Language switch error: " . $e->getMessage());
        return false;
    }
}

// CSRF Protection
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }
    return true;
}

// Rate Limiting
function checkRateLimit($key, $limit = 5, $period = 60) {
    if (!isset($_SESSION['rate_limit'][$key])) {
        $_SESSION['rate_limit'][$key] = [
            'count' => 0,
            'reset' => time() + $period
        ];
    }
    
    if (time() > $_SESSION['rate_limit'][$key]['reset']) {
        $_SESSION['rate_limit'][$key] = [
            'count' => 0,
            'reset' => time() + $period
        ];
    }
    
    $_SESSION['rate_limit'][$key]['count']++;
    
    if ($_SESSION['rate_limit'][$key]['count'] > $limit) {
        return false;
    }
    
    return true;
}

// Password Complexity
function validatePassword($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number';
    }
    
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'Password must contain at least one special character';
    }
    
    return $errors;
}

// Session Regeneration
function regenerateSession() {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Check if session needs regeneration
function checkSessionRegeneration() {
    $regeneration_interval = 1800; // 30 minutes
    
    if (!isset($_SESSION['last_regeneration']) || 
        time() - $_SESSION['last_regeneration'] > $regeneration_interval) {
        regenerateSession();
    }
}

// Two-Factor Authentication
function generate2FASecret() {
    $g = new \Google\Authenticator\GoogleAuthenticator();
    return $g->generateSecret();
}

function generate2FAQrCode($secret, $email) {
    $g = new \Google\Authenticator\GoogleAuthenticator();
    return $g->getURL($email, 'Attendance System', $secret);
}

function verify2FACode($secret, $code) {
    $g = new \Google\Authenticator\GoogleAuthenticator();
    return $g->checkCode($secret, $code);
}

function send2FACode($email) {
    $code = rand(100000, 999999);
    $expires = date('Y-m-d H:i:s', strtotime('+5 minutes'));
    
    // Store code in database
    global $pdo;
    $stmt = $pdo->prepare("
        INSERT INTO two_factor_codes (user_id, code, expires_at) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$_SESSION['user_id'], $code, $expires]);
    
    // Send email
    $message = "Your two-factor authentication code is: " . $code;
    $message .= "\nThis code will expire in 5 minutes.";
    
    return sendEmail($email, 'Two-Factor Authentication Code', $message);
}

function verify2FACodeEmail($code) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT * FROM two_factor_codes 
        WHERE user_id = ? AND code = ? AND expires_at > NOW() AND used = 0
    ");
    $stmt->execute([$_SESSION['user_id'], $code]);
    $result = $stmt->fetch();
    
    if ($result) {
        // Mark code as used
        $stmt = $pdo->prepare("UPDATE two_factor_codes SET used = 1 WHERE id = ?");
        $stmt->execute([$result['id']]);
        return true;
    }
    
    return false;
}
?> 