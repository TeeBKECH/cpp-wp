<?php
/**
 * Generic single fallback template.
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
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
get_footer();
