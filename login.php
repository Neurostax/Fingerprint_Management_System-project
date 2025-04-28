<?php
require_once 'config/config.php';
require_once 'includes/auth.php';

// Check if user is already logged in
if (checkAuth()) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $code = $_POST['code'] ?? '';
    
    // First step: verify email and password
    if (empty($code)) {
        $user = verifyLogin($email, $password);
        
        if ($user) {
            if (!empty($user['two_factor_secret'])) {
                // User has 2FA enabled, show code input
                $_SESSION['2fa_user_id'] = $user['id'];
                $_SESSION['2fa_email'] = $user['email'];
            } else {
                // No 2FA, log in directly
                login($email, $password, isset($_POST['remember']));
                header('Location: index.php');
                exit();
            }
        } else {
            $error = lang('login_error');
        }
    } 
    // Second step: verify 2FA code
    else if (isset($_SESSION['2fa_user_id'])) {
        $user = getUserById($_SESSION['2fa_user_id']);
        
        if ($user && verify2FACode($user['two_factor_secret'], $code)) {
            login($email, $password, isset($_POST['remember']));
            unset($_SESSION['2fa_user_id']);
            unset($_SESSION['2fa_email']);
            header('Location: index.php');
            exit();
        } else {
            $error = "Invalid verification code.";
        }
    }
}

// Check if we're in 2FA step
$show2FA = isset($_SESSION['2fa_user_id']);
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo lang('login'); ?> - <?php echo lang('site_name'); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="<?php echo $theme; ?>-theme">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <i class="fas fa-fingerprint fa-3x text-primary"></i>
                            <h2 class="mt-3"><?php echo lang('login'); ?></h2>
                        </div>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="login.php">
                            <?php if (!$show2FA): ?>
                                <div class="mb-3">
                                    <label for="email" class="form-label"><?php echo lang('email'); ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label"><?php echo lang('password'); ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="mb-3">
                                    <label for="code" class="form-label"><?php echo lang('verification_code'); ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                                        <input type="text" class="form-control" id="code" name="code" required>
                                    </div>
                                    <small class="form-text text-muted">
                                        <?php echo lang('verification_code_instructions'); ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember"><?php echo lang('remember_me'); ?></label>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    <?php echo $show2FA ? lang('verify') : lang('login'); ?>
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-3">
                            <a href="forgot-password.php" class="text-decoration-none">
                                <?php echo lang('forgot_password'); ?>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <a href="index.php" class="text-decoration-none">
                        <i class="fas fa-arrow-left me-2"></i>
                        <?php echo lang('back'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
</body>
</html> 