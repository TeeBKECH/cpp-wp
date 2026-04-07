<?php
/**
 * Main fallback template.
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main class="page">
    <section class="section section--content">
        <div class="container">
            <?php if (have_posts()) : ?>
                <?php while (have_posts()) : the_post(); ?>
                    <article <?php post_class(); ?>>
                        <h1><?php the_title(); ?></h1>
                        <div class="entry-content">
                            <?php the_content(); ?>
                        </div>
                    </article>
                <?php endwhile; ?>
            <?php else : ?>
                <h1>Контент пока не добавлен</h1>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php
get_footer();
