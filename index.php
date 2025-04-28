<?php
require_once 'config/config.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (checkAuth()) {
    // Redirect to appropriate dashboard based on role
    switch ($_SESSION['user_role']) {
        case 'admin':
            header('Location: modules/admin/dashboard.php');
            break;
        case 'lecturer':
            header('Location: modules/lecturer/dashboard.php');
            break;
        case 'student':
            header('Location: modules/student/dashboard.php');
            break;
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo lang('site_name'); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="<?php echo $theme; ?>-theme">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-fingerprint me-2"></i>
                <?php echo lang('site_name'); ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="login.php"><?php echo lang('login'); ?></a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container my-5">
        <div class="row">
            <div class="col-md-6">
                <h1 class="display-4 mb-4"><?php echo lang('welcome'); ?></h1>
                <p class="lead mb-4">
                    A modern, secure, and efficient attendance management system for educational institutions.
                </p>
                <div class="features mb-4">
                    <h5>Key Features:</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check-circle text-success me-2"></i> Real-time attendance tracking</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i> Role-based access control</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i> Multi-language support</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i> Dark/Light mode themes</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i> Comprehensive reporting</li>
                    </ul>
                </div>
                <a href="login.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    <?php echo lang('login'); ?>
                </a>
            </div>
            <div class="col-md-6">
                <img src="assets/images/attendance-system.svg" alt="Attendance System" class="img-fluid">
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12">
                <h2 class="text-center mb-4">System Roles</h2>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-user-shield fa-3x text-primary mb-3"></i>
                        <h3 class="card-title"><?php echo lang('role_admin'); ?></h3>
                        <p class="card-text">Manage users, system settings, and view comprehensive reports.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-chalkboard-teacher fa-3x text-primary mb-3"></i>
                        <h3 class="card-title"><?php echo lang('role_lecturer'); ?></h3>
                        <p class="card-text">Manage classes, authorize attendance, and track student participation.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-user-graduate fa-3x text-primary mb-3"></i>
                        <h3 class="card-title"><?php echo lang('role_student'); ?></h3>
                        <p class="card-text">View schedule, mark attendance, and track personal attendance records.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?php echo lang('site_name'); ?></h5>
                    <p>A smart solution for modern educational institutions.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; <?php echo date('Y'); ?> All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
</body>
</html> 