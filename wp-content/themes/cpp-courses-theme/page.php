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

$page_id = get_queried_object_id();
$mapped_template = cpp_courses_get_static_template_for_post($page_id);

if (!empty($mapped_template)) {
    echo cpp_courses_render_static_page($mapped_template);
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
