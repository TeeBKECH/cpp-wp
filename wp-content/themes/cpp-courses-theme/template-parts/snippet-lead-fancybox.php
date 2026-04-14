<?php
/**
 * Hidden CF7 «Modal» form for Fancybox (single #cpp-lead-fancy-inline sitewide).
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$form_id = function_exists('cpp_courses_get_modal_cf7_form_id') ? cpp_courses_get_modal_cf7_form_id() : 0;
if ($form_id < 1) {
    return;
}

$html = cpp_courses_render_cf7_form($form_id, 'dark');
if ($html === '') {
    return;
}
?>
<div id="cpp-lead-fancy-inline" class="cpp-lead-fancybox-inline" style="display:none;width:100%;max-width:560px;">
    <div class="cpp-lead-fancybox-head">
        <h2 class="cpp-lead-fancybox-title"><?php esc_html_e('Оставить заявку', 'cpp-courses-theme'); ?></h2>
        <p class="cpp-lead-fancybox-subtitle"><?php esc_html_e('Мы перезвоним Вам в ближайшее время', 'cpp-courses-theme'); ?></p>
    </div>
    <div class="cpp-lead-fancybox-form">
        <?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </div>
</div>
