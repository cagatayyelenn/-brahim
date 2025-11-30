<?php
// helpers.php

function swal_redirect_and_exit(string $title, string $text, string $icon = 'error', string $redirect = 'index.php', int $ms = 3000){
    if (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: text/html; charset=utf-8');
    ?>
    
    <script>
        Swal.fire({
            icon: <?= json_encode($icon) ?>,
            title: <?= json_encode($title) ?>,
            text: <?= json_encode($text) ?>,
            timer: <?= (int)$ms ?>,
            showConfirmButton: false,
            willClose: () => {
                window.location.href = <?= json_encode($redirect) ?>;
            }
        });
    </script>
    <?php
    exit;
}