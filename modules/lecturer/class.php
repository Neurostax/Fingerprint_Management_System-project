<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

// Check if user is logged in and is a lecturer
if (!isLoggedIn() || $_SESSION['role'] !== 'lecturer') {
    redirect('login.php');
}

// Get class ID from URL
$class_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Verify lecturer owns this class
$stmt = $pdo->prepare("SELECT * FROM classes WHERE id = ? AND lecturer_id = ?");
$stmt->execute([$class_id, $_SESSION['user_id']]);
$class = $stmt->fetch();

if (!$class) {
    redirect('dashboard.php');
}

// Get class attendance
$stmt = $pdo->prepare("
    SELECT a.*, u.name as student_name, u.reg_number 
    FROM attendance a 
    JOIN users u ON a.student_id = u.id 
    WHERE a.class_id = ? 
    ORDER BY a.created_at DESC
");
$stmt->execute([$class_id]);
$attendance = $stmt->fetchAll();

// Get enrolled students
$stmt = $pdo->prepare("
    SELECT u.* 
    FROM users u 
    JOIN class_enrollments ce ON u.id = ce.student_id 
    WHERE ce.class_id = ? 
    ORDER BY u.name
");
$stmt->execute([$class_id]);
$students = $stmt->fetchAll();

$page_title = lang('class_details') . ' - ' . htmlspecialchars($class['name']);
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <h2><?php echo htmlspecialchars($class['name']); ?></h2>
            <p class="text-muted"><?php echo htmlspecialchars($class['schedule']); ?></p>
            
            <h3 class="mt-4"><?php echo lang('enrolled_students'); ?></h3>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th><?php echo lang('name'); ?></th>
                            <th><?php echo lang('registration_number'); ?></th>
                            <th><?php echo lang('attendance_count'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td><?php echo htmlspecialchars($student['reg_number']); ?></td>
                                <td>
                                    <?php
                                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE class_id = ? AND student_id = ?");
                                    $stmt->execute([$class_id, $student['id']]);
                                    echo $stmt->fetchColumn();
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="col-md-4">
            <h3><?php echo lang('recent_attendance'); ?></h3>
            <div class="list-group">
                <?php foreach ($attendance as $record): ?>
                    <div class="list-group-item">
                        <h6 class="mb-1"><?php echo htmlspecialchars($record['student_name']); ?></h6>
                        <small class="text-muted">
                            <?php echo date('Y-m-d H:i', strtotime($record['created_at'])); ?>
                        </small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 