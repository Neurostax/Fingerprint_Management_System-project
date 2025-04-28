<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

// Check if user is logged in and is a student
if (!isLoggedIn() || $_SESSION['role'] !== 'student') {
    redirect('login.php');
}

// Get class ID from URL
$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;

// Verify student is enrolled in this class
$stmt = $pdo->prepare("
    SELECT c.*, l.name as lecturer_name 
    FROM classes c 
    JOIN class_enrollments ce ON c.id = ce.class_id 
    JOIN users l ON c.lecturer_id = l.id 
    WHERE c.id = ? AND ce.student_id = ?
");
$stmt->execute([$class_id, $_SESSION['user_id']]);
$class = $stmt->fetch();

if (!$class) {
    redirect('schedule.php');
}

// Handle attendance marking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $qr_code = isset($_POST['qr_code']) ? $_POST['qr_code'] : '';
    
    if ($qr_code) {
        // Verify QR code matches class
        if ($qr_code === md5($class_id . date('Y-m-d'))) {
            // Check if already marked attendance today
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM attendance 
                WHERE class_id = ? AND student_id = ? AND DATE(created_at) = CURDATE()
            ");
            $stmt->execute([$class_id, $_SESSION['user_id']]);
            
            if ($stmt->fetchColumn() == 0) {
                // Mark attendance
                $stmt = $pdo->prepare("
                    INSERT INTO attendance (class_id, student_id, status, created_at) 
                    VALUES (?, ?, 'present', NOW())
                ");
                $stmt->execute([$class_id, $_SESSION['user_id']]);
                
                // Send email notification
                $student = getUserById($_SESSION['user_id']);
                $message = "Attendance marked for {$class['name']} on " . date('Y-m-d H:i');
                sendEmail($student['email'], 'Attendance Marked', $message);
                
                $success = lang('attendance_marked_success');
            } else {
                $error = lang('attendance_already_marked');
            }
        } else {
            $error = lang('invalid_qr_code');
        }
    }
}

$page_title = lang('mark_attendance') . ' - ' . htmlspecialchars($class['name']);
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-6">
            <h2><?php echo lang('mark_attendance'); ?></h2>
            <p class="text-muted">
                <?php echo htmlspecialchars($class['name']); ?> - 
                <?php echo htmlspecialchars($class['lecturer_name']); ?>
            </p>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" class="mb-4">
                <div class="form-group">
                    <label for="qr_code"><?php echo lang('scan_qr_code'); ?></label>
                    <input type="text" class="form-control" id="qr_code" name="qr_code" required>
                </div>
                <button type="submit" class="btn btn-primary">
                    <?php echo lang('mark_attendance'); ?>
                </button>
            </form>
        </div>
        
        <div class="col-md-6">
            <h3><?php echo lang('attendance_history'); ?></h3>
            <div class="list-group">
                <?php
                $stmt = $pdo->prepare("
                    SELECT * FROM attendance 
                    WHERE class_id = ? AND student_id = ? 
                    ORDER BY created_at DESC 
                    LIMIT 5
                ");
                $stmt->execute([$class_id, $_SESSION['user_id']]);
                $attendance = $stmt->fetchAll();
                
                foreach ($attendance as $record):
                ?>
                    <div class="list-group-item">
                        <h6 class="mb-1"><?php echo date('Y-m-d H:i', strtotime($record['created_at'])); ?></h6>
                        <small class="text-muted"><?php echo lang($record['status']); ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Add QR code scanner functionality
document.addEventListener('DOMContentLoaded', function() {
    const qrInput = document.getElementById('qr_code');
    
    // Check if browser supports camera access
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        const scanner = new Html5QrcodeScanner('qr-reader', { fps: 10, qrbox: 250 });
        
        scanner.render((qrCode) => {
            qrInput.value = qrCode;
            document.querySelector('form').submit();
        });
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 