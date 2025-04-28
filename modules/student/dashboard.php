<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

// Check if user is logged in and is a student
if (!isLoggedIn() || $_SESSION['role'] !== 'student') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// Get student's classes and attendance data
$student_id = $_SESSION['user_id'];
$current_date = date('Y-m-d');

// Get upcoming classes
$stmt = $pdo->prepare("
    SELECT c.*, l.name as lecturer_name 
    FROM classes c 
    JOIN users l ON c.lecturer_id = l.id 
    JOIN attendance a ON c.id = a.class_id 
    WHERE a.student_id = ? AND c.date >= ? 
    ORDER BY c.date ASC, c.start_time ASC 
    LIMIT 5
");
$stmt->execute([$student_id, $current_date]);
$upcoming_classes = $stmt->fetchAll();

// Get attendance statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_classes,
        SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
        SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
        SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_count
    FROM attendance a
    JOIN classes c ON a.class_id = c.id
    WHERE a.student_id = ? AND c.date <= ?
");
$stmt->execute([$student_id, $current_date]);
$attendance_stats = $stmt->fetch();

// Calculate attendance percentage
$attendance_percentage = $attendance_stats['total_classes'] > 0 
    ? round(($attendance_stats['present_count'] / $attendance_stats['total_classes']) * 100, 2)
    : 0;

// Get recent attendance records
$stmt = $pdo->prepare("
    SELECT a.*, c.name as class_name, c.date, c.start_time, l.name as lecturer_name 
    FROM attendance a 
    JOIN classes c ON a.class_id = c.id 
    JOIN users l ON c.lecturer_id = l.id 
    WHERE a.student_id = ? 
    ORDER BY c.date DESC, c.start_time DESC 
    LIMIT 5
");
$stmt->execute([$student_id]);
$recent_attendance = $stmt->fetchAll();

$page_title = lang('student_dashboard');
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <!-- Attendance Statistics -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title"><?php echo lang('attendance_statistics'); ?></h5>
                    <div class="text-center mb-3">
                        <div class="display-4"><?php echo $attendance_percentage; ?>%</div>
                        <div class="text-muted"><?php echo lang('attendance_rate'); ?></div>
                    </div>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="text-success">
                                <div class="h4"><?php echo $attendance_stats['present_count']; ?></div>
                                <small><?php echo lang('present'); ?></small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-danger">
                                <div class="h4"><?php echo $attendance_stats['absent_count']; ?></div>
                                <small><?php echo lang('absent'); ?></small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-warning">
                                <div class="h4"><?php echo $attendance_stats['late_count']; ?></div>
                                <small><?php echo lang('late'); ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Classes -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title"><?php echo lang('upcoming_classes'); ?></h5>
                    <?php if (empty($upcoming_classes)): ?>
                        <p class="text-muted"><?php echo lang('no_upcoming_classes'); ?></p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($upcoming_classes as $class): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($class['name']); ?></h6>
                                        <small><?php echo formatDate($class['date']); ?></small>
                                    </div>
                                    <p class="mb-1">
                                        <small class="text-muted">
                                            <?php echo formatTime($class['start_time']); ?> - 
                                            <?php echo formatTime($class['end_time']); ?>
                                        </small>
                                    </p>
                                    <small class="text-muted">
                                        <?php echo lang('lecturer'); ?>: <?php echo htmlspecialchars($class['lecturer_name']); ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Attendance -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><?php echo lang('recent_attendance'); ?></h5>
            <?php if (empty($recent_attendance)): ?>
                <p class="text-muted"><?php echo lang('no_attendance_records'); ?></p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><?php echo lang('date'); ?></th>
                                <th><?php echo lang('class'); ?></th>
                                <th><?php echo lang('lecturer'); ?></th>
                                <th><?php echo lang('status'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_attendance as $record): ?>
                                <tr>
                                    <td><?php echo formatDate($record['date']); ?></td>
                                    <td><?php echo htmlspecialchars($record['class_name']); ?></td>
                                    <td><?php echo htmlspecialchars($record['lecturer_name']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $record['status'] === 'present' ? 'success' : 
                                                ($record['status'] === 'late' ? 'warning' : 'danger'); 
                                        ?>">
                                            <?php echo lang($record['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 