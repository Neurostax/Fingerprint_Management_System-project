<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    if (empty($email)) {
        $error = lang('email_required');
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Store token in database
            $stmt = $pdo->prepare("
                INSERT INTO password_resets (user_id, token, expires_at) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$user['id'], $token, $expires]);
            
            // Send reset email
            $reset_link = SITE_URL . "/reset_password.php?token=" . $token;
            $message = "Click the following link to reset your password: " . $reset_link;
            $message .= "\n\nThis link will expire in 1 hour.";
            
            if (sendEmail($email, 'Password Reset', $message)) {
                $success = lang('reset_email_sent');
            } else {
                $error = lang('email_send_failed');
            }
        } else {
            // Don't reveal if email exists or not
            $success = lang('reset_email_sent');
        }
    }
}

$page_title = lang('forgot_password');
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-center"><?php echo lang('forgot_password'); ?></h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php else: ?>
                        <form method="POST">
                            <div class="form-group">
                                <label for="email"><?php echo lang('email'); ?></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            
                            <div class="form-group mt-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <?php echo lang('reset_password'); ?>
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                    
                    <div class="text-center mt-3">
                        <a href="login.php"><?php echo lang('back_to_login'); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 