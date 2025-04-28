<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Require login
requireLogin();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'update_profile':
                    $name = sanitizeInput($_POST['name']);
                    $email = sanitizeInput($_POST['email']);
                    $language = sanitizeInput($_POST['language']);
                    
                    // Validate email
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        throw new Exception(lang('invalid_email'));
                    }
                    
                    // Check if email is already taken
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
                    $stmt->execute([$email, $_SESSION['user_id']]);
                    if ($stmt->fetchColumn() > 0) {
                        throw new Exception(lang('email_taken'));
                    }
                    
                    // Update profile
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, language = ? WHERE id = ?");
                    $stmt->execute([$name, $email, $language, $_SESSION['user_id']]);
                    
                    // Update session
                    $_SESSION['username'] = $name;
                    $_SESSION['email'] = $email;
                    
                    // Switch language if changed
                    if ($language !== $_SESSION['lang']) {
                        switchLanguage($language);
                    }
                    
                    $successMessage = lang('profile_updated');
                    logActivity($_SESSION['user_id'], "Updated profile");
                    break;
                    
                case 'change_password':
                    $currentPassword = $_POST['current_password'];
                    $newPassword = $_POST['new_password'];
                    $confirmPassword = $_POST['confirm_password'];
                    
                    // Verify current password
                    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $user = $stmt->fetch();
                    
                    if (!password_verify($currentPassword, $user['password'])) {
                        throw new Exception(lang('incorrect_password'));
                    }
                    
                    // Validate new password
                    if ($newPassword !== $confirmPassword) {
                        throw new Exception(lang('passwords_dont_match'));
                    }
                    
                    if (strlen($newPassword) < 8) {
                        throw new Exception(lang('password_too_short'));
                    }
                    
                    // Update password
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashedPassword, $_SESSION['user_id']]);
                    
                    $successMessage = lang('password_changed');
                    logActivity($_SESSION['user_id'], "Changed password");
                    break;
            }
        }
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
}

// Get user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    error_log("Get user data error: " . $e->getMessage());
    $errorMessage = lang('error_occurred');
}

$pageTitle = lang('profile');
require_once '../../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?php echo lang('profile_settings'); ?></h5>
                </div>
                <div class="card-body">
                    <?php if (isset($errorMessage)): ?>
                        <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($successMessage)): ?>
                        <div class="alert alert-success"><?php echo $successMessage; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="mb-3">
                            <label class="form-label"><?php echo lang('name'); ?></label>
                            <input type="text" class="form-control" name="name" 
                                   value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label"><?php echo lang('email'); ?></label>
                            <input type="email" class="form-control" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label"><?php echo lang('language'); ?></label>
                            <select class="form-select" name="language">
                                <option value="en" <?php echo $user['language'] === 'en' ? 'selected' : ''; ?>>
                                    English
                                </option>
                                <option value="fr" <?php echo $user['language'] === 'fr' ? 'selected' : ''; ?>>
                                    Français
                                </option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <?php echo lang('save_changes'); ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?php echo lang('change_password'); ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="mb-3">
                            <label class="form-label"><?php echo lang('current_password'); ?></label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label"><?php echo lang('new_password'); ?></label>
                            <input type="password" class="form-control" name="new_password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label"><?php echo lang('confirm_password'); ?></label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <?php echo lang('change_password'); ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?> 