<?php
$theme = $_COOKIE['theme'] ?? 'light';
?>
<button id="themeToggle" class="theme-toggle" title="Toggle Theme">
    <?= $theme === 'dark' ? '☀️' : '🌙' ?>
</button>

<script src="assets/js/jquery.min.js"></script>
<script>
$(document).ready(function() {
    $('#themeToggle').on('click', function() {
        let currentTheme = $('html').hasClass('dark-theme') ? 'dark' : 'light';
        let newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        if (newTheme === 'dark') {
            $('html').addClass('dark-theme');
            $(this).text('☀️');
        } else {
            $('html').removeClass('dark-theme');
            $(this).text('🌙');
        }
        document.cookie = "theme=" + newTheme + ";path=/;max-age=" + (30 * 24 * 60 * 60);
    });
});
</script>
