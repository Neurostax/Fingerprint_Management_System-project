<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

// Check if user is logged in and is a lecturer
if (!isLoggedIn() || $_SESSION['role'] !== 'lecturer') {
    redirect('login.php');
}

// Get lecturer's classes
$stmt = $pdo->prepare("
    SELECT c.*, COUNT(a.id) as attendance_count 
    FROM classes c 
    LEFT JOIN attendance a ON c.id = a.class_id 
    WHERE c.lecturer_id = ? 
    GROUP BY c.id
");
$stmt->execute([$_SESSION['user_id']]);
$classes = $stmt->fetchAll();

// Get today's attendance
$stmt = $pdo->prepare("
    SELECT a.*, u.name as student_name, c.name as class_name 
    FROM attendance a 
    JOIN users u ON a.student_id = u.id 
    JOIN classes c ON a.class_id = c.id 
    WHERE c.lecturer_id = ? AND DATE(a.created_at) = CURDATE()
    ORDER BY a.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$today_attendance = $stmt->fetchAll();

$page_title = lang('lecturer_dashboard');
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-4"><?php echo lang('dashboard'); ?></h1>
            <a href="../../admin/schedule.php" class="btn btn-info">
                <i class="fas fa-calendar-alt me-2"></i> Schedule Management
            </a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <h2><?php echo lang('my_classes'); ?></h2>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th><?php echo lang('class_name'); ?></th>
                            <th><?php echo lang('schedule'); ?></th>
                            <th><?php echo lang('attendance_count'); ?></th>
                            <th><?php echo lang('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classes as $class): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($class['name']); ?></td>
                                <td><?php echo htmlspecialchars($class['schedule']); ?></td>
                                <td><?php echo $class['attendance_count']; ?></td>
                                <td>
                                    <a href="class.php?id=<?php echo $class['id']; ?>" class="btn btn-sm btn-primary">
                                        <?php echo lang('view_details'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="col-md-4">
            <h2><?php echo lang('today_attendance'); ?></h2>
            <div class="list-group">
                <?php foreach ($today_attendance as $attendance): ?>
                    <div class="list-group-item">
                        <h6 class="mb-1"><?php echo htmlspecialchars($attendance['student_name']); ?></h6>
                        <small class="text-muted">
                            <?php echo htmlspecialchars($attendance['class_name']); ?> - 
                            <?php echo date('H:i', strtotime($attendance['created_at'])); ?>
                        </small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 