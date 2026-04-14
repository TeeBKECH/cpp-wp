<?php
/**
 * Reusable CTA block (dark section + CF7 from site options). No admin fields.
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$cf7_post = cpp_courses_get_cta_dark_cf7_form_id();
$cf7_html = '';
if (!empty($cf7_post)) {
    $cf7_html = cpp_courses_render_cf7_form($cf7_post);
}
?>
<section class="section section--cta section--dark" id="cta">
    <div class="container">
        <div class="cta section_content section_content--alt">
            <div class="section_header section_header--alt">
                <h2 class="section_title"><?php esc_html_e('Написать нам', 'cpp-courses-theme'); ?></h2>
                <p class="section_subtitle">
                    <?php esc_html_e('По телефону или по электронной почте заполните пожалуйста форму обратной связи. Сотрудник центра свяжется с Вами в ближайшее время.', 'cpp-courses-theme'); ?>
                </p>
            </div>
            <?php if ($cf7_html !== '') : ?>
                <div class="cta_form">
                    <?php echo $cf7_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
