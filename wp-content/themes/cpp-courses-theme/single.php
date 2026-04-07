<?php
/**
 * Single post template.
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$slug = get_post_field('post_name', get_post());
$single_map = array(
    'article' => 'article.html',
);

if (isset($single_map[$slug])) {
    echo cpp_courses_render_static_page($single_map[$slug]); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
} else {
    ?>
    <main class="page">
        <section class="section">
            <div class="container">
                <?php if (have_posts()) : ?>
                    <?php while (have_posts()) : the_post(); ?>
                        <article <?php post_class(); ?>>
                            <h1><?php the_title(); ?></h1>
                            <div class="page-content"><?php the_content(); ?></div>
                        </article>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <?php
}

get_footer();
