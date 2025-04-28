<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['avatar'];
        
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowed_types)) {
            $error = lang('invalid_file_type');
        } else {
            // Validate file size (max 2MB)
            if ($file['size'] > 2 * 1024 * 1024) {
                $error = lang('file_too_large');
            } else {
                // Generate unique filename
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $extension;
                $upload_path = AVATAR_UPLOAD_PATH . '/' . $filename;
                
                // Create upload directory if it doesn't exist
                if (!file_exists(AVATAR_UPLOAD_PATH)) {
                    mkdir(AVATAR_UPLOAD_PATH, 0777, true);
                }
                
                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    // Process image
                    try {
                        $image = new Imagick($upload_path);
                        
                        // Resize image
                        $image->resizeImage(200, 200, Imagick::FILTER_LANCZOS, 1);
                        
                        // Convert to JPEG if not already
                        if ($extension !== 'jpg' && $extension !== 'jpeg') {
                            $image->setImageFormat('jpeg');
                            $filename = pathinfo($filename, PATHINFO_FILENAME) . '.jpg';
                            $upload_path = AVATAR_UPLOAD_PATH . '/' . $filename;
                        }
                        
                        // Save processed image
                        $image->writeImage($upload_path);
                        $image->clear();
                        
                        // Update user's avatar in database
                        $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                        $stmt->execute([$filename, $_SESSION['user_id']]);
                        
                        // Delete old avatar if exists
                        if (!empty($_SESSION['avatar'])) {
                            $old_avatar = AVATAR_UPLOAD_PATH . '/' . $_SESSION['avatar'];
                            if (file_exists($old_avatar)) {
                                unlink($old_avatar);
                            }
                        }
                        
                        // Update session
                        $_SESSION['avatar'] = $filename;
                        
                        $success = lang('avatar_updated');
                    } catch (Exception $e) {
                        $error = lang('image_processing_failed');
                        // Delete uploaded file if processing failed
                        if (file_exists($upload_path)) {
                            unlink($upload_path);
                        }
                    }
                } else {
                    $error = lang('upload_failed');
                }
            }
        }
    } else {
        $error = lang('no_file_uploaded');
    }
}

$page_title = lang('update_avatar');
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-center"><?php echo lang('update_avatar'); ?></h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <div class="text-center mb-4">
                        <?php if (!empty($_SESSION['avatar'])): ?>
                            <img src="<?php echo AVATAR_URL . '/' . $_SESSION['avatar']; ?>" 
                                 alt="Avatar" class="rounded-circle" style="width: 200px; height: 200px;">
                        <?php else: ?>
                            <img src="<?php echo AVATAR_URL . '/default.jpg'; ?>" 
                                 alt="Default Avatar" class="rounded-circle" style="width: 200px; height: 200px;">
                        <?php endif; ?>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="avatar"><?php echo lang('select_avatar'); ?></label>
                            <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*" required>
                            <small class="form-text text-muted">
                                <?php echo lang('avatar_requirements'); ?>
                            </small>
                        </div>
                        
                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <?php echo lang('upload_avatar'); ?>
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="profile.php" class="btn btn-secondary">
                            <?php echo lang('back_to_profile'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 