<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Require admin role
requireRole('admin');

// Get statistics
try {
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $totalUsers = $stmt->fetchColumn();
    
    // Total lecturers
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'lecturer'");
    $totalLecturers = $stmt->fetchColumn();
    
    // Total students
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
    $totalStudents = $stmt->fetchColumn();
    
    // Total classes
    $stmt = $pdo->query("SELECT COUNT(*) FROM classes");
    $totalClasses = $stmt->fetchColumn();
    
    // Recent activities
    $stmt = $pdo->query("
        SELECT l.*, u.name as user_name 
        FROM logs l 
        JOIN users u ON l.user_id = u.id 
        ORDER BY l.timestamp DESC 
        LIMIT 5
    ");
    $recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // System status
    $stmt = $pdo->query("SELECT COUNT(*) FROM authorization_sessions WHERE status = 'active'");
    $activeSessions = $stmt->fetchColumn();
    
} catch (PDOException $e) {
    error_log("Dashboard statistics error: " . $e->getMessage());
    $errorMessage = lang('error_occurred');
}

$pageTitle = lang('dashboard');
require_once '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-4"><?php echo lang('dashboard'); ?></h1>
            <div>
                <a href="users.php" class="btn btn-primary me-2">
                    <i class="fas fa-users me-2"></i> User Management
                </a>
                <a href="schedule.php" class="btn btn-info">
                    <i class="fas fa-calendar-alt me-2"></i> Schedule Management
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                <?php echo lang('total_users'); ?>
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalUsers; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                <?php echo lang('total_lecturers'); ?>
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalLecturers; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                <?php echo lang('total_students'); ?>
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalStudents; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                <?php echo lang('total_classes'); ?>
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalClasses; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Status and Recent Activities -->
    <div class="row">
        <!-- System Status -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><?php echo lang('system_status'); ?></h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="font-weight-bold"><?php echo lang('active_sessions'); ?>:</span>
                        <span class="badge bg-<?php echo $activeSessions > 0 ? 'success' : 'secondary'; ?>">
                            <?php echo $activeSessions; ?>
                        </span>
                    </div>
                    <div class="mb-3">
                        <span class="font-weight-bold"><?php echo lang('system_version'); ?>:</span>
                        <span>1.0.0</span>
                    </div>
                    <div class="mb-3">
                        <span class="font-weight-bold"><?php echo lang('last_backup'); ?>:</span>
                        <span><?php echo date('Y-m-d H:i:s'); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><?php echo lang('recent_activities'); ?></h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th><?php echo lang('user'); ?></th>
                                    <th><?php echo lang('activity'); ?></th>
                                    <th><?php echo lang('timestamp'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentActivities as $activity): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($activity['user_name']); ?></td>
                                        <td><?php echo htmlspecialchars($activity['activity']); ?></td>
                                        <td><?php echo date('Y-m-d H:i:s', strtotime($activity['timestamp'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?> 