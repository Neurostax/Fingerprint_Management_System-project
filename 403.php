<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title = lang('unauthorized');
require_once 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <h1 class="display-1 text-warning">403</h1>
            <h2 class="mb-4"><?php echo lang('unauthorized'); ?></h2>
            <p class="lead mb-4">
                <?php echo lang('unauthorized_message'); ?>
            </p>
            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                <a href="<?php echo BASE_URL; ?>" class="btn btn-primary">
                    <i class="fas fa-home me-2"></i>
                    <?php echo lang('go_home'); ?>
                </a>
                <a href="<?php echo BASE_URL; ?>/login.php" class="btn btn-outline-primary">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    <?php echo lang('login'); ?>
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 