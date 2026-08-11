<?php
/**
 * Education block with photo (same as home main_edu_seo_* fields).
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$front_id = cpp_services_sections_front_page_id();
if ($front_id < 1) {
    return;
}

$edu_title = function_exists('get_field') ? (string) get_field('main_edu_seo_title', $front_id) : '';
$edu_sub = function_exists('get_field') ? (string) get_field('main_edu_seo_subtitle', $front_id) : '';
$edu_img = '';
if (function_exists('get_field')) {
    $edu_img = cpp_courses_image_field_url(get_field('main_edu_seo_image', $front_id));
}
$edu_content = function_exists('get_field') ? (string) get_field('main_edu_seo_content', $front_id) : '';

if ($edu_content === '' && $edu_img === '') {
    return;
}
?>
<section class="section section--education" id="education">
    <div class="container">
        <div class="education section_content">
            <?php if ($edu_title !== '' || $edu_sub !== '') : ?>
                <div class="section_header">
                    <?php if ($edu_title !== '') : ?>
                        <h2 class="section_title"><?php echo esc_html($edu_title); ?></h2>
                    <?php endif; ?>
                    <?php if ($edu_sub !== '') : ?>
                        <p class="section_subtitle"><?php echo esc_html($edu_sub); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <div class="education_inner">
                <?php if ($edu_img !== '') : ?>
                    <div class="education_img-wrap">
                        <img src="<?php echo esc_url($edu_img); ?>" alt="" width="360" height="320" loading="lazy" />
                    </div>
                <?php endif; ?>
                <?php if ($edu_content !== '') : ?>
                    <div class="education_text entry-content"><?php echo apply_filters('the_content', $edu_content); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
