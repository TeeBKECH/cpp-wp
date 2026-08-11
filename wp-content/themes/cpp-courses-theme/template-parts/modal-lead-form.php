<?php
/**
 * Site modal: «Оставить заявку» + CF7 (theme modal system, not Fancybox).
 *
 * Open with data-modal="lead-form-modal".
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
<div class="modal modal-form" id="lead-form-modal" aria-hidden="true">
    <div class="modal_overlay" data-close=""></div>
    <div class="modal_content">
        <div class="modal_cls" data-close="" aria-label="<?php esc_attr_e('Закрыть', 'cpp-courses-theme'); ?>"></div>
        <h2 class="modal-title"><?php esc_html_e('Оставить заявку', 'cpp-courses-theme'); ?></h2>
        <p class="modal-subtitle"><?php esc_html_e('Мы перезвоним Вам в ближайшее время', 'cpp-courses-theme'); ?></p>
        <div class="cpp-lead-modal-form">
            <?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
    </div>
</div>
