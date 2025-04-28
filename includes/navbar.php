<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="/">Smart Attendance System</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if (isLoggedIn()): ?>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link<?php if ($current_page == 'dashboard.php') echo ' active'; ?>" href="/modules/admin/dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?php if ($current_page == 'users.php') echo ' active'; ?>" href="/modules/admin/users.php">Users</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?php if ($current_page == 'schedule.php') echo ' active'; ?>" href="/modules/admin/schedule.php">Schedule</a>
                        </li>
                    <?php elseif ($_SESSION['role'] === 'lecturer'): ?>
                        <li class="nav-item">
                            <a class="nav-link<?php if ($current_page == 'dashboard.php') echo ' active'; ?>" href="/modules/lecturer/dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?php if ($current_page == 'classes.php') echo ' active'; ?>" href="/modules/lecturer/classes.php">My Classes</a>
                        </li>
                    <?php elseif ($_SESSION['role'] === 'student'): ?>
                        <li class="nav-item">
                            <a class="nav-link<?php if ($current_page == 'dashboard.php') echo ' active'; ?>" href="/modules/student/dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?php if ($current_page == 'schedule.php') echo ' active'; ?>" href="/modules/student/schedule.php">My Schedule</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?php if ($current_page == 'attendance.php') echo ' active'; ?>" href="/modules/student/attendance.php">My Attendance</a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
            
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo htmlspecialchars($_SESSION['name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="/modules/profile.php">Profile</a></li>
                            <li><a class="dropdown-item" href="/modules/settings.php">Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link<?php if ($current_page == 'login.php') echo ' active'; ?>" href="/login.php">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav> 