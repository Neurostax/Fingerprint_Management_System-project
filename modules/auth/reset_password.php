<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$error = '';
$success = '';
$token = isset($_GET['token']) ? $_GET['token'] : '';

if (empty($token)) {
    redirect('login.php');
}

// Verify token
$stmt = $pdo->prepare("
    SELECT pr.*, u.email 
    FROM password_resets pr 
    JOIN users u ON pr.user_id = u.id 
    WHERE pr.token = ? AND pr.expires_at > NOW() AND pr.used = 0
");
$stmt->execute([$token]);
$reset = $stmt->fetch();

if (!$reset) {
    $error = lang('invalid_reset_token');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    if (empty($password) || empty($confirm_password)) {
        $error = lang('password_required');
    } elseif ($password !== $confirm_password) {
        $error = lang('passwords_dont_match');
    } else {
        // Validate password strength
        $errors = validatePassword($password);
        if (!empty($errors)) {
            $error = implode('<br>', $errors);
        } else {
            // Update password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $pdo->beginTransaction();
            
            try {
                // Update user password
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $reset['user_id']]);
                
                // Mark token as used
                $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
                $stmt->execute([$reset['id']]);
                
                $pdo->commit();
                
                // Send confirmation email
                $message = "Your password has been successfully reset.";
                sendEmail($reset['email'], 'Password Reset Confirmation', $message);
                
                $success = lang('password_reset_success');
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = lang('password_reset_failed');
            }
        }
    }
}

$page_title = lang('reset_password');
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-center"><?php echo lang('reset_password'); ?></h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                        <div class="text-center">
                            <a href="login.php" class="btn btn-primary">
                                <?php echo lang('back_to_login'); ?>
                            </a>
                        </div>
                    <?php elseif (!$error): ?>
                        <form method="POST">
                            <div class="form-group">
                                <label for="password"><?php echo lang('new_password'); ?></label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <div class="form-group mt-3">
                                <label for="confirm_password"><?php echo lang('confirm_password'); ?></label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <div class="form-group mt-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <?php echo lang('reset_password'); ?>
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 