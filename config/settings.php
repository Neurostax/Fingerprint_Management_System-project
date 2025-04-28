<?php
require_once __DIR__ . '/../includes/functions.php';

// Load all settings from database
$settings = getAllSettings();

// Set timezone
date_default_timezone_set($settings['timezone'] ?? 'UTC'); 