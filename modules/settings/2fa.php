<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

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
    if (isset($_POST['enable_2fa'])) {
        // Generate new secret
        $secret = generate2FASecret();
        $qrCode = generate2FAQrCode($secret, $user['email']);
        
        // Store secret in database
        $stmt = $pdo->prepare("UPDATE users SET two_factor_secret = ? WHERE id = ?");
        $stmt->execute([$secret, $user['id']]);
        
        $message = "Two-factor authentication has been enabled. Please scan the QR code with your authenticator app.";
    } elseif (isset($_POST['disable_2fa'])) {
        // Remove 2FA
        $stmt = $pdo->prepare("UPDATE users SET two_factor_secret = NULL WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        $message = "Two-factor authentication has been disabled.";
    } elseif (isset($_POST['verify_code'])) {
        $code = $_POST['code'];
        $secret = $user['two_factor_secret'];
        
        if (verify2FACode($secret, $code)) {
            $message = "Two-factor authentication has been verified successfully.";
        } else {
            $error = "Invalid verification code.";
        }
    }
}

// Get current 2FA status
$has2FA = !empty($user['two_factor_secret']);
$qrCode = $has2FA ? generate2FAQrCode($user['two_factor_secret'], $user['email']) : '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <div class="container">
        <h1>Two-Factor Authentication</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <?php if (!$has2FA): ?>
                    <h2>Enable Two-Factor Authentication</h2>
                    <p>Two-factor authentication adds an extra layer of security to your account. You'll need to scan a QR code with an authenticator app like Google Authenticator or Authy.</p>
                    
                    <form method="post">
                        <button type="submit" name="enable_2fa" class="btn btn-primary">Enable 2FA</button>
                    </form>
                <?php else: ?>
                    <h2>Two-Factor Authentication is Enabled</h2>
                    <p>Scan this QR code with your authenticator app:</p>
                    <img src="<?php echo $qrCode; ?>" alt="QR Code" class="qr-code">
                    
                    <form method="post" class="mt-4">
                        <div class="form-group">
                            <label for="code">Verification Code</label>
                            <input type="text" id="code" name="code" class="form-control" required>
                        </div>
                        <button type="submit" name="verify_code" class="btn btn-primary">Verify Code</button>
                        <button type="submit" name="disable_2fa" class="btn btn-danger">Disable 2FA</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html> 