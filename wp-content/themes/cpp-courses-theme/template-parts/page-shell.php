<?php
/**
 * Standard inner page layout: optional breadcrumbs, H1 + excerpt, Gutenberg content.
 *
 * Used by page.php and front-page.php. Expects the loop post (the_post() already called).
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$show_breadcrumbs = ! is_front_page();

$intro_raw = get_the_excerpt();
$intro_raw = is_string($intro_raw) ? trim($intro_raw) : '';

$main_class = 'main main--service';
if (is_front_page()) {
    $main_class .= ' main--index';
}
?>
<main class="<?php echo esc_attr($main_class); ?>">
    <section class="section section--page-intro">
        <div class="container">
            <div class="page-intro">
                <?php if ($show_breadcrumbs) : ?>
                    <?php get_template_part('template-parts/breadcrumbs'); ?>
                <?php endif; ?>
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
                <div class="page-content_main page-content_main--blocks">
                    <?php the_content(); ?>
                </div>
            </div>
        </div>
    </section>
</main>
