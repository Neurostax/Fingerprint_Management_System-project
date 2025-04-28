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
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        
        // Validate input
        if (empty($name)) {
            $error = lang('name_required');
        } elseif (!isValidEmail($email)) {
            $error = lang('invalid_email');
        } else {
            // Check if email is already taken by another user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user['id']]);
            if ($stmt->fetch()) {
                $error = lang('email_taken');
            } else {
                // Update profile
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
                if ($stmt->execute([$name, $email, $phone, $user['id']])) {
                    $message = lang('profile_updated');
                    // Update session
                    $_SESSION['name'] = $name;
                    $_SESSION['email'] = $email;
                    // Refresh user data
                    $user = getCurrentUser();
                } else {
                    $error = lang('update_failed');
                }
            }
        }
    } elseif (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate input
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = lang('all_fields_required');
        } elseif ($new_password !== $confirm_password) {
            $error = lang('passwords_dont_match');
        } elseif (!password_verify($current_password, $user['password'])) {
            $error = lang('current_password_incorrect');
        } else {
            // Validate new password strength
            $password_errors = validatePassword($new_password);
            if (!empty($password_errors)) {
                $error = implode('<br>', $password_errors);
            } else {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                if ($stmt->execute([$hashed_password, $user['id']])) {
                    $message = lang('password_updated');
                } else {
                    $error = lang('update_failed');
                }
            }
        }
    }
}

$page_title = lang('profile');
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-4">
            <!-- Profile Card -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    <img src="<?php echo getAvatarUrl($user['avatar']); ?>" 
                         class="rounded-circle mb-3" 
                         alt="Profile Picture" 
                         style="width: 150px; height: 150px; object-fit: cover;">
                    <h5 class="card-title"><?php echo htmlspecialchars($user['name']); ?></h5>
                    <p class="text-muted"><?php echo getRoleName($user['role']); ?></p>
                    <a href="avatar.php" class="btn btn-outline-primary btn-sm">
                        <?php echo lang('change_avatar'); ?>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- Profile Information -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title"><?php echo lang('profile_information'); ?></h5>
                    <form method="post">
                        <div class="mb-3">
                            <label for="name" class="form-label"><?php echo lang('name'); ?></label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label"><?php echo lang('email'); ?></label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label"><?php echo lang('phone'); ?></label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <?php echo lang('update_profile'); ?>
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Change Password -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?php echo lang('change_password'); ?></h5>
                    <form method="post">
                        <div class="mb-3">
                            <label for="current_password" class="form-label"><?php echo lang('current_password'); ?></label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label"><?php echo lang('new_password'); ?></label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label"><?php echo lang('confirm_password'); ?></label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <button type="submit" name="change_password" class="btn btn-primary">
                            <?php echo lang('change_password'); ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 