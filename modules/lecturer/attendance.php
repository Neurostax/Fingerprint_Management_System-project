<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

// Check if user is logged in and is a lecturer
if (!isLoggedIn() || $_SESSION['role'] !== 'lecturer') {
    redirect('login.php');
}

// Get class ID from URL
$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;

// Verify lecturer owns this class
$stmt = $pdo->prepare("SELECT * FROM classes WHERE id = ? AND lecturer_id = ?");
$stmt->execute([$class_id, $_SESSION['user_id']]);
$class = $stmt->fetch();

if (!$class) {
    redirect('dashboard.php');
}

// Handle attendance marking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = isset($_POST['student_id']) ? (int)$_POST['student_id'] : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : 'present';
    
    if ($student_id > 0) {
        $stmt = $pdo->prepare("
            INSERT INTO attendance (class_id, student_id, status, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$class_id, $student_id, $status]);
        
        // Redirect to prevent form resubmission
        redirect("attendance.php?class_id=$class_id");
    }
}

// Get enrolled students
$stmt = $pdo->prepare("
    SELECT u.*, 
           (SELECT COUNT(*) FROM attendance a 
            WHERE a.student_id = u.id AND a.class_id = ?) as attendance_count
    FROM users u 
    JOIN class_enrollments ce ON u.id = ce.student_id 
    WHERE ce.class_id = ? 
    ORDER BY u.name
");
$stmt->execute([$class_id, $class_id]);
$students = $stmt->fetchAll();

// Get today's attendance
$stmt = $pdo->prepare("
    SELECT a.*, u.name as student_name 
    FROM attendance a 
    JOIN users u ON a.student_id = u.id 
    WHERE a.class_id = ? AND DATE(a.created_at) = CURDATE()
    ORDER BY a.created_at DESC
");
$stmt->execute([$class_id]);
$today_attendance = $stmt->fetchAll();

$page_title = lang('manage_attendance') . ' - ' . htmlspecialchars($class['name']);
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <h2><?php echo lang('manage_attendance'); ?></h2>
            <p class="text-muted"><?php echo htmlspecialchars($class['name']); ?></p>
            
            <form method="POST" class="mb-4">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th><?php echo lang('name'); ?></th>
                                <th><?php echo lang('registration_number'); ?></th>
                                <th><?php echo lang('attendance_count'); ?></th>
                                <th><?php echo lang('status'); ?></th>
                                <th><?php echo lang('action'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['reg_number']); ?></td>
                                    <td><?php echo $student['attendance_count']; ?></td>
                                    <td>
                                        <select name="status" class="form-control">
                                            <option value="present"><?php echo lang('present'); ?></option>
                                            <option value="absent"><?php echo lang('absent'); ?></option>
                                            <option value="late"><?php echo lang('late'); ?></option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <?php echo lang('mark_attendance'); ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
        
        <div class="col-md-4">
            <h3><?php echo lang('today_attendance'); ?></h3>
            <div class="list-group">
                <?php foreach ($today_attendance as $record): ?>
                    <div class="list-group-item">
                        <h6 class="mb-1"><?php echo htmlspecialchars($record['student_name']); ?></h6>
                        <small class="text-muted">
                            <?php echo date('H:i', strtotime($record['created_at'])); ?> - 
                            <?php echo lang($record['status']); ?>
                        </small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 