<?php
if (!isset($pageTitle)) {
    $pageTitle = SITE_NAME;
}
// Breadcrumbs logic
function render_breadcrumbs() {
    $map = [
        '/modules/admin/dashboard.php' => ['Dashboard'],
        '/modules/admin/users.php' => ['Dashboard' => '/modules/admin/dashboard.php', 'User Management'],
        '/modules/admin/schedule.php' => ['Dashboard' => '/modules/admin/dashboard.php', 'Schedule Management'],
        '/modules/lecturer/dashboard.php' => ['Dashboard'],
        '/modules/profile/profile.php' => ['Profile'],
        '/modules/settings/settings.php' => ['Settings'],
        '/modules/student/dashboard.php' => ['Dashboard'],
    ];
    $uri = $_SERVER['REQUEST_URI'];
    $crumbs = [];
    foreach ($map as $path => $trail) {
        if (strpos($uri, $path) !== false) {
            $crumbs = $trail;
            break;
        }
    }
    if ($crumbs) {
        echo '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
        foreach ($crumbs as $label => $link) {
            if (is_int($label)) {
                echo '<li class="breadcrumb-item active" aria-current="page">' . htmlspecialchars($link) . '</li>';
            } else {
                echo '<li class="breadcrumb-item"><a href="' . $link . '">' . htmlspecialchars($label) . '</a></li>';
            }
        }
        echo '</ol></nav>';
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo DEFAULT_LANGUAGE; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="/assets/css/style.css" rel="stylesheet">
    
    <!-- Theme CSS -->
    <link href="/assets/css/<?php echo $theme; ?>.css" rel="stylesheet">
    
    <!-- Dashboard Specific CSS -->
    <link href="/assets/css/dashboard.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Custom Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
</head>
<body>
    <?php require_once 'navbar.php'; ?>
    <?php render_breadcrumbs(); ?>
    <main class="container py-4">
        <?php if (isset($successMessage)): ?>
            <div class="alert alert-success fade-in">
                <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger fade-in">
                <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?> 