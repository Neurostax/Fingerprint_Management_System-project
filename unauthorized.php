<?php
require_once 'config/config.php';
require_once 'includes/auth.php';

$pageTitle = lang('unauthorized');
require_once 'includes/header.php';
?>

<div class="text-center py-5">
    <i class="fas fa-lock fa-5x text-danger mb-4"></i>
    <h1 class="display-4 mb-4"><?php echo lang('unauthorized'); ?></h1>
    <p class="lead mb-4"><?php echo lang('unauthorized_message'); ?></p>
    <a href="<?php echo SITE_URL; ?>" class="btn btn-primary">
        <i class="fas fa-home me-2"></i>
        <?php echo lang('back_to_home'); ?>
    </a>
</div>

<?php require_once 'includes/footer.php'; ?> 