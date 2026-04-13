<?php
/**
 * Fallback template (blog index, edge cases). Matches default page layout when showing a single post.
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (!have_posts()) {
    ?>
    <main class="main main--service">
        <section class="section section--page-intro">
            <div class="container">
                <div class="page-intro">
                    <?php get_template_part('template-parts/breadcrumbs'); ?>
                    <h1 class="page-intro_title"><?php esc_html_e('Контент пока не добавлен', 'cpp-courses-theme'); ?></h1>
                </div>
            </div>
        </section>
    </main>
    <?php
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
