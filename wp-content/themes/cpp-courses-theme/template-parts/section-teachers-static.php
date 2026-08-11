<?php
/**
 * Teachers grid: latest teachers CPT, fixed section title/subtitle.
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$q = new WP_Query(
    array(
        'post_type' => 'teachers',
        'post_status' => 'publish',
        'posts_per_page' => 12,
        'orderby' => 'date',
        'order' => 'DESC',
        'no_found_rows' => true,
    )
);
if (!$q->have_posts()) {
    return;
}
?>
<section class="section section--teachers" id="teachers">
    <div class="container container--teachers">
        <div class="teachers section_content">
            <div class="section_header">
                <h2 class="section_title"><?php esc_html_e('Преподавательский состав', 'cpp-courses-theme'); ?></h2>
                <p class="section_subtitle"><?php esc_html_e('Сотрудники, которые Вас будут обучать', 'cpp-courses-theme'); ?></p>
            </div>
            <div class="teachers_grid teachers_grid--teachers">
                <?php
                while ($q->have_posts()) {
                    $q->the_post();
                    get_template_part('template-parts/card', 'teacher', array('post' => get_post()));
                }
                wp_reset_postdata();
                ?>
            </div>
        </div>
    </div>
</section>
