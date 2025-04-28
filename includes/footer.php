    </main>

    <footer class="footer mt-auto py-3 bg-light">
        <div class="container text-center">
            <span class="text-muted">
                &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. 
                <?php echo lang('all_rights_reserved'); ?>
            </span>
        </div>
    </footer>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom JS -->
    <script src="/assets/js/main.js"></script>
    
    <!-- Dashboard Specific JS -->
    <script src="/assets/js/dashboard.js"></script>
    
    <!-- Theme Switcher -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Theme switcher
            const themeSwitcher = document.getElementById('themeSwitcher');
            if (themeSwitcher) {
                themeSwitcher.addEventListener('click', function() {
                    const currentTheme = document.body.classList.contains('dark-theme') ? 'dark' : 'light';
                    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                    
                    // Update theme in database if user is logged in
                    if (<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
                        fetch('ajax/update_theme.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'theme=' + newTheme
                        });
                    }
                    
                    // Update theme in session
                    document.body.classList.remove(currentTheme + '-theme');
                    document.body.classList.add(newTheme + '-theme');
                });
            }
        });
    </script>
</body>
</html> 