<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Require admin role
requireRole('admin');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'update_general':
                    $site_name = sanitizeInput($_POST['site_name']);
                    $site_description = sanitizeInput($_POST['site_description']);
                    $default_language = sanitizeInput($_POST['default_language']);
                    $timezone = sanitizeInput($_POST['timezone']);
                    
                    updateSetting('site_name', $site_name);
                    updateSetting('site_description', $site_description);
                    updateSetting('default_language', $default_language);
                    updateSetting('timezone', $timezone);
                    
                    $successMessage = lang('settings_updated');
                    logActivity($_SESSION['user_id'], "Updated general settings");
                    break;
                    
                case 'update_attendance':
                    $qr_duration = (int)$_POST['qr_duration'];
                    $late_threshold = (int)$_POST['late_threshold'];
                    $auto_approve = isset($_POST['auto_approve']) ? 1 : 0;
                    $notify_lecturer = isset($_POST['notify_lecturer']) ? 1 : 0;
                    
                    updateSetting('qr_duration', $qr_duration);
                    updateSetting('late_threshold', $late_threshold);
                    updateSetting('auto_approve', $auto_approve);
                    updateSetting('notify_lecturer', $notify_lecturer);
                    
                    $successMessage = lang('settings_updated');
                    logActivity($_SESSION['user_id'], "Updated attendance settings");
                    break;
                    
                case 'update_email':
                    $smtp_host = sanitizeInput($_POST['smtp_host']);
                    $smtp_port = (int)$_POST['smtp_port'];
                    $smtp_username = sanitizeInput($_POST['smtp_username']);
                    $smtp_password = $_POST['smtp_password'];
                    $smtp_encryption = sanitizeInput($_POST['smtp_encryption']);
                    $from_email = sanitizeInput($_POST['from_email']);
                    $from_name = sanitizeInput($_POST['from_name']);
                    
                    updateSetting('smtp_host', $smtp_host);
                    updateSetting('smtp_port', $smtp_port);
                    updateSetting('smtp_username', $smtp_username);
                    if (!empty($smtp_password)) {
                        updateSetting('smtp_password', $smtp_password);
                    }
                    updateSetting('smtp_encryption', $smtp_encryption);
                    updateSetting('from_email', $from_email);
                    updateSetting('from_name', $from_name);
                    
                    $successMessage = lang('settings_updated');
                    logActivity($_SESSION['user_id'], "Updated email settings");
                    break;
            }
        }
    } catch (PDOException $e) {
        error_log("Settings update error: " . $e->getMessage());
        $errorMessage = lang('error_occurred');
    }
}

// Get current settings
try {
    $settings = [];
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $e) {
    error_log("Get settings error: " . $e->getMessage());
    $errorMessage = lang('error_occurred');
}

$pageTitle = lang('system_settings');
require_once '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4"><?php echo lang('system_settings'); ?></h1>
        </div>
    </div>

    <!-- Settings Tabs -->
    <div class="row">
        <div class="col-12">
            <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="general-tab" data-bs-toggle="tab" 
                            data-bs-target="#general" type="button" role="tab">
                        <i class="fas fa-cog me-2"></i>
                        <?php echo lang('general_settings'); ?>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="attendance-tab" data-bs-toggle="tab" 
                            data-bs-target="#attendance" type="button" role="tab">
                        <i class="fas fa-user-check me-2"></i>
                        <?php echo lang('attendance_settings'); ?>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="email-tab" data-bs-toggle="tab" 
                            data-bs-target="#email" type="button" role="tab">
                        <i class="fas fa-envelope me-2"></i>
                        <?php echo lang('email_settings'); ?>
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="settingsTabContent">
                <!-- General Settings -->
                <div class="tab-pane fade show active" id="general" role="tabpanel">
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="update_general">
                                
                                <div class="mb-3">
                                    <label class="form-label"><?php echo lang('site_name'); ?></label>
                                    <input type="text" class="form-control" name="site_name" 
                                           value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label"><?php echo lang('site_description'); ?></label>
                                    <textarea class="form-control" name="site_description" rows="3"><?php 
                                        echo htmlspecialchars($settings['site_description'] ?? ''); 
                                    ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label"><?php echo lang('default_language'); ?></label>
                                    <select class="form-select" name="default_language" required>
                                        <option value="en" <?php echo ($settings['default_language'] ?? '') === 'en' ? 'selected' : ''; ?>>
                                            English
                                        </option>
                                        <option value="fr" <?php echo ($settings['default_language'] ?? '') === 'fr' ? 'selected' : ''; ?>>
                                            Français
                                        </option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label"><?php echo lang('timezone'); ?></label>
                                    <select class="form-select" name="timezone" required>
                                        <?php
                                        $timezones = DateTimeZone::listIdentifiers();
                                        foreach ($timezones as $tz) {
                                            $selected = ($settings['timezone'] ?? '') === $tz ? 'selected' : '';
                                            echo "<option value=\"$tz\" $selected>$tz</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <?php echo lang('save_changes'); ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Attendance Settings -->
                <div class="tab-pane fade" id="attendance" role="tabpanel">
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="update_attendance">
                                
                                <div class="mb-3">
                                    <label class="form-label"><?php echo lang('qr_duration'); ?></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="qr_duration" 
                                               value="<?php echo (int)($settings['qr_duration'] ?? 5); ?>" min="1" max="60" required>
                                        <span class="input-group-text"><?php echo lang('minutes'); ?></span>
                                    </div>
                                    <small class="form-text text-muted">
                                        <?php echo lang('qr_duration_help'); ?>
                                    </small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label"><?php echo lang('late_threshold'); ?></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="late_threshold" 
                                               value="<?php echo (int)($settings['late_threshold'] ?? 15); ?>" min="1" max="60" required>
                                        <span class="input-group-text"><?php echo lang('minutes'); ?></span>
                                    </div>
                                    <small class="form-text text-muted">
                                        <?php echo lang('late_threshold_help'); ?>
                                    </small>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="auto_approve" 
                                               id="auto_approve" <?php echo ($settings['auto_approve'] ?? 0) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="auto_approve">
                                            <?php echo lang('auto_approve_attendance'); ?>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        <?php echo lang('auto_approve_help'); ?>
                                    </small>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="notify_lecturer" 
                                               id="notify_lecturer" <?php echo ($settings['notify_lecturer'] ?? 0) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="notify_lecturer">
                                            <?php echo lang('notify_lecturer_late'); ?>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        <?php echo lang('notify_lecturer_help'); ?>
                                    </small>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <?php echo lang('save_changes'); ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Email Settings -->
                <div class="tab-pane fade" id="email" role="tabpanel">
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="update_email">
                                
                                <div class="mb-3">
                                    <label class="form-label"><?php echo lang('smtp_host'); ?></label>
                                    <input type="text" class="form-control" name="smtp_host" 
                                           value="<?php echo htmlspecialchars($settings['smtp_host'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label"><?php echo lang('smtp_port'); ?></label>
                                    <input type="number" class="form-control" name="smtp_port" 
                                           value="<?php echo (int)($settings['smtp_port'] ?? 587); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label"><?php echo lang('smtp_username'); ?></label>
                                    <input type="text" class="form-control" name="smtp_username" 
                                           value="<?php echo htmlspecialchars($settings['smtp_username'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label"><?php echo lang('smtp_password'); ?></label>
                                    <input type="password" class="form-control" name="smtp_password" 
                                           placeholder="<?php echo lang('leave_blank_unchanged'); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label"><?php echo lang('smtp_encryption'); ?></label>
                                    <select class="form-select" name="smtp_encryption" required>
                                        <option value="tls" <?php echo ($settings['smtp_encryption'] ?? '') === 'tls' ? 'selected' : ''; ?>>
                                            TLS
                                        </option>
                                        <option value="ssl" <?php echo ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : ''; ?>>
                                            SSL
                                        </option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label"><?php echo lang('from_email'); ?></label>
                                    <input type="email" class="form-control" name="from_email" 
                                           value="<?php echo htmlspecialchars($settings['from_email'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label"><?php echo lang('from_name'); ?></label>
                                    <input type="text" class="form-control" name="from_name" 
                                           value="<?php echo htmlspecialchars($settings['from_name'] ?? ''); ?>" required>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <?php echo lang('save_changes'); ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?> 