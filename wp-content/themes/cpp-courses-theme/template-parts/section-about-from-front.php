<?php
/**
 * About block: same fields as home page (static front page).
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

$about_title = function_exists('get_field') ? (string) get_field('main_about_title', $front_id) : __('О нас', 'cpp-courses-theme');
$about_html = function_exists('get_field') ? (string) get_field('main_about_content', $front_id) : '';
$about_img = '';
if (function_exists('get_field')) {
    $about_img = cpp_courses_image_field_url(get_field('main_about_image', $front_id));
}
$about_more = function_exists('get_field') ? get_field('main_about_more', $front_id) : null;

if ($about_html === '' && $about_img === '') {
    return;
}
?>
<section class="section section--about" id="about">
    <div class="container">
        <div class="about section_content">
            <div class="section_header">
                <h2 class="section_title"><?php echo esc_html($about_title !== '' ? $about_title : __('О нас', 'cpp-courses-theme')); ?></h2>
            </div>
            <div class="about_inner">
                <?php if ($about_html !== '') : ?>
                    <div class="about_text about_text--truncatable entry-content"><?php echo apply_filters('the_content', $about_html); ?></div>
                <?php endif; ?>
                <?php if ($about_img !== '') : ?>
                    <div class="about_img-wrap">
                        <img src="<?php echo esc_url($about_img); ?>" alt="<?php echo esc_attr($about_title); ?>" width="760" height="480" loading="lazy" />
                    </div>
                <?php endif; ?>
            </div>
            <?php
            $more_url = is_array($about_more) && !empty($about_more['url']) ? (string) $about_more['url'] : '';
            $more_text = is_array($about_more) && !empty($about_more['title']) ? (string) $about_more['title'] : __('Подробнее', 'cpp-courses-theme');
            $more_target = is_array($about_more) && !empty($about_more['target']) ? (string) $about_more['target'] : '_self';
            if ($more_url !== '') :
                ?>
                <div class="section_footer">
                    <a class="button button--filled button--md" href="<?php echo esc_url($more_url); ?>" target="<?php echo esc_attr($more_target); ?>">
                        <span class="button_text"><?php echo esc_html($more_text); ?></span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
