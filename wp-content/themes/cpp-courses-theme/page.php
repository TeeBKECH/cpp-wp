<?php
/**
 * Default page template: static HTML map or service-style layout (intro + content).
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$page_id = get_queried_object_id();
$mapped_template = cpp_courses_get_static_template_for_post($page_id);

if (!empty($mapped_template)) {
    echo cpp_courses_render_static_page($mapped_template);
    get_footer();
    return;
}

if (!have_posts()) {
    get_footer();
    return;
}

while (have_posts()) :
    the_post();

    $intro_raw = get_the_excerpt();
    $intro_raw = is_string($intro_raw) ? trim($intro_raw) : '';
    ?>
    <main class="main main--service">
        <section class="section section--page-intro">
            <div class="container">
                <div class="page-intro">
                    <?php get_template_part('template-parts/breadcrumbs'); ?>
                    <h1 class="page-intro_title"><?php the_title(); ?></h1>
                    <?php if ($intro_raw !== '') : ?>
                        <p class="page-intro_desc"><?php echo nl2br(esc_html($intro_raw)); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="section section--page-content">
            <div class="container">
                <div class="page-content">
                    <div class="page-content_main">
                        <?php the_content(); ?>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <?php
endwhile;

get_footer();
