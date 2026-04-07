<?php
/**
 * Generic page template for static mapped pages.
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$slug = get_post_field('post_name', get_post());
$static_map = array(
    'services' => 'services.html',
    'service' => 'service.html',
    'course' => 'course.html',
    'articles' => 'articles.html',
    'article' => 'article.html',
    'contacts' => 'contacts.html',
    'edu-info' => 'edu-info.html',
    'test-intro' => 'test-intro.html',
    'test-quiz' => 'test-quiz.html',
    'links' => 'links.html',
);

if (isset($static_map[$slug])) {
    echo cpp_courses_render_static_page($static_map[$slug]);
} else {
    ?>
    <main class="page">
        <section class="section">
            <div class="container">
                <h1><?php the_title(); ?></h1>
                <div class="page-content"><?php the_content(); ?></div>
            </div>
        </section>
    </main>
    <?php
}

get_footer();
