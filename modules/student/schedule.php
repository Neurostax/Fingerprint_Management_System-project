<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

// Check if user is logged in and is a student
if (!isLoggedIn() || $_SESSION['role'] !== 'student') {
    redirect('login.php');
}

// Get student's enrolled classes
$stmt = $pdo->prepare("
    SELECT c.*, l.name as lecturer_name 
    FROM classes c 
    JOIN class_enrollments ce ON c.id = ce.class_id 
    JOIN users l ON c.lecturer_id = l.id 
    WHERE ce.student_id = ? 
    ORDER BY c.schedule
");
$stmt->execute([$_SESSION['user_id']]);
$classes = $stmt->fetchAll();

$page_title = lang('my_schedule');
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mt-4">
    <h2><?php echo lang('my_schedule'); ?></h2>
    
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?php echo lang('class_name'); ?></th>
                    <th><?php echo lang('lecturer'); ?></th>
                    <th><?php echo lang('schedule'); ?></th>
                    <th><?php echo lang('attendance'); ?></th>
                    <th><?php echo lang('actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($classes as $class): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($class['name']); ?></td>
                        <td><?php echo htmlspecialchars($class['lecturer_name']); ?></td>
                        <td><?php echo htmlspecialchars($class['schedule']); ?></td>
                        <td>
                            <?php
                            $stmt = $pdo->prepare("
                                SELECT COUNT(*) as total, 
                                       SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present
                                FROM attendance 
                                WHERE class_id = ? AND student_id = ?
                            ");
                            $stmt->execute([$class['id'], $_SESSION['user_id']]);
                            $attendance = $stmt->fetch();
                            echo $attendance['present'] . '/' . $attendance['total'];
                            ?>
                        </td>
                        <td>
                            <a href="attendance.php?class_id=<?php echo $class['id']; ?>" class="btn btn-primary btn-sm">
                                <?php echo lang('mark_attendance'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 