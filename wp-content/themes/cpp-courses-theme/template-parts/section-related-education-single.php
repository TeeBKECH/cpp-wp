<?php
/**
 * Related education materials swiper (single education).
 *
 * @package CppCoursesTheme
 *
 * @var array $args { @type int $exclude Post ID to exclude. }
 */

if (!defined('ABSPATH')) {
    exit;
}

$exclude = isset($args['exclude']) ? (int) $args['exclude'] : 0;

$rel_title = function_exists('get_field') ? (string) get_field('cpp_edu_related_title') : __('Другие материалы', 'cpp-courses-theme');
if ($rel_title === '') {
    $rel_title = __('Другие материалы', 'cpp-courses-theme');
}
$rel_subtitle = function_exists('get_field') ? (string) get_field('cpp_edu_related_subtitle') : '';
$rel_count = function_exists('get_field') ? (int) get_field('cpp_edu_related_count') : 6;
if ($rel_count < 1) {
    $rel_count = 6;
}
if ($rel_count > 12) {
    $rel_count = 12;
}

$related = new WP_Query(
    array(
        'post_type' => 'education',
        'post_status' => 'publish',
        'posts_per_page' => $rel_count,
        'post__not_in' => $exclude > 0 ? array($exclude) : array(),
        'orderby' => 'date',
        'order' => 'DESC',
        'no_found_rows' => true,
    )
);
if (!$related->have_posts()) {
    return;
}

$archive = get_post_type_archive_link('education');
if (!$archive) {
    $archive = home_url('/education/');
}
?>
<section class="section section--services" id="other-education">
    <div class="container container--other-services">
        <div class="services section_content">
            <div class="section_header section_header--other-services">
                <h2 class="section_title"><?php echo esc_html($rel_title); ?></h2>
                <?php if ($rel_subtitle !== '') : ?>
                    <p class="section_subtitle"><?php echo esc_html($rel_subtitle); ?></p>
                <?php endif; ?>
            </div>
            <div class="services_grid services_grid--swiper">
                <?php
                while ($related->have_posts()) {
                    $related->the_post();
                    get_template_part('template-parts/post-card', 'education');
                }
                wp_reset_postdata();
                ?>
            </div>
            <div class="section_footer section_footer--other-services">
                <a class="button button--filled button--md" href="<?php echo esc_url($archive); ?>">
                    <span class="button_text"><?php esc_html_e('Все материалы', 'cpp-courses-theme'); ?></span>
                </a>
                <nav class="swiper_navigation swiper_navigation--services" aria-label="<?php esc_attr_e('Карусель материалов', 'cpp-courses-theme'); ?>">
                    <button class="swiper_navigation-btn swiper_navigation-btn--prev" type="button" aria-label="<?php esc_attr_e('Назад', 'cpp-courses-theme'); ?>"></button>
                    <div class="swiper_navigation-pages"></div>
                    <button class="swiper_navigation-btn swiper_navigation-btn--next" type="button" aria-label="<?php esc_attr_e('Вперёд', 'cpp-courses-theme'); ?>"></button>
                </nav>
            </div>
        </div>
    </div>
</section>
