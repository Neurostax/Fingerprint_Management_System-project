<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title = lang('help');
require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <!-- Navigation -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title"><?php echo lang('help_topics'); ?></h5>
                    <div class="list-group">
                        <a href="#getting-started" class="list-group-item list-group-item-action">
                            <?php echo lang('getting_started'); ?>
                        </a>
                        <a href="#attendance" class="list-group-item list-group-item-action">
                            <?php echo lang('attendance'); ?>
                        </a>
                        <a href="#profile" class="list-group-item list-group-item-action">
                            <?php echo lang('profile'); ?>
                        </a>
                        <a href="#settings" class="list-group-item list-group-item-action">
                            <?php echo lang('settings'); ?>
                        </a>
                        <a href="#security" class="list-group-item list-group-item-action">
                            <?php echo lang('security'); ?>
                        </a>
                        <a href="#troubleshooting" class="list-group-item list-group-item-action">
                            <?php echo lang('troubleshooting'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <!-- Content -->
            <div class="card">
                <div class="card-body">
                    <!-- Getting Started -->
                    <section id="getting-started" class="mb-5">
                        <h2 class="mb-4"><?php echo lang('getting_started'); ?></h2>
                        <div class="accordion" id="gettingStartedAccordion">
                            <div class="accordion-item">
                                <h3 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" 
                                            data-bs-target="#gettingStarted1">
                                        <?php echo lang('how_to_login'); ?>
                                    </button>
                                </h3>
                                <div id="gettingStarted1" class="accordion-collapse collapse show" 
                                     data-bs-parent="#gettingStartedAccordion">
                                    <div class="accordion-body">
                                        <p><?php echo lang('login_instructions'); ?></p>
                                        <ol>
                                            <li><?php echo lang('login_step1'); ?></li>
                                            <li><?php echo lang('login_step2'); ?></li>
                                            <li><?php echo lang('login_step3'); ?></li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h3 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                            data-bs-target="#gettingStarted2">
                                        <?php echo lang('navigating_dashboard'); ?>
                                    </button>
                                </h3>
                                <div id="gettingStarted2" class="accordion-collapse collapse" 
                                     data-bs-parent="#gettingStartedAccordion">
                                    <div class="accordion-body">
                                        <p><?php echo lang('dashboard_instructions'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    
                    <!-- Attendance -->
                    <section id="attendance" class="mb-5">
                        <h2 class="mb-4"><?php echo lang('attendance'); ?></h2>
                        <div class="accordion" id="attendanceAccordion">
                            <div class="accordion-item">
                                <h3 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" 
                                            data-bs-target="#attendance1">
                                        <?php echo lang('marking_attendance'); ?>
                                    </button>
                                </h3>
                                <div id="attendance1" class="accordion-collapse collapse show" 
                                     data-bs-parent="#attendanceAccordion">
                                    <div class="accordion-body">
                                        <p><?php echo lang('attendance_instructions'); ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h3 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                            data-bs-target="#attendance2">
                                        <?php echo lang('viewing_attendance'); ?>
                                    </button>
                                </h3>
                                <div id="attendance2" class="accordion-collapse collapse" 
                                     data-bs-parent="#attendanceAccordion">
                                    <div class="accordion-body">
                                        <p><?php echo lang('view_attendance_instructions'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    
                    <!-- Profile -->
                    <section id="profile" class="mb-5">
                        <h2 class="mb-4"><?php echo lang('profile'); ?></h2>
                        <div class="accordion" id="profileAccordion">
                            <div class="accordion-item">
                                <h3 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" 
                                            data-bs-target="#profile1">
                                        <?php echo lang('updating_profile'); ?>
                                    </button>
                                </h3>
                                <div id="profile1" class="accordion-collapse collapse show" 
                                     data-bs-parent="#profileAccordion">
                                    <div class="accordion-body">
                                        <p><?php echo lang('update_profile_instructions'); ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h3 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                            data-bs-target="#profile2">
                                        <?php echo lang('changing_password'); ?>
                                    </button>
                                </h3>
                                <div id="profile2" class="accordion-collapse collapse" 
                                     data-bs-parent="#profileAccordion">
                                    <div class="accordion-body">
                                        <p><?php echo lang('change_password_instructions'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    
                    <!-- Settings -->
                    <section id="settings" class="mb-5">
                        <h2 class="mb-4"><?php echo lang('settings'); ?></h2>
                        <div class="accordion" id="settingsAccordion">
                            <div class="accordion-item">
                                <h3 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" 
                                            data-bs-target="#settings1">
                                        <?php echo lang('changing_language'); ?>
                                    </button>
                                </h3>
                                <div id="settings1" class="accordion-collapse collapse show" 
                                     data-bs-parent="#settingsAccordion">
                                    <div class="accordion-body">
                                        <p><?php echo lang('change_language_instructions'); ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h3 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                            data-bs-target="#settings2">
                                        <?php echo lang('changing_theme'); ?>
                                    </button>
                                </h3>
                                <div id="settings2" class="accordion-collapse collapse" 
                                     data-bs-parent="#settingsAccordion">
                                    <div class="accordion-body">
                                        <p><?php echo lang('change_theme_instructions'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    
                    <!-- Security -->
                    <section id="security" class="mb-5">
                        <h2 class="mb-4"><?php echo lang('security'); ?></h2>
                        <div class="accordion" id="securityAccordion">
                            <div class="accordion-item">
                                <h3 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" 
                                            data-bs-target="#security1">
                                        <?php echo lang('two_factor_auth'); ?>
                                    </button>
                                </h3>
                                <div id="security1" class="accordion-collapse collapse show" 
                                     data-bs-parent="#securityAccordion">
                                    <div class="accordion-body">
                                        <p><?php echo lang('2fa_instructions'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    
                    <!-- Troubleshooting -->
                    <section id="troubleshooting" class="mb-5">
                        <h2 class="mb-4"><?php echo lang('troubleshooting'); ?></h2>
                        <div class="accordion" id="troubleshootingAccordion">
                            <div class="accordion-item">
                                <h3 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" 
                                            data-bs-target="#troubleshooting1">
                                        <?php echo lang('forgot_password'); ?>
                                    </button>
                                </h3>
                                <div id="troubleshooting1" class="accordion-collapse collapse show" 
                                     data-bs-parent="#troubleshootingAccordion">
                                    <div class="accordion-body">
                                        <p><?php echo lang('forgot_password_instructions'); ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h3 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                            data-bs-target="#troubleshooting2">
                                        <?php echo lang('contact_support'); ?>
                                    </button>
                                </h3>
                                <div id="troubleshooting2" class="accordion-collapse collapse" 
                                     data-bs-parent="#troubleshootingAccordion">
                                    <div class="accordion-body">
                                        <p><?php echo lang('contact_support_instructions'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 