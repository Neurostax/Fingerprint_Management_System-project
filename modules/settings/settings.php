<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$user = getCurrentUser();
$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_settings'])) {
        $language = $_POST['language'];
        $theme = $_POST['theme'];
        $notifications = isset($_POST['notifications']) ? 1 : 0;
        $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
        
        // Update settings
        $stmt = $pdo->prepare("
            UPDATE users 
            SET language = ?, theme = ?, notifications = ?, email_notifications = ? 
            WHERE id = ?
        ");
        
        if ($stmt->execute([$language, $theme, $notifications, $email_notifications, $user['id']])) {
            $message = lang('settings_updated');
            // Update session
            $_SESSION['language'] = $language;
            $_SESSION['theme'] = $theme;
            // Refresh user data
            $user = getCurrentUser();
        } else {
            $error = lang('update_failed');
        }
    }
}

$page_title = lang('settings');
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?php echo lang('settings'); ?></h5>
                    
                    <form method="post">
                        <!-- Language Settings -->
                        <div class="mb-4">
                            <h6><?php echo lang('language_settings'); ?></h6>
                            <div class="form-group">
                                <label for="language"><?php echo lang('language'); ?></label>
                                <select class="form-control" id="language" name="language">
                                    <option value="en" <?php echo $user['language'] === 'en' ? 'selected' : ''; ?>>
                                        English
                                    </option>
                                    <option value="fr" <?php echo $user['language'] === 'fr' ? 'selected' : ''; ?>>
                                        Français
                                    </option>
                                    <option value="ar" <?php echo $user['language'] === 'ar' ? 'selected' : ''; ?>>
                                        العربية
                                    </option>
                                    <option value="sw" <?php echo $user['language'] === 'sw' ? 'selected' : ''; ?>>
                                        Kiswahili
                                    </option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Theme Settings -->
                        <div class="mb-4">
                            <h6><?php echo lang('theme_settings'); ?></h6>
                            <div class="form-group">
                                <label for="theme"><?php echo lang('theme'); ?></label>
                                <select class="form-control" id="theme" name="theme">
                                    <option value="light" <?php echo $user['theme'] === 'light' ? 'selected' : ''; ?>>
                                        <?php echo lang('light_theme'); ?>
                                    </option>
                                    <option value="dark" <?php echo $user['theme'] === 'dark' ? 'selected' : ''; ?>>
                                        <?php echo lang('dark_theme'); ?>
                                    </option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Notification Settings -->
                        <div class="mb-4">
                            <h6><?php echo lang('notification_settings'); ?></h6>
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" id="notifications" name="notifications" 
                                       <?php echo $user['notifications'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="notifications">
                                    <?php echo lang('enable_notifications'); ?>
                                </label>
                            </div>
                            
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="email_notifications" 
                                       name="email_notifications" 
                                       <?php echo $user['email_notifications'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="email_notifications">
                                    <?php echo lang('enable_email_notifications'); ?>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Two-Factor Authentication -->
                        <div class="mb-4">
                            <h6><?php echo lang('security_settings'); ?></h6>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0"><?php echo lang('two_factor_auth'); ?></h6>
                                    <small class="text-muted">
                                        <?php echo lang('two_factor_auth_description'); ?>
                                    </small>
                                </div>
                                <a href="2fa.php" class="btn btn-outline-primary">
                                    <?php echo empty($user['two_factor_secret']) ? 
                                        lang('enable_2fa') : lang('manage_2fa'); ?>
                                </a>
                            </div>
                        </div>
                        
                        <button type="submit" name="update_settings" class="btn btn-primary">
                            <?php echo lang('save_settings'); ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 