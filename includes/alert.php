<?php
function showAlert($message, $type = 'success') {
    $icons = [
        'success' => 'fa-check-circle',
        'danger'  => 'fa-exclamation-circle',
        'warning' => 'fa-exclamation-triangle',
        'info'    => 'fa-info-circle'
    ];
    $icon = $icons[$type] ?? 'fa-info-circle';
    echo '<div class="sc-alert sc-alert-' . $type . '">';
    echo '<i class="fas ' . $icon . '"></i>';
    echo '<span>' . $message . '</span>';
    echo '<button class="sc-alert-close" onclick="this.parentElement.remove()">&times;</button>';
    echo '</div>';
}
