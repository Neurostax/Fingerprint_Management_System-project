<?php
$currentLang = $_SESSION['lang'] ?? DEFAULT_LANGUAGE;
$availableLangs = ['en' => 'English', 'fr' => 'Français'];
?>

<div class="dropdown">
    <button class="btn btn-link dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-globe"></i> <?php echo $availableLangs[$currentLang]; ?>
    </button>
    <ul class="dropdown-menu" aria-labelledby="languageDropdown">
        <?php foreach ($availableLangs as $code => $name): ?>
            <?php if ($code !== $currentLang): ?>
                <li>
                    <a class="dropdown-item" href="?lang=<?php echo $code; ?>">
                        <?php echo $name; ?>
                    </a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
</div>

<?php
// Handle language switch
if (isset($_GET['lang']) && in_array($_GET['lang'], array_keys($availableLangs))) {
    switchLanguage($_GET['lang']);
    // Redirect to remove lang parameter from URL
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}
?> 