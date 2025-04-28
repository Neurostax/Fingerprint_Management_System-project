<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title = lang('page_not_found');
require_once 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <h1 class="display-1 text-primary">404</h1>
            <h2 class="mb-4"><?php echo lang('page_not_found'); ?></h2>
            <p class="lead mb-4">
                <?php echo lang('page_not_found_message'); ?>
            </p>
            <a href="<?php echo BASE_URL; ?>" class="btn btn-primary">
                <i class="fas fa-home me-2"></i>
                <?php echo lang('go_home'); ?>
            </a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 